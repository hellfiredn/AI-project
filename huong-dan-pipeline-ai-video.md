# Hướng dẫn Pipeline tạo Video Kể Chuyện bằng AI

Pipeline 5 bước, tối ưu cho video tiếng Việt 60–90 giây, đăng Facebook Reels / TikTok / YouTube Shorts.

---

## Bước 1 — Viết script (kịch bản)

**Công cụ:** Claude / ChatGPT / Gemini

**Cấu trúc script kể chuyện 60s hiệu quả (~150–170 từ tiếng Việt):**

| Đoạn | Thời lượng | Vai trò |
|---|---|---|
| Hook | 0–3s | Câu mở khiến người xem dừng lướt |
| Bối cảnh | 3–10s | Giới thiệu nhân vật / hoàn cảnh |
| Xung đột | 10–35s | Vấn đề, mâu thuẫn, biến cố |
| Cao trào | 35–50s | Đỉnh điểm cảm xúc |
| Kết & bài học | 50–60s | Câu chốt + CTA |

**Prompt mẫu cho AI:**

```
Viết script kể chuyện 60 giây tiếng Việt theo phong cách "chữa lành",
chủ đề: [TIỀN TỪ] (vd: lòng biết ơn / sự kiên nhẫn / tình mẹ).
Yêu cầu:
- 150–170 từ
- Chia 6 cảnh, mỗi cảnh 1 câu narration + 1 mô tả hình ảnh
- Giọng kể ngôi thứ ba, ấm áp, không sến
- Câu chốt cuối là một bài học sâu sắc, không dạy đời
```

---

## Bước 2 — Sinh voiceover (tiếng Việt)

**Khuyên dùng cho tiếng Việt:**

| Tool | Ưu | Nhược | Giá |
|---|---|---|---|
| **ElevenLabs** | Giọng tự nhiên nhất, có emotion | Tiếng Việt vẫn hơi "Tây hóa" | $5/tháng |
| **FPT.AI TTS** | Giọng Việt chuẩn, nhiều vùng miền | Hơi máy móc | Miễn phí có hạn ngạch |
| **Zalo AI TTS** | Giọng Việt rất tự nhiên | API hạn chế | Miễn phí |
| **Viettel AI TTS** | Giọng Bắc/Nam tốt | Cần đăng ký | Có gói free |

**Tips chỉnh voiceover:**
- Chèn dấu phẩy nhiều hơn bình thường để tạo nhịp nghỉ
- Dùng "..." ở chỗ cần lắng cảm xúc
- Đọc to script trước để check ngắt câu

Bạn đang dùng `voice_nam_tram_am.mp3` — giọng nam trầm ấm hợp video châm ngôn. Cho video kể chuyện, nên thử thêm 1 giọng nữ trẻ để A/B test.

---

## Bước 3 — Sinh hình ảnh từng cảnh

**Theo thứ tự ưu tiên cho phong cách kể chuyện cảm xúc:**

| Tool | Phù hợp | Giá |
|---|---|---|
| **Midjourney v6** | Đẹp nghệ thuật nhất, cinematic | $10/tháng |
| **Flux.1 [dev]** trên fal.ai / Replicate | Realistic tốt, kiểm soát ngôn ngữ tự nhiên | ~$0.025/ảnh |
| **Ideogram 2.0** | Text trong ảnh tốt nhất | Có gói free |
| **DALL·E 3** (qua ChatGPT) | Tiện, dễ chỉnh | $20/tháng (Plus) |

**Prompt formula cho ảnh kể chuyện:**

```
[Cinematic shot of] + [chủ thể] + [hành động] + [bối cảnh]
+ [ánh sáng] + [phong cách] + [mood]

Ví dụ:
"Cinematic shot of an elderly Vietnamese woman in a traditional ao ba ba,
sitting alone on a wooden porch at sunset, golden hour light,
film grain, melancholic mood, shot on 35mm"
```

**Quy tắc giữ nhất quán nhân vật giữa các cảnh:**
- Mô tả nhân vật bằng đúng những từ y hệt nhau ở mọi prompt
- Hoặc dùng **Midjourney `--cref`** (character reference) / **Flux Redux**
- Hoặc tạo 1 ảnh "model sheet" rồi dùng làm reference

---

## Bước 4 — Animate ảnh thành video

Đây là khâu nâng tầm video. Tool image-to-video tốt nhất 2025:

| Tool | Thời lượng/clip | Chất lượng | Giá | Ghi chú |
|---|---|---|---|---|
| **Kling 2.0** | 5–10s | ⭐⭐⭐⭐⭐ | ~$0.30/clip | Tốt nhất cho người Á Đông, motion mượt |
| **Runway Gen-3 Alpha Turbo** | 5–10s | ⭐⭐⭐⭐ | ~$0.50/clip | Camera control tốt |
| **Hailuo (MiniMax)** | 6s | ⭐⭐⭐⭐ | Free tier rộng | Rẻ, hợp test |
| **Sora** | tới 20s | ⭐⭐⭐⭐⭐ | $20/tháng (Plus) | Khó access ở VN |
| **Pika 2.0** | 5s | ⭐⭐⭐ | $10/tháng | Có hiệu ứng đặc biệt |

**Prompt animate hiệu quả:**
- Mô tả camera movement: "slow push in", "dolly back", "static shot", "handheld"
- Mô tả chuyển động chính: "she slowly turns her head", "wind blows her hair"
- Tránh chuyển động phức tạp như "she walks across the room then opens the door"

---

## Bước 5 — Ghép + nhạc + sub

**Tool ghép:**

| Tool | Phù hợp |
|---|---|
| **CapCut Desktop** | Free, dễ, đủ tính năng cho 95% nhu cầu |
| **DaVinci Resolve** | Free, pro-level color grading |
| **Premiere Pro** | Pro, tiền |
| **Remotion (code)** | Programmable, render hàng loạt → xem `remotion-story/` |

**Nhạc nền:**
- **Pixabay Music** / **YouTube Audio Library** — free, không bản quyền
- **Suno AI** — sinh nhạc theo prompt, tuyệt cho mood riêng
- **Epidemic Sound** — pro, $15/tháng

**Sub tiếng Việt:**
- CapCut auto-caption hỗ trợ tiếng Việt khá tốt
- Hoặc **Whisper** (OpenAI) → SRT → import CapCut

**Quy tắc sub cho video dọc:**
- Font sans-serif đậm (Be Vietnam, Inter, Montserrat)
- Đặt ở 60–70% chiều cao màn hình (chừa chỗ cho UI Facebook/TikTok)
- Tối đa 2 dòng, mỗi dòng 4–6 từ
- Highlight từ khóa bằng màu khác

---

## Chi phí ước tính cho 1 video 60s

| Khâu | Tool | Cost |
|---|---|---|
| Script | Claude (đã có sub) | $0 |
| Voiceover | ElevenLabs | ~$0.10 |
| 6 ảnh | Flux.1 dev | ~$0.15 |
| 6 clip animate | Kling | ~$1.80 |
| Ghép | CapCut | $0 |
| **Tổng** | | **~$2/video** |

Render 30 video/tháng ≈ $60. Có thể thấp hơn nếu xài free tier.

---

## Workflow đề xuất cho fanpage của bạn

Bạn đang chạy fanpage châm ngôn với Python pipeline. Để mở rộng sang video kể chuyện:

```
1. Soạn batch 5 chủ đề/tuần (Claude)
2. Generate script cho cả 5 trong 1 lần
3. Batch sinh ảnh qua Flux API (parallel)
4. Batch animate qua Kling API
5. Render hàng loạt qua Remotion (code, không cần mở app)
6. Auto post qua script Facebook bạn đã có
```

Khi cần scale, viết script Python orchestrator ghép cả 6 bước → 1 lệnh ra video xong xuôi.

---

## Tài nguyên đi kèm

- `script-mau-ke-chuyen.md` — script tiếng Việt 60s đã viết sẵn, có image prompts
- `remotion-story/` — project Remotion mẫu, render video từ ảnh + voiceover
