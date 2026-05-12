from __future__ import annotations

import math
import re
import subprocess
from pathlib import Path

import imageio_ffmpeg
import numpy as np
from PIL import Image, ImageDraw, ImageFilter, ImageFont


ROOT = Path(__file__).resolve().parent
OUT_DIR = ROOT / "outputs" / "cham-ngon-song-hay"
SOURCE_IMAGE = OUT_DIR / "lang-que-3d-source.png"
WIDTH = 720
HEIGHT = 1280
FPS = 30
DURATION = 30
TOTAL_FRAMES = FPS * DURATION

VISUAL_MP4 = OUT_DIR / "visual_3d_image_animated.mp4"
MUSIC_WAV = OUT_DIR / "music_bed.wav"
VOICE_MP3 = OUT_DIR / "voice_nam_tram_am.mp3"
FINAL_MP4 = OUT_DIR / "cham-ngon-song-hay-3d-bg-animated.mp4"

TEXT_SEGMENTS = [
    (0.0, 5.6, "Đời người", "không cần đi quá nhanh..."),
    (5.6, 11.8, "Sống chậm lại", "để thấy điều đẹp nhất"),
    (11.8, 18.2, "Bình yên", "nằm trong những điều rất gần"),
    (18.2, 24.4, "Đừng quên", "hạnh phúc nhỏ quanh mình"),
    (24.4, 30.0, "Sống tử tế", "Biết đủ. Biết thương."),
]

CAMERA_KEYS = [
    (0.0, 440, 520, 1.00),
    (6.0, 540, 518, 1.06),
    (12.5, 735, 530, 1.10),
    (19.0, 995, 540, 1.08),
    (25.0, 1125, 535, 1.04),
    (30.0, 690, 525, 1.02),
]


def clamp(value: float, lo: float = 0.0, hi: float = 1.0) -> float:
    return max(lo, min(hi, value))


def smoothstep(value: float) -> float:
    value = clamp(value)
    return value * value * (3 - 2 * value)


def lerp(a: float, b: float, amount: float) -> float:
    return a + (b - a) * amount


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
FONT_SMALL = load_font(22)


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


def interpolate_camera(t: float) -> tuple[float, float, float]:
    for i in range(len(CAMERA_KEYS) - 1):
        t0, x0, y0, z0 = CAMERA_KEYS[i]
        t1, x1, y1, z1 = CAMERA_KEYS[i + 1]
        if t0 <= t <= t1:
            amount = smoothstep((t - t0) / (t1 - t0))
            drift_x = math.sin(t * 0.32) * 9
            drift_y = math.sin(t * 0.27 + 1.2) * 6
            return (
                lerp(x0, x1, amount) + drift_x,
                lerp(y0, y1, amount) + drift_y,
                lerp(z0, z1, amount),
            )
    _, x, y, z = CAMERA_KEYS[-1]
    return x, y, z


def crop_camera(source: Image.Image, t: float) -> tuple[Image.Image, tuple[float, float, float, float]]:
    src_w, src_h = source.size
    cx, cy, zoom = interpolate_camera(t)
    crop_h = src_h / zoom
    crop_w = crop_h * WIDTH / HEIGHT
    left = clamp(cx - crop_w / 2, 0, src_w - crop_w)
    top = clamp(cy - crop_h / 2, 0, src_h - crop_h)
    right = left + crop_w
    bottom = top + crop_h
    frame = source.crop((left, top, right, bottom)).resize((WIDTH, HEIGHT), Image.Resampling.LANCZOS)
    return frame.convert("RGBA"), (left, top, crop_w, crop_h)


def project(src_x: float, src_y: float, camera: tuple[float, float, float, float]) -> tuple[float, float, float]:
    left, top, crop_w, crop_h = camera
    scale = WIDTH / crop_w
    return (src_x - left) * scale, (src_y - top) * scale, scale


def make_vignette() -> Image.Image:
    yy, xx = np.mgrid[0:HEIGHT, 0:WIDTH]
    nx = (xx - WIDTH / 2) / (WIDTH / 2)
    ny = (yy - HEIGHT * 0.47) / (HEIGHT * 0.65)
    dist = np.sqrt(nx * nx + ny * ny)
    alpha = np.clip((dist - 0.68) / 0.45, 0, 1) ** 1.7
    layer = np.zeros((HEIGHT, WIDTH, 4), dtype=np.uint8)
    layer[..., 3] = (alpha * 84).astype(np.uint8)
    return Image.fromarray(layer, "RGBA")


VIGNETTE = make_vignette()


def apply_wind_sway(frame: Image.Image, t: float) -> Image.Image:
    arr = np.asarray(frame.convert("RGB")).copy()
    h, w, _ = arr.shape
    yy = np.arange(h)[:, None]
    r = arr[..., 0].astype(np.float32)
    g = arr[..., 1].astype(np.float32)
    b = arr[..., 2].astype(np.float32)
    vegetation = (
        (yy > h * 0.25)
        & (
            ((g > 72) & (g > b * 1.10) & (g > r * 0.72))
            | ((r > 115) & (g > 95) & (b < 112) & (yy > h * 0.45))
        )
    )
    mask = Image.fromarray((vegetation.astype(np.uint8) * 255), "L").filter(ImageFilter.GaussianBlur(7))
    mask_arr = np.asarray(mask).astype(np.float32)[..., None] / 255.0

    shifted = arr.copy()
    for y in range(h):
        depth = smoothstep((y - h * 0.24) / (h * 0.76))
        amp = 1.2 + depth * 8.5
        shift = int(round(math.sin(t * 2.0 + y * 0.036) * amp + math.sin(t * 3.7 + y * 0.072) * 1.6))
        if shift:
            shifted[y] = np.roll(arr[y], shift, axis=0)

    mixed = arr * (1.0 - mask_arr * 0.72) + shifted * (mask_arr * 0.72)
    return Image.fromarray(np.clip(mixed, 0, 255).astype(np.uint8), "RGB").convert("RGBA")


def draw_smoke(layer: Image.Image, camera: tuple[float, float, float, float], t: float) -> None:
    draw = ImageDraw.Draw(layer)
    x0, y0, scale = project(604, 206, camera)
    if not (-80 < x0 < WIDTH + 80 and -80 < y0 < HEIGHT + 120):
        return
    for i in range(8):
        rise = ((t * 17 + i * 19) % 150) * scale
        drift = math.sin(t * 0.75 + i * 0.9) * 16 * scale
        radius = (7 + i * 1.6) * scale
        alpha = int(max(0, 36 - rise * 0.18))
        draw.ellipse(
            [x0 + drift - radius, y0 - rise - radius, x0 + drift + radius, y0 - rise + radius],
            fill=(250, 241, 220, alpha),
        )


def draw_conical_farmer(
    draw: ImageDraw.ImageDraw,
    x: float,
    y: float,
    s: float,
    phase: float,
    carrying: bool = False,
    shirt: tuple[int, int, int, int] = (103, 76, 52, 255),
) -> None:
    if x < -90 or x > WIDTH + 90 or y < -120 or y > HEIGHT + 140:
        return
    bob = math.sin(phase) * 3 * s
    shadow = [x - 22 * s, y + 50 * s, x + 28 * s, y + 60 * s]
    draw.ellipse(shadow, fill=(30, 25, 18, 60))
    hat = (224, 183, 104, 255)
    edge = (132, 89, 45, 230)
    draw.polygon([(x - 36 * s, y - 52 * s + bob), (x, y - 86 * s + bob), (x + 36 * s, y - 52 * s + bob)], fill=hat)
    draw.line([(x - 36 * s, y - 52 * s + bob), (x + 36 * s, y - 52 * s + bob)], fill=edge, width=max(1, int(2 * s)))
    draw.ellipse([x - 10 * s, y - 56 * s + bob, x + 10 * s, y - 35 * s + bob], fill=(69, 47, 34, 255))
    draw.line([(x, y - 34 * s + bob), (x, y + 12 * s + bob)], fill=shirt, width=max(3, int(8 * s)))
    leg = math.sin(phase) * 15 * s
    arm = math.sin(phase + 1.2) * 14 * s
    draw.line([(x, y - 18 * s + bob), (x - 20 * s, y + arm + bob)], fill=(58, 43, 35, 255), width=max(2, int(4 * s)))
    draw.line([(x, y - 18 * s + bob), (x + 20 * s, y - arm * 0.35 + bob)], fill=(58, 43, 35, 255), width=max(2, int(4 * s)))
    draw.line([(x, y + 12 * s + bob), (x - 17 * s, y + 49 * s + leg + bob)], fill=(44, 36, 31, 255), width=max(2, int(4 * s)))
    draw.line([(x, y + 12 * s + bob), (x + 17 * s, y + 49 * s - leg + bob)], fill=(44, 36, 31, 255), width=max(2, int(4 * s)))
    if carrying:
        draw.line([(x - 43 * s, y - 22 * s + bob), (x + 44 * s, y - 40 * s + bob)], fill=(93, 60, 34, 255), width=max(2, int(3 * s)))
        for side in [-1, 1]:
            cx = x + side * 50 * s
            cy = y - 24 * s + bob
            draw.ellipse([cx - 17 * s, cy - 24 * s, cx + 17 * s, cy + 24 * s], fill=(205, 165, 72, 235))
            for k in range(6):
                a = -1.0 + k * 0.4
                draw.line([(cx, cy), (cx + math.cos(a) * 24 * s, cy + math.sin(a) * 25 * s)], fill=(232, 190, 83, 210), width=1)


def draw_working_farmer(draw: ImageDraw.ImageDraw, x: float, y: float, s: float, phase: float) -> None:
    if x < -90 or x > WIDTH + 90 or y < -120 or y > HEIGHT + 140:
        return
    bend = math.sin(phase) * 7 * s
    draw.ellipse([x - 30 * s, y + 34 * s, x + 34 * s, y + 45 * s], fill=(24, 20, 15, 45))
    draw.polygon([(x - 33 * s, y - 26 * s), (x, y - 58 * s + bend), (x + 33 * s, y - 26 * s)], fill=(226, 185, 109, 255))
    draw.line([(x - 33 * s, y - 26 * s), (x + 33 * s, y - 26 * s)], fill=(131, 92, 51, 220), width=max(1, int(2 * s)))
    draw.line([(x, y - 25 * s), (x + 28 * s, y + 5 * s + bend)], fill=(96, 63, 55, 255), width=max(3, int(8 * s)))
    draw.line([(x + 18 * s, y - 8 * s), (x + 49 * s, y + 25 * s + bend)], fill=(63, 43, 34, 255), width=max(2, int(4 * s)))
    draw.line([(x + 14 * s, y + 4 * s), (x - 14 * s, y + 36 * s)], fill=(46, 36, 31, 255), width=max(2, int(4 * s)))
    draw.line([(x + 28 * s, y + 8 * s), (x + 8 * s, y + 40 * s)], fill=(46, 36, 31, 255), width=max(2, int(4 * s)))


def draw_buffalo(draw: ImageDraw.ImageDraw, x: float, y: float, s: float, phase: float) -> None:
    if x < -150 or x > WIDTH + 150 or y < -120 or y > HEIGHT + 140:
        return
    color = (55, 48, 41, 245)
    draw.ellipse([x - 74 * s, y - 30 * s, x + 60 * s, y + 32 * s], fill=color)
    draw.ellipse([x + 48 * s, y - 44 * s, x + 92 * s, y - 5 * s], fill=color)
    draw.arc([x + 62 * s, y - 68 * s, x + 126 * s, y - 14 * s], 180, 318, fill=(35, 31, 28, 230), width=max(2, int(3 * s)))
    for i, lx in enumerate([-44, -10, 30, 55]):
        step = math.sin(phase + i * 0.8) * 8 * s
        draw.line([(x + lx * s, y + 18 * s), (x + lx * s + step, y + 72 * s)], fill=color, width=max(3, int(7 * s)))
    draw.line([(x - 72 * s, y - 8 * s), (x - 108 * s, y - 29 * s + math.sin(phase) * 8 * s)], fill=color, width=max(2, int(3 * s)))


def draw_subtle_worker_motion(
    draw: ImageDraw.ImageDraw,
    camera: tuple[float, float, float, float],
    src_x: float,
    src_y: float,
    size: float,
    phase: float,
) -> None:
    x, y, scale = project(src_x, src_y, camera)
    s = scale * size
    if x < -80 or x > WIDTH + 80 or y < -120 or y > HEIGHT + 120:
        return
    bob = math.sin(phase) * 4 * s
    hat_alpha = 88
    arm_alpha = 98
    draw.polygon(
        [(x - 25 * s, y - 38 * s + bob), (x, y - 65 * s + bob), (x + 25 * s, y - 38 * s + bob)],
        fill=(234, 196, 122, hat_alpha),
    )
    draw.line(
        [(x - 24 * s, y - 38 * s + bob), (x + 26 * s, y - 38 * s + bob)],
        fill=(116, 83, 48, hat_alpha),
        width=max(1, int(2 * s)),
    )
    swing = math.sin(phase + 0.7) * 12 * s
    draw.line(
        [(x + 2 * s, y - 21 * s), (x + 32 * s, y + 10 * s + swing)],
        fill=(77, 52, 38, arm_alpha),
        width=max(1, int(3 * s)),
    )
    for k in range(3):
        gx = x + (k - 1) * 15 * s
        gy = y + (16 + k * 4) * s
        draw.line(
            [(gx, gy), (gx + math.sin(phase + k) * 9 * s, gy - 18 * s)],
            fill=(240, 203, 83, 62),
            width=1,
        )


def draw_path_farmer_motion(draw: ImageDraw.ImageDraw, camera: tuple[float, float, float, float], t: float) -> None:
    x, y, scale = project(455 + math.sin(t * 0.8) * 5, 730 + math.sin(t * 1.2) * 3, camera)
    s = scale * 0.52
    if x < -80 or x > WIDTH + 80 or y < -120 or y > HEIGHT * 0.72:
        return
    sway = math.sin(t * 4.4) * 8 * s
    for side in [-1, 1]:
        cx = x + side * (38 * s + sway * 0.25)
        cy = y - 12 * s - sway * side * 0.20
        draw.ellipse([cx - 15 * s, cy - 22 * s, cx + 15 * s, cy + 22 * s], fill=(223, 178, 72, 74))
        for k in range(4):
            a = -1.0 + k * 0.55 + math.sin(t * 3 + k) * 0.09
            draw.line([(cx, cy), (cx + math.cos(a) * 20 * s, cy + math.sin(a) * 21 * s)], fill=(246, 203, 88, 72), width=1)


def draw_buffalo_motion(draw: ImageDraw.ImageDraw, camera: tuple[float, float, float, float], t: float) -> None:
    x, y, scale = project(1302, 548, camera)
    s = scale * 0.52
    if x < -120 or x > WIDTH + 120 or y < -100 or y > HEIGHT + 100:
        return
    tail = math.sin(t * 3.2) * 12 * s
    draw.line(
        [(x - 52 * s, y - 8 * s), (x - 83 * s, y - 26 * s + tail)],
        fill=(48, 37, 30, 115),
        width=max(1, int(3 * s)),
    )
    for lx in [-28, 8, 38]:
        step = math.sin(t * 4.0 + lx) * 4 * s
        draw.line([(x + lx * s, y + 22 * s), (x + lx * s + step, y + 43 * s)], fill=(48, 37, 30, 82), width=max(1, int(2 * s)))


def draw_grass_glints(layer: Image.Image, camera: tuple[float, float, float, float], t: float) -> None:
    draw = ImageDraw.Draw(layer)
    for x_src, y_src, length, phase in GRASS_BLADES:
        x, y, scale = project(x_src, y_src, camera)
        if -20 < x < WIDTH + 20 and HEIGHT * 0.40 < y < HEIGHT + 30:
            s = scale * 0.95
            sway = math.sin(t * 2.3 + phase + y_src * 0.02) * 7.5 * s
            color = (238, 203, 91, 78) if y_src > 610 else (133, 177, 80, 58)
            draw.line([(x, y), (x + sway, y - length * s)], fill=color, width=1)


def draw_animated_life(frame: Image.Image, camera: tuple[float, float, float, float], t: float) -> Image.Image:
    layer = Image.new("RGBA", frame.size, (0, 0, 0, 0))
    draw_grass_glints(layer, camera, t)
    draw_smoke(layer, camera, t)
    layer = layer.filter(ImageFilter.GaussianBlur(1.8))
    draw = ImageDraw.Draw(layer)

    draw_path_farmer_motion(draw, camera, t)
    for src_x, src_y, base_s, offset in [
        (870, 755, 0.40, 0.0),
        (1085, 700, 0.35, 1.5),
        (1305, 678, 0.36, 3.1),
    ]:
        draw_subtle_worker_motion(draw, camera, src_x, src_y, base_s, t * 4.1 + offset)
    draw_buffalo_motion(draw, camera, t)

    bird_alpha = int(95 + math.sin(t * 1.7) * 30)
    for i in range(4):
        bx = 120 + ((t * 18 + i * 145) % 650)
        by = 190 + math.sin(t * 1.2 + i) * 18 + i * 12
        wing = math.sin(t * 7 + i) * 9
        draw.arc([bx - 12, by - wing, bx + 8, by + 12], 205, 340, fill=(54, 48, 38, bird_alpha), width=2)
        draw.arc([bx + 6, by - wing, bx + 26, by + 12], 200, 335, fill=(54, 48, 38, bird_alpha), width=2)

    frame.alpha_composite(layer)
    return frame


def draw_text_overlay(img: Image.Image, t: float) -> None:
    selected = None
    for start, end, title, sub in TEXT_SEGMENTS:
        if start <= t < end:
            fade = min(smoothstep((t - start) / 0.7), smoothstep((end - t) / 0.7))
            selected = title, sub, fade
            break
    if not selected:
        return
    title, sub, alpha = selected
    overlay = Image.new("RGBA", img.size, (0, 0, 0, 0))
    od = ImageDraw.Draw(overlay)
    for i in range(420):
        y = HEIGHT - 420 + i
        a = int(lerp(0, 178, i / 420) * alpha)
        od.line([(0, y), (WIDTH, y)], fill=(16, 19, 15, a))

    draw = ImageDraw.Draw(overlay)
    max_width = WIDTH - 112
    title_lines = wrap_text(draw, title, FONT_TITLE, max_width)
    sub_lines = wrap_text(draw, sub, FONT_SUB, max_width)
    total_h = len(title_lines) * 70 + len(sub_lines) * 45 + 24
    y = HEIGHT - 250 - total_h / 2

    for line in title_lines:
        tw, _ = text_size(draw, line, FONT_TITLE)
        x = (WIDTH - tw) / 2
        draw.text((x + 3, y + 4), line, font=FONT_TITLE, fill=(0, 0, 0, int(150 * alpha)))
        draw.text((x, y), line, font=FONT_TITLE, fill=(255, 246, 222, int(255 * alpha)))
        y += 70
    y += 6
    for line in sub_lines:
        tw, _ = text_size(draw, line, FONT_SUB)
        x = (WIDTH - tw) / 2
        draw.text((x + 2, y + 3), line, font=FONT_SUB, fill=(0, 0, 0, int(132 * alpha)))
        draw.text((x, y), line, font=FONT_SUB, fill=(240, 225, 183, int(255 * alpha)))
        y += 45

    img.alpha_composite(overlay)


def render_frame(source: Image.Image, t: float) -> Image.Image:
    frame, camera = crop_camera(source, t)
    frame = apply_wind_sway(frame, t)
    frame = draw_animated_life(frame, camera, t)
    light = Image.new("RGBA", frame.size, (255, 184, 86, 0))
    ld = ImageDraw.Draw(light)
    glow_x = WIDTH * (0.76 + math.sin(t * 0.12) * 0.04)
    glow_y = HEIGHT * 0.12
    for i in range(7, 0, -1):
        r = i * 58
        ld.ellipse([glow_x - r, glow_y - r, glow_x + r, glow_y + r], fill=(255, 188, 92, int(i * 5)))
    frame.alpha_composite(light.filter(ImageFilter.GaussianBlur(16)))
    frame.alpha_composite(VIGNETTE)
    draw_text_overlay(frame, t)
    return frame.convert("RGB")


def run(cmd: list[str]) -> subprocess.CompletedProcess:
    return subprocess.run(cmd, check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)


def probe_duration(ffmpeg: str, path: Path) -> float | None:
    proc = subprocess.run([ffmpeg, "-i", str(path), "-f", "null", "-"], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
    match = re.search(r"Duration: (\d+):(\d+):(\d+\.\d+)", proc.stderr)
    if not match:
        return None
    hours, minutes, seconds = match.groups()
    return int(hours) * 3600 + int(minutes) * 60 + float(seconds)


def render_visual(ffmpeg: str, source: Image.Image) -> None:
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
            frame = render_frame(source, t)
            proc.stdin.write(frame.tobytes())
            if frame_idx % 90 == 0:
                print(f"Rendered frame {frame_idx}/{TOTAL_FRAMES}")
    finally:
        proc.stdin.close()
    stderr = proc.stderr.read().decode("utf-8", errors="replace") if proc.stderr else ""
    return_code = proc.wait()
    if return_code != 0:
        raise RuntimeError(stderr)


def mux_final(ffmpeg: str) -> None:
    if not VOICE_MP3.exists():
        raise FileNotFoundError(f"Missing voice file: {VOICE_MP3}")
    if not MUSIC_WAV.exists():
        raise FileNotFoundError(f"Missing music file: {MUSIC_WAV}")
    filter_complex = (
        "[1:a]volume=1.35,adelay=650|650,apad=pad_dur=30[voice];"
        "[2:a]volume=0.20[music];"
        "[voice][music]amix=inputs=2:duration=longest:dropout_transition=2[aout]"
    )
    cmd = [
        ffmpeg,
        "-y",
        "-i",
        str(VISUAL_MP4),
        "-i",
        str(VOICE_MP3),
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
    run(cmd)


rng = np.random.default_rng(48)
GRASS_BLADES: list[tuple[float, float, float, float]] = []
for _ in range(520):
    if rng.random() < 0.78:
        x = rng.uniform(690, 1490)
        y = rng.uniform(590, 980)
    else:
        x = rng.uniform(300, 880)
        y = rng.uniform(640, 990)
    length = rng.uniform(8, 24)
    phase = rng.uniform(0, math.tau)
    GRASS_BLADES.append((x, y, length, phase))


def main() -> int:
    if not SOURCE_IMAGE.exists():
        raise FileNotFoundError(f"Missing source image: {SOURCE_IMAGE}")
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    ffmpeg = imageio_ffmpeg.get_ffmpeg_exe()
    print(f"Using ffmpeg: {ffmpeg}")
    source = Image.open(SOURCE_IMAGE).convert("RGB")
    print(f"Source image: {SOURCE_IMAGE} {source.size}")
    print("Rendering animated 3D countryside background...")
    render_visual(ffmpeg, source)
    print("Muxing voiceover and music...")
    mux_final(ffmpeg)
    duration = probe_duration(ffmpeg, FINAL_MP4)
    print(f"Done: {FINAL_MP4}")
    if duration:
        print(f"Duration: {duration:.2f}s")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
