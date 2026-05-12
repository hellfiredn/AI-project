# Script Mẫu — "Chén Trà Của Bà"

**Thời lượng:** 60 giây · **Định dạng:** Dọc 1080×1920 · **Chủ đề:** Lòng biết ơn / Ký ức gia đình

---

## Toàn bộ narration (đọc liên tục, ~155 từ)

> Bà tôi có một chén trà sứ đã sứt mẻ một góc.
>
> Mỗi sáng, bà rót trà bằng chính cái chén ấy, dù trong tủ còn cả bộ trà mới tinh con cháu mua tặng.
>
> Tôi từng hỏi sao bà không dùng bộ mới cho đẹp. Bà chỉ cười.
>
> Một chiều mưa, bà kể cho tôi nghe... rằng cái chén ấy là của ông để lại, ngày ông đi bộ đội.
>
> Ông đã uống nước trong chén đó suốt những năm xa nhà. Khi trở về, ông đưa nó lại cho bà, bảo: "Giữ giùm anh nhé."
>
> Hơn nửa thế kỷ trôi qua. Ông đã đi xa. Nhưng cái chén vẫn còn đó, mỗi sáng bà rót trà như rót lại cả một đời người.
>
> Có những thứ trong đời, sứt mẻ rồi mà lại quý hơn vạn lần đồ nguyên vẹn.
>
> Vì nó mang trong mình một câu chuyện không gì thay thế được.

---

## Phân cảnh chi tiết (6 cảnh × 10 giây)

### Cảnh 1 — Hook (0:00–0:10)

**Narration:**
> Bà tôi có một chén trà sứ đã sứt mẻ một góc. Mỗi sáng, bà rót trà bằng chính cái chén ấy.

**Image prompt:**
```
Extreme close-up of an old, chipped porcelain Vietnamese tea cup on a worn
wooden table, soft morning light through bamboo curtains, steam rising,
shallow depth of field, cinematic, film grain, warm tones, 35mm lens
```

**Animate prompt (Kling/Runway):**
```
Slow push-in toward the tea cup, gentle steam rising upward,
subtle particles floating in light beam, static camera
```

---

### Cảnh 2 — Bối cảnh (0:10–0:20)

**Narration:**
> Tôi từng hỏi sao bà không dùng bộ mới cho đẹp. Bà chỉ cười.

**Image prompt:**
```
A wrinkled Vietnamese grandmother in traditional ao ba ba, sitting on a
wooden chair in a rural countryside home, holding the chipped tea cup,
gentle smile, sunlight from the side window, warm golden hour,
cinematic, shot on Kodak Portra 400, soft focus background
```

**Animate prompt:**
```
The grandmother slowly raises the cup toward her lips, eyes closed gently,
subtle smile forming, very slow micro-movements, intimate handheld feel
```

---

### Cảnh 3 — Hồi tưởng (0:20–0:30)

**Narration:**
> Một chiều mưa, bà kể cho tôi nghe rằng cái chén ấy là của ông để lại, ngày ông đi bộ đội.

**Image prompt:**
```
Vintage 1960s Vietnam, young man in green soldier uniform standing in
a rural courtyard, holding the same porcelain tea cup, rainy afternoon,
muted desaturated colors, faded photograph aesthetic, nostalgic mood,
overcast sky, wet stone path
```

**Animate prompt:**
```
Subtle rain falling, the soldier slowly looks down at the cup in his hands,
slight wind moving his uniform collar, melancholic atmosphere
```

---

### Cảnh 4 — Lời hứa (0:30–0:40)

**Narration:**
> Ông đã uống nước trong chén đó suốt những năm xa nhà. Khi trở về, ông đưa nó lại cho bà, bảo: "Giữ giùm anh nhé."

**Image prompt:**
```
Close-up of two pairs of hands meeting — a young soldier's calloused
hand passing the chipped porcelain cup to a young woman's hand,
both wearing simple cotton clothing, soft natural light, rural Vietnam
1970s, intimate moment, shallow depth of field, romantic but quiet
```

**Animate prompt:**
```
The hands slowly come together, the cup gently passes from one hand
to the other, fingers briefly touching, very slow tender motion
```

---

### Cảnh 5 — Hiện tại (0:40–0:50)

**Narration:**
> Hơn nửa thế kỷ trôi qua. Ông đã đi xa. Nhưng cái chén vẫn còn đó, mỗi sáng bà rót trà như rót lại cả một đời người.

**Image prompt:**
```
The same elderly grandmother pouring tea from a clay teapot into the
chipped porcelain cup, on a wooden tray with old framed black-and-white
photograph of the soldier behind her, morning light, deeply emotional
atmosphere, cinematic, warm tones with hint of melancholy
```

**Animate prompt:**
```
Tea slowly pours into the cup, steam rises, grandmother's hand trembles
slightly, camera does very slow dolly-back to reveal the framed photograph
behind, emotional reveal
```

---

### Cảnh 6 — Bài học / chốt (0:50–1:00)

**Narration:**
> Có những thứ trong đời, sứt mẻ rồi mà lại quý hơn vạn lần đồ nguyên vẹn. Vì nó mang trong mình một câu chuyện không gì thay thế được.

**Image prompt:**
```
Macro close-up of the chipped edge of the porcelain tea cup, dust
particles floating in a sunbeam, extremely shallow depth of field,
the chip catching light beautifully, poetic mood, like the cup itself
is speaking, cinematic still life
```

**Animate prompt:**
```
Camera slowly rotates around the cup's chipped edge, light shifts
gradually across the surface, dust motes drift through the sunbeam,
contemplative finale
```

---

## Hướng dẫn render với assets

1. Sinh 6 ảnh từ image prompts ở trên (Midjourney / Flux)
2. Animate 6 ảnh thành 6 clip 10s (Kling / Runway)
3. Sinh voiceover từ toàn bộ narration → 1 file MP3 ~58s (ElevenLabs / Zalo TTS)
4. Đặt assets vào `remotion-story/public/`:
   ```
   public/
     scene1.mp4 .. scene6.mp4
     narration.mp3
     music.mp3       (nhạc nền instrumental, volume 20%)
   ```
5. Chạy `npx remotion studio` để preview, `npx remotion render` để xuất MP4

## Gợi ý nhạc nền

- Tìm trên Pixabay: "emotional piano", "vietnamese traditional", "đàn tranh"
- Hoặc dùng Suno AI với prompt: `slow emotional vietnamese traditional piano with subtle dan tranh, melancholic, nostalgic, instrumental, 60 seconds`
- Volume nhạc nền: 15–25%, voiceover: 100%
