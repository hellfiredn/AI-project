from __future__ import annotations

import asyncio
import math
import re
import subprocess
import sys
import wave
from pathlib import Path

import edge_tts
import imageio_ffmpeg
import numpy as np
from PIL import Image, ImageDraw, ImageFilter, ImageFont


ROOT = Path(__file__).resolve().parent
OUT_DIR = ROOT / "outputs" / "cham-ngon-song-hay"
WIDTH = 720
HEIGHT = 1280
FPS = 30
DURATION = 30
TOTAL_FRAMES = FPS * DURATION
HORIZON = int(HEIGHT * 0.42)

VISUAL_MP4 = OUT_DIR / "visual_only.mp4"
MUSIC_WAV = OUT_DIR / "music_bed.wav"
VOICE_MP3 = OUT_DIR / "voice_nam_tram_am.mp3"
VOICE_FIT = OUT_DIR / "voice_fit.m4a"
FINAL_MP4 = OUT_DIR / "cham-ngon-song-hay-30s.mp4"

NARRATION = (
    "Đời người không cần đi quá nhanh. "
    "Có những điều đẹp nhất, chỉ thấy được khi ta biết chậm lại. "
    "Bình yên không ở đâu xa. "
    "Nó nằm trong bữa cơm nhà, trong tiếng cười, trong những ngày còn có nhau. "
    "Đừng mải chạy theo điều lớn lao, mà quên hạnh phúc đến từ những điều rất nhỏ. "
    "Sống tử tế, biết đủ, biết thương. Vậy là một đời đã đáng."
)

TEXT_SEGMENTS = [
    (0.0, 5.6, "Đời người", "không cần đi quá nhanh..."),
    (5.6, 11.8, "Sống chậm lại", "để thấy điều đẹp nhất"),
    (11.8, 18.2, "Bình yên", "nằm trong những điều rất gần"),
    (18.2, 24.4, "Đừng quên", "hạnh phúc nhỏ quanh mình"),
    (24.4, 30.0, "Sống tử tế", "Biết đủ. Biết thương."),
]

SCENES = [
    (0.0, 6.6, "field"),
    (6.6, 13.0, "village"),
    (13.0, 19.0, "home"),
    (19.0, 25.0, "evening"),
    (25.0, 30.0, "sunset"),
]


def clamp(value: float, lo: float = 0.0, hi: float = 1.0) -> float:
    return max(lo, min(hi, value))


def smoothstep(value: float) -> float:
    value = clamp(value)
    return value * value * (3 - 2 * value)


def lerp(a: float, b: float, amount: float) -> float:
    return a + (b - a) * amount


def lerp_color(a: tuple[int, int, int], b: tuple[int, int, int], amount: float) -> tuple[int, int, int]:
    return tuple(int(lerp(a[i], b[i], amount)) for i in range(3))


def load_font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont:
    candidates = [
        Path("C:/Windows/Fonts/arialbd.ttf" if bold else "C:/Windows/Fonts/arial.ttf"),
        Path("C:/Windows/Fonts/segoeuib.ttf" if bold else "C:/Windows/Fonts/segoeui.ttf"),
        Path("C:/Windows/Fonts/tahoma.ttf"),
    ]
    for path in candidates:
        if path.exists():
            return ImageFont.truetype(str(path), size=size)
    return ImageFont.load_default()


FONT_TITLE = load_font(58, bold=True)
FONT_SUB = load_font(34)
FONT_SMALL = load_font(24)


def text_size(draw: ImageDraw.ImageDraw, text: str, font: ImageFont.ImageFont) -> tuple[int, int]:
    box = draw.textbbox((0, 0), text, font=font)
    return box[2] - box[0], box[3] - box[1]


def wrap_text(draw: ImageDraw.ImageDraw, text: str, font: ImageFont.ImageFont, max_width: int) -> list[str]:
    words = text.split()
    lines: list[str] = []
    current = ""
    for word in words:
        candidate = word if not current else f"{current} {word}"
        if text_size(draw, candidate, font)[0] <= max_width:
            current = candidate
        else:
            if current:
                lines.append(current)
            current = word
    if current:
        lines.append(current)
    return lines


def add_gradient_sky(img: Image.Image, top: tuple[int, int, int], mid: tuple[int, int, int], bottom: tuple[int, int, int]) -> None:
    draw = ImageDraw.Draw(img)
    for y in range(HORIZON + 40):
        r = y / max(1, HORIZON + 40)
        color = lerp_color(top, mid, r / 0.55) if r < 0.55 else lerp_color(mid, bottom, (r - 0.55) / 0.45)
        draw.line([(0, y), (WIDTH, y)], fill=color)


def draw_sun(img: Image.Image, x: float, y: float, radius: float, glow: bool = True) -> None:
    if glow:
        glow_layer = Image.new("RGBA", img.size, (0, 0, 0, 0))
        glow_draw = ImageDraw.Draw(glow_layer)
        for i in range(5, 0, -1):
            alpha_radius = radius * (1 + i * 0.55)
            shade = (255, int(170 + i * 7), 91, int(22 + i * 9))
            glow_draw.ellipse(
                [x - alpha_radius, y - alpha_radius, x + alpha_radius, y + alpha_radius],
                fill=shade,
            )
        img.alpha_composite(glow_layer.filter(ImageFilter.GaussianBlur(18)))
    draw = ImageDraw.Draw(img)
    draw.ellipse([x - radius, y - radius, x + radius, y + radius], fill=(255, 225, 138, 255))


def draw_clouds(draw: ImageDraw.ImageDraw, t: float, opacity_shift: int = 0) -> None:
    color = (255, 236, 205)
    for idx, base in enumerate([(80, 190, 70), (455, 145, 55), (255, 255, 42)]):
        bx, by, scale = base
        x = (bx + t * (8 + idx * 3)) % (WIDTH + 180) - 90
        shade = tuple(max(0, min(255, c - opacity_shift)) for c in color)
        draw.ellipse([x, by, x + 2.3 * scale, by + scale], fill=shade)
        draw.ellipse([x + 40, by - 18, x + 40 + 2.1 * scale, by + scale * 0.85], fill=shade)
        draw.ellipse([x + 95, by + 4, x + 95 + 1.7 * scale, by + scale * 0.9], fill=shade)


def draw_mountains(draw: ImageDraw.ImageDraw, color: tuple[int, int, int], offset: float = 0.0) -> None:
    points = [
        (-30 + offset, HORIZON + 16),
        (95 + offset, HORIZON - 75),
        (190 + offset, HORIZON + 6),
        (310 + offset, HORIZON - 92),
        (450 + offset, HORIZON + 10),
        (585 + offset, HORIZON - 70),
        (780 + offset, HORIZON + 18),
    ]
    draw.polygon(points + [(WIDTH + 40, HORIZON + 90), (-40, HORIZON + 90)], fill=color)


def draw_house(draw: ImageDraw.ImageDraw, x: int, y: int, scale: float, wall: tuple[int, int, int], roof: tuple[int, int, int]) -> None:
    w = int(92 * scale)
    h = int(62 * scale)
    draw.rectangle([x, y, x + w, y + h], fill=wall)
    draw.polygon([(x - 10 * scale, y + 6 * scale), (x + w / 2, y - 34 * scale), (x + w + 10 * scale, y + 6 * scale)], fill=roof)
    draw.rectangle([x + int(36 * scale), y + int(27 * scale), x + int(58 * scale), y + h], fill=(87, 53, 38))


def draw_smoke(layer: Image.Image, x: float, y: float, t: float, scale: float = 1.0) -> None:
    draw = ImageDraw.Draw(layer)
    for i in range(5):
        rise = ((t * 20 + i * 22) % 110) * scale
        drift = math.sin(t * 0.7 + i) * 12 * scale
        radius = (11 + i * 3) * scale
        alpha = int(max(0, 58 - rise * 0.35))
        draw.ellipse(
            [x + drift - radius, y - rise - radius, x + drift + radius, y - rise + radius],
            fill=(255, 242, 220, alpha),
        )


def draw_bamboo(draw: ImageDraw.ImageDraw, x: int, y: int, scale: float, sway: float) -> None:
    stem = (76, 105, 55)
    for i in range(4):
        xx = x + i * 13 * scale
        draw.line([(xx, y), (xx + sway * (20 + i * 3), y - 170 * scale)], fill=stem, width=max(2, int(3 * scale)))
        top_x = xx + sway * (20 + i * 3)
        top_y = y - 165 * scale
        for k in range(5):
            ang = -1.3 + k * 0.65 + sway * 0.2
            length = 42 * scale
            draw.line(
                [(top_x, top_y), (top_x + math.cos(ang) * length, top_y + math.sin(ang) * length)],
                fill=(95, 132, 66),
                width=max(1, int(2 * scale)),
            )


def draw_field_rows(draw: ImageDraw.ImageDraw, gold: bool = False) -> None:
    field_top = HORIZON + 35
    base = (113, 157, 69) if not gold else (167, 143, 64)
    alt = (134, 177, 75) if not gold else (197, 169, 74)
    draw.rectangle([0, field_top, WIDTH, HEIGHT], fill=base)
    vanishing_x = WIDTH * 0.56
    for i in range(-10, 17):
        bottom_x = i * WIDTH / 10
        top_x = vanishing_x + i * 8
        color = alt if i % 2 == 0 else (92, 132, 62)
        draw.line([(top_x, field_top), (bottom_x, HEIGHT + 50)], fill=color, width=4)
    for y in range(field_top + 35, HEIGHT, 58):
        ratio = (y - field_top) / (HEIGHT - field_top)
        col = lerp_color((135, 181, 80), (84, 121, 61), ratio)
        draw.line([(0, y), (WIDTH, y + int(16 * ratio))], fill=col, width=2)
    for x, y, length, sway in RICE_BLADES:
        color = (202, 180, 87) if gold else (151, 185, 74)
        draw.line([(x, y), (x + sway, y - length)], fill=color, width=1)


def draw_path(draw: ImageDraw.ImageDraw, center: float = 0.52, color: tuple[int, int, int] = (171, 136, 82)) -> None:
    top_y = HORIZON + 32
    cx = WIDTH * center
    points = [
        (cx - 20, top_y),
        (cx + 24, top_y),
        (WIDTH * 0.78, HEIGHT),
        (WIDTH * 0.18, HEIGHT),
    ]
    draw.polygon(points, fill=color)
    draw.line([(cx - 20, top_y), (WIDTH * 0.18, HEIGHT)], fill=(127, 99, 67), width=2)
    draw.line([(cx + 24, top_y), (WIDTH * 0.78, HEIGHT)], fill=(127, 99, 67), width=2)


def draw_farmer(draw: ImageDraw.ImageDraw, x: float, y: float, scale: float, phase: float, color=(61, 48, 37)) -> None:
    bob = math.sin(phase) * 3 * scale
    x = x
    y = y + bob
    hat_w = 54 * scale
    hat_h = 18 * scale
    draw.polygon([(x - hat_w / 2, y - 68 * scale), (x, y - 100 * scale), (x + hat_w / 2, y - 68 * scale)], fill=(173, 133, 74))
    draw.ellipse([x - 13 * scale, y - 75 * scale, x + 13 * scale, y - 48 * scale], fill=color)
    draw.line([(x, y - 48 * scale), (x, y + 8 * scale)], fill=color, width=max(3, int(5 * scale)))
    arm = math.sin(phase + 0.8) * 16 * scale
    leg = math.sin(phase) * 20 * scale
    draw.line([(x - 38 * scale, y - 32 * scale), (x + 42 * scale, y - 50 * scale)], fill=(88, 62, 39), width=max(2, int(3 * scale)))
    draw.line([(x, y - 25 * scale), (x - 24 * scale, y + arm)], fill=color, width=max(2, int(4 * scale)))
    draw.line([(x, y - 25 * scale), (x + 25 * scale, y - arm * 0.4)], fill=color, width=max(2, int(4 * scale)))
    draw.line([(x, y + 8 * scale), (x - 18 * scale, y + 52 * scale + leg)], fill=color, width=max(2, int(4 * scale)))
    draw.line([(x, y + 8 * scale), (x + 19 * scale, y + 52 * scale - leg)], fill=color, width=max(2, int(4 * scale)))


def draw_child(draw: ImageDraw.ImageDraw, x: float, y: float, scale: float, phase: float) -> None:
    color = (75, 57, 46)
    shirt = (178, 80, 58)
    bob = abs(math.sin(phase)) * 7 * scale
    draw.ellipse([x - 10 * scale, y - 55 * scale - bob, x + 10 * scale, y - 35 * scale - bob], fill=color)
    draw.line([(x, y - 34 * scale - bob), (x, y + 5 * scale - bob)], fill=shirt, width=max(4, int(9 * scale)))
    swing = math.sin(phase) * 24 * scale
    draw.line([(x, y - 26 * scale - bob), (x - 20 * scale, y - 8 * scale + swing - bob)], fill=color, width=max(2, int(3 * scale)))
    draw.line([(x, y - 26 * scale - bob), (x + 20 * scale, y - 8 * scale - swing - bob)], fill=color, width=max(2, int(3 * scale)))
    draw.line([(x, y + 4 * scale - bob), (x - 14 * scale, y + 38 * scale + swing * 0.4 - bob)], fill=color, width=max(2, int(3 * scale)))
    draw.line([(x, y + 4 * scale - bob), (x + 14 * scale, y + 38 * scale - swing * 0.4 - bob)], fill=color, width=max(2, int(3 * scale)))


def draw_elder(draw: ImageDraw.ImageDraw, x: float, y: float, scale: float) -> None:
    color = (65, 50, 43)
    draw.ellipse([x - 13 * scale, y - 74 * scale, x + 13 * scale, y - 48 * scale], fill=color)
    draw.line([(x, y - 45 * scale), (x - 10 * scale, y + 8 * scale)], fill=color, width=max(3, int(5 * scale)))
    draw.line([(x - 10 * scale, y + 8 * scale), (x - 50 * scale, y + 28 * scale)], fill=color, width=max(2, int(4 * scale)))
    draw.line([(x - 10 * scale, y + 6 * scale), (x + 36 * scale, y + 28 * scale)], fill=color, width=max(2, int(4 * scale)))
    draw.line([(x + 30 * scale, y - 18 * scale), (x + 42 * scale, y + 56 * scale)], fill=(92, 64, 43), width=max(2, int(3 * scale)))


def draw_buffalo(draw: ImageDraw.ImageDraw, x: float, y: float, scale: float, phase: float) -> None:
    color = (54, 47, 42)
    draw.ellipse([x - 70 * scale, y - 36 * scale, x + 56 * scale, y + 28 * scale], fill=color)
    draw.ellipse([x + 43 * scale, y - 48 * scale, x + 88 * scale, y - 8 * scale], fill=color)
    draw.arc([x + 60 * scale, y - 70 * scale, x + 120 * scale, y - 18 * scale], start=185, end=315, fill=(38, 34, 31), width=max(2, int(3 * scale)))
    for i, lx in enumerate([-42, -5, 32, 55]):
        step = math.sin(phase + i) * 8 * scale
        draw.line([(x + lx * scale, y + 15 * scale), (x + lx * scale + step, y + 75 * scale)], fill=color, width=max(4, int(7 * scale)))
    draw.line([(x - 70 * scale, y - 12 * scale), (x - 103 * scale, y - 35 * scale + math.sin(phase) * 8 * scale)], fill=color, width=max(2, int(3 * scale)))


def draw_family_table(draw: ImageDraw.ImageDraw, t: float) -> None:
    table_y = int(HEIGHT * 0.72)
    draw.ellipse([WIDTH * 0.20, table_y - 48, WIDTH * 0.80, table_y + 56], fill=(123, 72, 44))
    draw.ellipse([WIDTH * 0.32, table_y - 28, WIDTH * 0.45, table_y + 20], fill=(238, 228, 194))
    draw.ellipse([WIDTH * 0.53, table_y - 30, WIDTH * 0.66, table_y + 18], fill=(238, 228, 194))
    for x, y in [(255, table_y - 112), (360, table_y - 132), (465, table_y - 112)]:
        phase = math.sin(t * 1.8 + x) * 2
        draw.ellipse([x - 22, y - 44 + phase, x + 22, y + phase], fill=(79, 55, 45))
        draw.line([(x, y + phase), (x, y + 78 + phase)], fill=(89, 61, 47), width=9)
        draw.line([(x, y + 36 + phase), (x - 45, y + 58 + phase)], fill=(89, 61, 47), width=5)
        draw.line([(x, y + 36 + phase), (x + 43, y + 58 + phase)], fill=(89, 61, 47), width=5)


def draw_text_overlay(img: Image.Image, t: float) -> None:
    segment = None
    for start, end, title, sub in TEXT_SEGMENTS:
        if start <= t < end:
            fade = min(smoothstep((t - start) / 0.7), smoothstep((end - t) / 0.7))
            segment = (title, sub, fade)
            break
    if not segment:
        return

    title, sub, alpha = segment
    overlay = Image.new("RGBA", img.size, (0, 0, 0, 0))
    od = ImageDraw.Draw(overlay)
    for i in range(360):
        a = int(lerp(0, 150, i / 360) * alpha)
        y = HEIGHT - 360 + i
        od.line([(0, y), (WIDTH, y)], fill=(20, 24, 18, a))

    draw = ImageDraw.Draw(overlay)
    max_width = WIDTH - 112
    title_lines = wrap_text(draw, title, FONT_TITLE, max_width)
    sub_lines = wrap_text(draw, sub, FONT_SUB, max_width)
    total_h = len(title_lines) * 70 + len(sub_lines) * 45 + 24
    y = HEIGHT - 254 - total_h / 2

    for line in title_lines:
        tw, th = text_size(draw, line, FONT_TITLE)
        x = (WIDTH - tw) / 2
        draw.text((x + 3, y + 4), line, font=FONT_TITLE, fill=(0, 0, 0, int(130 * alpha)))
        draw.text((x, y), line, font=FONT_TITLE, fill=(255, 246, 219, int(255 * alpha)))
        y += 70
    y += 6
    for line in sub_lines:
        tw, th = text_size(draw, line, FONT_SUB)
        x = (WIDTH - tw) / 2
        draw.text((x + 2, y + 3), line, font=FONT_SUB, fill=(0, 0, 0, int(120 * alpha)))
        draw.text((x, y), line, font=FONT_SUB, fill=(236, 222, 176, int(255 * alpha)))
        y += 45

    img.alpha_composite(overlay)


def scene_field(t: float) -> Image.Image:
    img = Image.new("RGBA", (WIDTH, HEIGHT), (0, 0, 0, 255))
    add_gradient_sky(img, (77, 118, 154), (239, 169, 101), (255, 211, 139))
    draw = ImageDraw.Draw(img)
    draw_sun(img, WIDTH * 0.73, HORIZON * 0.55 + math.sin(t * 0.4) * 8, 36)
    draw_clouds(draw, t * 0.7)
    draw_mountains(draw, (92, 116, 84))
    draw_field_rows(draw, gold=False)
    draw_path(draw, 0.50)
    for i, x in enumerate([40, 145, 565, 650]):
        draw_house(draw, x, HORIZON - 3 + (i % 2) * 12, 0.8, (189, 143, 86), (110, 67, 48))
    smoke = Image.new("RGBA", img.size, (0, 0, 0, 0))
    draw_smoke(smoke, 105, HORIZON - 15, t, 0.8)
    draw_smoke(smoke, 620, HORIZON + 2, t + 1.8, 0.65)
    img.alpha_composite(smoke.filter(ImageFilter.GaussianBlur(1.4)))
    draw = ImageDraw.Draw(img)
    draw_bamboo(draw, 35, HORIZON + 130, 0.82, math.sin(t * 0.5) * 0.55)
    draw_farmer(draw, 120 + t * 32, HORIZON + 300 + t * 7, 1.05, t * 4.2)
    return img


def scene_village(t: float) -> Image.Image:
    img = Image.new("RGBA", (WIDTH, HEIGHT), (0, 0, 0, 255))
    add_gradient_sky(img, (86, 145, 177), (247, 188, 117), (255, 223, 158))
    draw = ImageDraw.Draw(img)
    draw_clouds(draw, t * 0.8, opacity_shift=16)
    draw.rectangle([0, HORIZON - 8, WIDTH, HEIGHT], fill=(128, 151, 84))
    draw_path(draw, 0.48, (183, 142, 89))
    draw.polygon([(0, HORIZON + 160), (WIDTH * 0.26, HORIZON + 80), (WIDTH * 0.18, HEIGHT), (0, HEIGHT)], fill=(89, 135, 65))
    draw.polygon([(WIDTH, HORIZON + 115), (WIDTH * 0.68, HORIZON + 70), (WIDTH * 0.84, HEIGHT), (WIDTH, HEIGHT)], fill=(98, 142, 70))
    for i, (x, y, s) in enumerate([(68, HORIZON + 12, 1.2), (488, HORIZON + 18, 1.12), (272, HORIZON - 5, 0.92)]):
        draw_house(draw, x, y, s, (196, 150, 91), (118, 64, 45))
    smoke = Image.new("RGBA", img.size, (0, 0, 0, 0))
    draw_smoke(smoke, 155, HORIZON + 10, t, 0.85)
    draw_smoke(smoke, 555, HORIZON + 18, t + 2.1, 0.75)
    img.alpha_composite(smoke.filter(ImageFilter.GaussianBlur(1.3)))
    draw = ImageDraw.Draw(img)
    draw_bamboo(draw, 635, HORIZON + 190, 0.88, math.sin(t * 0.5 + 1.3) * 0.45)
    draw_child(draw, 115 + t * 55, HORIZON + 410, 1.0, t * 7.0)
    draw_child(draw, 40 + t * 43, HORIZON + 500, 0.83, t * 7.4 + 1.2)
    draw_farmer(draw, 560 - t * 19, HORIZON + 360, 0.82, t * 3.7 + 1.3, color=(73, 55, 43))
    return img


def scene_home(t: float) -> Image.Image:
    img = Image.new("RGBA", (WIDTH, HEIGHT), (116, 73, 46, 255))
    draw = ImageDraw.Draw(img)
    for y in range(HEIGHT):
        r = y / HEIGHT
        color = lerp_color((96, 58, 40), (198, 137, 76), r)
        draw.line([(0, y), (WIDTH, y)], fill=color)
    draw.rectangle([0, int(HEIGHT * 0.60), WIDTH, HEIGHT], fill=(96, 63, 45))
    draw.rectangle([58, 184, WIDTH - 58, int(HEIGHT * 0.60)], fill=(142, 89, 52))
    draw.polygon([(32, 198), (WIDTH / 2, 80), (WIDTH - 32, 198)], fill=(102, 58, 42))
    draw.rectangle([116, 255, 276, 485], fill=(240, 178, 92))
    draw.rectangle([444, 255, 604, 485], fill=(231, 166, 86))
    for i in range(10):
        x = 92 + i * 58
        draw.line([(x, 206), (x - 45, 560)], fill=(86, 52, 39), width=4)
    fire = Image.new("RGBA", img.size, (0, 0, 0, 0))
    fd = ImageDraw.Draw(fire)
    cx, cy = 152, int(HEIGHT * 0.72)
    flicker = math.sin(t * 9) * 7
    fd.ellipse([cx - 58, cy + 20, cx + 58, cy + 56], fill=(54, 44, 37, 255))
    fd.polygon([(cx - 22, cy + 20), (cx + flicker, cy - 54), (cx + 24, cy + 22)], fill=(255, 180, 61, 220))
    fd.polygon([(cx - 11, cy + 18), (cx - flicker * 0.7, cy - 34), (cx + 14, cy + 20)], fill=(255, 234, 151, 230))
    draw_smoke(fire, cx + 6, cy - 42, t, 0.95)
    img.alpha_composite(fire.filter(ImageFilter.GaussianBlur(0.7)))
    draw = ImageDraw.Draw(img)
    draw_family_table(draw, t)
    draw_farmer(draw, 585, 870, 0.95, t * 2.6, color=(70, 51, 41))
    return img


def scene_evening(t: float) -> Image.Image:
    img = Image.new("RGBA", (WIDTH, HEIGHT), (0, 0, 0, 255))
    add_gradient_sky(img, (84, 96, 128), (226, 137, 82), (239, 178, 104))
    draw = ImageDraw.Draw(img)
    draw_sun(img, WIDTH * 0.30, HORIZON * 0.82 + t * 5, 42, glow=False)
    draw_mountains(draw, (82, 95, 82))
    draw_field_rows(draw, gold=True)
    draw_path(draw, 0.57, (153, 116, 75))
    for x in [80, 480]:
        draw_house(draw, x, HORIZON + 18, 1.0, (152, 101, 69), (88, 53, 44))
    draw.rectangle([0, HORIZON + 180, 155, HORIZON + 300], fill=(105, 72, 52))
    draw.polygon([(0, HORIZON + 180), (74, HORIZON + 120), (158, HORIZON + 180)], fill=(78, 48, 39))
    draw_elder(draw, 92, HORIZON + 290, 0.92)
    draw_buffalo(draw, 520 - t * 17, HORIZON + 520, 1.0, t * 3.2)
    draw_child(draw, 420 - t * 12, HORIZON + 440, 0.75, t * 5.5)
    return img


def scene_sunset(t: float) -> Image.Image:
    img = Image.new("RGBA", (WIDTH, HEIGHT), (0, 0, 0, 255))
    add_gradient_sky(img, (73, 79, 111), (208, 117, 80), (238, 169, 96))
    draw = ImageDraw.Draw(img)
    draw_sun(img, WIDTH * 0.56, HORIZON * 0.94 + t * 2, 50, glow=False)
    draw_mountains(draw, (74, 86, 75))
    draw_field_rows(draw, gold=True)
    draw_path(draw, 0.50, (145, 106, 72))
    for i, x in enumerate([45, 575, 260]):
        draw_house(draw, x, HORIZON + 5 + i * 12, 0.9, (142, 93, 65), (79, 48, 40))
    draw_bamboo(draw, 618, HORIZON + 160, 0.85, math.sin(t * 0.4) * 0.4)
    draw_farmer(draw, WIDTH * 0.50 + math.sin(t * 0.35) * 10, HORIZON + 485 + t * 7, 1.18, t * 3.8, color=(51, 42, 36))
    return img


SCENE_RENDERERS = {
    "field": scene_field,
    "village": scene_village,
    "home": scene_home,
    "evening": scene_evening,
    "sunset": scene_sunset,
}


def scene_for_time(t: float) -> tuple[int, float, float, str]:
    for idx, (start, end, name) in enumerate(SCENES):
        if start <= t < end or idx == len(SCENES) - 1:
            return idx, start, end, name
    return len(SCENES) - 1, SCENES[-1][0], SCENES[-1][1], SCENES[-1][2]


def make_vignette() -> Image.Image:
    yy, xx = np.mgrid[0:HEIGHT, 0:WIDTH]
    nx = (xx - WIDTH / 2) / (WIDTH / 2)
    ny = (yy - HEIGHT * 0.48) / (HEIGHT * 0.62)
    dist = np.sqrt(nx * nx + ny * ny)
    alpha = np.clip((dist - 0.70) / 0.42, 0, 1) ** 1.8
    layer = np.zeros((HEIGHT, WIDTH, 4), dtype=np.uint8)
    layer[..., 3] = (alpha * 78).astype(np.uint8)
    return Image.fromarray(layer, "RGBA")


def render_frame(t: float) -> Image.Image:
    idx, start, end, name = scene_for_time(t)
    local_t = t - start
    img = SCENE_RENDERERS[name](local_t)
    transition = 0.9
    if idx < len(SCENES) - 1 and t > end - transition:
        next_start, _, next_name = SCENES[idx + 1]
        alpha = smoothstep((t - (end - transition)) / transition)
        next_img = SCENE_RENDERERS[next_name](max(0.0, t - next_start))
        img = Image.blend(img, next_img, alpha)
    img.alpha_composite(VIGNETTE)
    draw_text_overlay(img, t)
    return img.convert("RGB")


def generate_music(path: Path) -> None:
    sr = 44_100
    total = int(DURATION * sr)
    audio = np.zeros(total, dtype=np.float32)
    rng = np.random.default_rng(7)

    def add_note(start: float, freq: float, length: float, amp: float, pluck: bool = False) -> None:
        start_i = int(start * sr)
        end_i = min(total, int((start + length) * sr))
        if end_i <= start_i:
            return
        tt = np.arange(end_i - start_i, dtype=np.float32) / sr
        if pluck:
            env = np.exp(-tt * 2.4) * np.minimum(1.0, tt / 0.025)
            tone = (
                np.sin(2 * np.pi * freq * tt)
                + 0.45 * np.sin(2 * np.pi * freq * 2 * tt)
                + 0.18 * np.sin(2 * np.pi * freq * 3 * tt)
            )
        else:
            attack = np.minimum(1.0, tt / 0.35)
            release = np.minimum(1.0, (length - tt) / 0.6)
            env = np.clip(attack * release, 0, 1)
            vibrato = 1 + 0.004 * np.sin(2 * np.pi * 5.1 * tt)
            tone = np.sin(2 * np.pi * freq * vibrato * tt)
        audio[start_i:end_i] += amp * env * tone

    chords = [
        (0, [196.0, 261.63, 329.63]),
        (6, [174.61, 261.63, 349.23]),
        (12, [220.0, 277.18, 329.63]),
        (18, [196.0, 246.94, 329.63]),
        (24, [174.61, 261.63, 349.23]),
    ]
    for start, notes in chords:
        for note in notes:
            add_note(start, note, 7.8, 0.026, pluck=False)

    for start in np.arange(0.7, 29, 2.4):
        for freq in [261.63, 329.63, 392.0]:
            add_note(float(start), freq, 1.55, 0.035, pluck=True)

    melody = [
        (3.2, 392.0),
        (5.8, 440.0),
        (8.4, 523.25),
        (12.6, 493.88),
        (15.1, 392.0),
        (19.8, 440.0),
        (22.6, 523.25),
        (26.1, 587.33),
    ]
    for start, freq in melody:
        add_note(start, freq, 2.25, 0.025, pluck=False)

    wind = rng.normal(0, 0.0045, total).astype(np.float32)
    for _ in range(3):
        wind = np.convolve(wind, np.ones(240, dtype=np.float32) / 240, mode="same")
    audio += wind
    audio *= np.linspace(0, 1, int(1.2 * sr)).tolist() + [1] * (total - int(2.4 * sr)) + np.linspace(1, 0, int(1.2 * sr)).tolist()
    audio = np.clip(audio, -0.95, 0.95)
    stereo = np.stack([audio * 0.92, audio], axis=1)
    pcm = (stereo * 32767).astype(np.int16)
    with wave.open(str(path), "wb") as wf:
        wf.setnchannels(2)
        wf.setsampwidth(2)
        wf.setframerate(sr)
        wf.writeframes(pcm.tobytes())


async def generate_voice(path: Path) -> bool:
    try:
        communicator = edge_tts.Communicate(
            NARRATION,
            voice="vi-VN-NamMinhNeural",
            rate="-2%",
            pitch="-5Hz",
            volume="+0%",
        )
        await communicator.save(str(path))
        return path.exists() and path.stat().st_size > 1000
    except Exception as exc:
        print(f"Voice generation failed: {exc}")
        return False


def run(cmd: list[str], stdin_data: bytes | None = None) -> subprocess.CompletedProcess:
    return subprocess.run(cmd, input=stdin_data, check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)


def probe_duration(ffmpeg: str, path: Path) -> float | None:
    proc = subprocess.run([ffmpeg, "-i", str(path), "-f", "null", "-"], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
    match = re.search(r"Duration: (\d+):(\d+):(\d+\.\d+)", proc.stderr)
    if not match:
        return None
    hours, minutes, seconds = match.groups()
    return int(hours) * 3600 + int(minutes) * 60 + float(seconds)


def fit_voice_if_needed(ffmpeg: str) -> Path:
    duration = probe_duration(ffmpeg, VOICE_MP3)
    if not duration:
        return VOICE_MP3
    if duration <= 28.4:
        return VOICE_MP3
    tempo = duration / 28.4
    tempo = min(max(tempo, 1.0), 1.35)
    print(f"Voice duration {duration:.1f}s, applying atempo={tempo:.3f}")
    run(
        [
            ffmpeg,
            "-y",
            "-i",
            str(VOICE_MP3),
            "-filter:a",
            f"atempo={tempo:.5f}",
            "-c:a",
            "aac",
            "-b:a",
            "160k",
            str(VOICE_FIT),
        ]
    )
    return VOICE_FIT


def render_visual(ffmpeg: str) -> None:
    cmd = [
        ffmpeg,
        "-y",
        "-f",
        "rawvideo",
        "-vcodec",
        "rawvideo",
        "-pix_fmt",
        "rgb24",
        "-s",
        f"{WIDTH}x{HEIGHT}",
        "-r",
        str(FPS),
        "-i",
        "-",
        "-an",
        "-c:v",
        "libx264",
        "-preset",
        "medium",
        "-crf",
        "18",
        "-pix_fmt",
        "yuv420p",
        str(VISUAL_MP4),
    ]
    proc = subprocess.Popen(cmd, stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    assert proc.stdin is not None
    try:
        for frame_idx in range(TOTAL_FRAMES):
            t = frame_idx / FPS
            frame = render_frame(t)
            proc.stdin.write(frame.tobytes())
            if frame_idx % 90 == 0:
                print(f"Rendered frame {frame_idx}/{TOTAL_FRAMES}")
    finally:
        proc.stdin.close()
    stderr = proc.stderr.read().decode("utf-8", errors="replace") if proc.stderr else ""
    return_code = proc.wait()
    if return_code != 0:
        raise RuntimeError(stderr)


def mux_final(ffmpeg: str, voice_available: bool) -> None:
    if voice_available:
        voice_source = fit_voice_if_needed(ffmpeg)
        filter_complex = (
            "[1:a]volume=1.35,adelay=650|650,apad=pad_dur=30[voice];"
            "[2:a]volume=0.22[music];"
            "[voice][music]amix=inputs=2:duration=longest:dropout_transition=2[aout]"
        )
        cmd = [
            ffmpeg,
            "-y",
            "-i",
            str(VISUAL_MP4),
            "-i",
            str(voice_source),
            "-i",
            str(MUSIC_WAV),
            "-filter_complex",
            filter_complex,
            "-map",
            "0:v",
            "-map",
            "[aout]",
            "-c:v",
            "copy",
            "-c:a",
            "aac",
            "-b:a",
            "192k",
            "-t",
            str(DURATION),
            "-movflags",
            "+faststart",
            str(FINAL_MP4),
        ]
    else:
        cmd = [
            ffmpeg,
            "-y",
            "-i",
            str(VISUAL_MP4),
            "-i",
            str(MUSIC_WAV),
            "-map",
            "0:v",
            "-map",
            "1:a",
            "-c:v",
            "copy",
            "-c:a",
            "aac",
            "-b:a",
            "192k",
            "-t",
            str(DURATION),
            "-movflags",
            "+faststart",
            str(FINAL_MP4),
        ]
    run(cmd)


def main() -> int:
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    ffmpeg = imageio_ffmpeg.get_ffmpeg_exe()
    print(f"Using ffmpeg: {ffmpeg}")
    print("Generating warm rural music bed...")
    generate_music(MUSIC_WAV)
    print("Generating Vietnamese male voiceover...")
    voice_available = asyncio.run(generate_voice(VOICE_MP3))
    print("Rendering animated countryside frames...")
    render_visual(ffmpeg)
    print("Muxing final MP4...")
    mux_final(ffmpeg, voice_available)
    final_duration = probe_duration(ffmpeg, FINAL_MP4)
    print(f"Done: {FINAL_MP4}")
    if final_duration:
        print(f"Duration: {final_duration:.2f}s")
    if not voice_available:
        print("Warning: final video has music but no voiceover because TTS failed.")
    return 0


rng = np.random.default_rng(12)
RICE_BLADES = []
for _ in range(420):
    y = int(rng.uniform(HORIZON + 115, HEIGHT - 20))
    x = int(rng.uniform(0, WIDTH))
    length = int(rng.uniform(8, 23) * ((y - HORIZON) / (HEIGHT - HORIZON)))
    sway = int(rng.uniform(-8, 8))
    RICE_BLADES.append((x, y, max(5, length), sway))
VIGNETTE = make_vignette()


if __name__ == "__main__":
    raise SystemExit(main())
