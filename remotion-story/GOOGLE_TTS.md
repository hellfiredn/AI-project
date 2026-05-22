# Google Cloud Text-to-Speech

## 1. Chuẩn bị Google Cloud

1. Tạo hoặc chọn một Google Cloud project.
2. Bật **Cloud Text-to-Speech API**.
3. Bật billing cho project.
4. Tạo service account và tải key JSON.
5. Set biến môi trường trỏ tới file key JSON:

```powershell
$env:GOOGLE_APPLICATION_CREDENTIALS="D:\keys\google-tts-key.json"
```

Không đặt file key vào Git. Folder này đã ignore `*.json`.

## 2. Tạo file MP3

Sửa nội dung trong:

```text
scripts/narration.example.txt
```

Chạy:

```bash
npm run tts:google
```

File audio sẽ được tạo tại:

```text
public/narration.mp3
```

Remotion đang đọc file này qua `VOICEOVER_SRC` trong `src/scenes.ts`.

## 3. Chọn giọng khác

Ví dụ dùng giọng nam:

```bash
node scripts/google-tts.mjs --text-file scripts/narration.example.txt --out public/narration.mp3 --voice vi-VN-Standard-B
```

Một số giọng tiếng Việt:

- `vi-VN-Standard-A`: nữ
- `vi-VN-Standard-B`: nam
- `vi-VN-Standard-C`: nữ
- `vi-VN-Standard-D`: nam
- `vi-VN-Wavenet-A`: nữ, chất lượng cao hơn
- `vi-VN-Wavenet-B`: nam, chất lượng cao hơn
- `vi-VN-Neural2-A`: nữ, neural
- `vi-VN-Neural2-D`: nam, neural

## 4. Tham số hữu ích

```bash
node scripts/google-tts.mjs \
  --text-file scripts/narration.example.txt \
  --out public/narration.mp3 \
  --voice vi-VN-Wavenet-A \
  --speaking-rate 0.95 \
  --pitch 0
```

`--speaking-rate` thấp hơn `1` sẽ đọc chậm hơn. `--pitch` chỉnh cao độ giọng.
