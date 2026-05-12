# Assets folder

Đặt các file media của video tại đây.

## Files cần có

| File | Mô tả | Nguồn |
|---|---|---|
| `scene1.mp4` ... `scene6.mp4` | 6 clip video, mỗi clip 10s, 1080×1920 hoặc tỉ lệ 9:16 | Animate từ ảnh bằng Kling / Runway |
| `narration.mp3` | Voiceover toàn bộ script, ~58s | ElevenLabs / Zalo TTS / FPT.AI |
| `music.mp3` | Nhạc nền instrumental, ≥60s | Pixabay / Suno AI |

Nếu chưa có video animate, có thể dùng ảnh tĩnh (`scene1.jpg` ...) và đổi `mediaType: "image"` trong `src/scenes.ts`.

## Sau khi đặt assets

```bash
npm run dev      # mở Remotion Studio để preview
```

Để render ra MP4:

```bash
npx remotion render Story out/story.mp4
```
