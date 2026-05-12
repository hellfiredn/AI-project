# remotion-story

Template Remotion để render video kể chuyện dọc (1080×1920, 60s) từ assets AI:
- 6 clip cảnh (từ Kling/Runway) — Ken Burns + fade in/out
- 1 voiceover liên tục (từ ElevenLabs/Zalo TTS)
- 1 nhạc nền (volume mặc định 18%)
- Subtitle tiếng Việt fade-up, font Be Vietnam Pro

## Quick start

```bash
# 1. Vào folder
cd remotion-story

# 2. Đặt assets vào public/
#    scene1.mp4 .. scene6.mp4 + narration.mp3 + music.mp3
#    (xem public/README.md)

# 3. Preview
npm run dev

# 4. Render ra MP4
npx remotion render Story out/story.mp4
```

## Tùy biến

- **Số cảnh / thời lượng / caption**: sửa `src/scenes.ts`
- **Animation Ken Burns / fade**: sửa `src/Scene.tsx`
- **Layout caption**: sửa `src/Scene.tsx` (chỗ `paddingBottom: 360`, `fontSize`, v.v.)
- **Kích thước video**: sửa `WIDTH` / `HEIGHT` trong `src/scenes.ts`
- **Volume nhạc**: sửa `MUSIC_VOLUME` trong `src/scenes.ts`

## Đổi sang dùng ảnh tĩnh

Nếu chưa có video animate, dùng ảnh JPG/PNG + Ken Burns sẵn có:

```ts
// src/scenes.ts
{ id: "scene1", mediaSrc: "scene1.jpg", mediaType: "image", ... }
```

## Render hàng loạt (batch)

```bash
# Render nhiều version với props khác nhau
npx remotion render Story out/v1.mp4 --props='{"variant":1}'
```

Để parametrize, mở rộng `StoryComposition` với props và `calculateMetadata`.
