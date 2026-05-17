import argparse
import datetime as dt
from PIL import Image, ImageDraw, ImageFont


def wrap_text(draw, text, font, max_width):
  words = text.split()
  lines = []
  cur = []
  for w in words:
    trial = (" ".join(cur + [w])).strip()
    box = draw.textbbox((0, 0), trial, font=font)
    if box[2] <= max_width or not cur:
      cur.append(w)
    else:
      lines.append(" ".join(cur))
      cur = [w]
  if cur:
    lines.append(" ".join(cur))
  return lines


def main():
  ap = argparse.ArgumentParser()
  ap.add_argument("--title", required=True)
  ap.add_argument("--out", required=True)
  ap.add_argument("--w", type=int, default=1200)
  ap.add_argument("--h", type=int, default=630)
  ap.add_argument("--brand", default="Tạp Hóa Giảm Giá")
  ap.add_argument("--date", default=None)
  args = ap.parse_args()

  W, H = args.w, args.h
  bg = Image.new("RGB", (W, H), (248, 250, 252))
  d = ImageDraw.Draw(bg)

  # Simple clean layout: top bar + card
  bar_h = int(H * 0.18)
  d.rectangle([0, 0, W, bar_h], fill=(249, 115, 22))  # orange

  card_margin = int(W * 0.06)
  card_top = int(bar_h * 0.65)
  d.rounded_rectangle(
    [card_margin, card_top, W - card_margin, H - card_margin],
    radius=36,
    fill=(255, 255, 255),
    outline=(226, 232, 240),
    width=4,
  )

  # Fonts: use default PIL font fallback (works without system fonts)
  try:
    title_font = ImageFont.truetype("DejaVuSans-Bold.ttf", 56)
    brand_font = ImageFont.truetype("DejaVuSans.ttf", 34)
    small_font = ImageFont.truetype("DejaVuSans.ttf", 28)
  except Exception:
    title_font = ImageFont.load_default()
    brand_font = ImageFont.load_default()
    small_font = ImageFont.load_default()

  brand_text = args.brand
  date_text = args.date or dt.datetime.now().strftime("%d/%m/%Y")

  # Brand on orange bar
  d.text((card_margin, int(bar_h * 0.35)), brand_text, font=brand_font, fill=(255, 255, 255))
  # Date right-aligned
  date_box = d.textbbox((0, 0), date_text, font=small_font)
  d.text((W - card_margin - (date_box[2] - date_box[0]), int(bar_h * 0.42)), date_text, font=small_font, fill=(255, 255, 255))

  # Title in card
  max_text_width = W - card_margin * 2 - 80
  lines = wrap_text(d, args.title, title_font, max_text_width)
  lines = lines[:3]
  y = card_top + 70
  for line in lines:
    d.text((card_margin + 40, y), line, font=title_font, fill=(15, 23, 42))
    y += 70

  # Small CTA line
  cta = "Mã giảm giá • Deal hot • Mua sắm thông minh"
  d.text((card_margin + 40, H - card_margin - 90), cta, font=small_font, fill=(100, 116, 139))

  bg.save(args.out, quality=90, method=6)


if __name__ == "__main__":
  main()
