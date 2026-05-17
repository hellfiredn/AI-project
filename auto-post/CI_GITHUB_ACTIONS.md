# Chạy automation đăng bài SEO bằng GitHub Actions

Do môi trường sandbox hiện tại có thể bị chặn DNS/egress (dẫn tới lỗi `ENOTFOUND`), cách ổn định nhất để automation chạy mỗi tối là dùng GitHub Actions (runner có internet).

## 1) Thêm workflow

Workflow đã có sẵn tại:
- `/Users/caovien/Documents/private_project/AI-project/.github/workflows/taphoagiamgia-seo-nightly.yml`

Mặc định lịch chạy: **20:30 (Asia/Ho_Chi_Minh)** mỗi ngày.

## 2) Tạo GitHub Secrets

Vào repo GitHub → **Settings → Secrets and variables → Actions → New repository secret** và tạo tối thiểu:

**Bắt buộc**
- `WEBSITE_AUTHOR` (vd: `auto_bot`)
- `WEBSITE_APP_PASS` (Application Password WordPress)
- `WEBSITE_URL` (vd: `https://taphoagiamgia.com`)
- `WP_API_BASE` (vd: `https://taphoagiamgia.com/wp-json/wp/v2`)
- `WP_POSTS_ENDPOINT` (vd: `https://taphoagiamgia.com/wp-json/wp/v2/posts`)
- `WP_MEDIA_ENDPOINT` (vd: `https://taphoagiamgia.com/wp-json/wp/v2/media`)

**Khuyến nghị** (nếu không set sẽ dùng mặc định trong script/.env local của bạn)
- `CONTENT_TIMEZONE` (vd: `Asia/Ho_Chi_Minh`)
- `WP_POST_STATUS` (vd: `publish`)
- `WP_DEFAULT_CATEGORY_SLUG` (vd: `coupon`)
- `WP_DEFAULT_TAGS` (csv, vd: `tap-hoa-giam-gia,ma-giam-gia,san-deal,mua-sam-thong-minh`)
- `CONTENT_DISCLOSURE` (chuỗi disclosure affiliate)
- `WEBSITE_COUPON_URL`, `WEBSITE_DEAL_URL`
- `IMAGE_MAIN_WIDTH`, `IMAGE_MAIN_HEIGHT`, `IMAGE_FORMAT`, `IMAGE_ALT_PREFIX`

Lưu ý: workflow sẽ tự tạo `auto-post/.env` từ secrets khi chạy; không commit `.env` vào repo.

## 3) Test chạy thủ công

Vào tab **Actions** → chọn workflow “Tạp Hóa Giảm Giá: Đăng bài SEO mỗi tối” → **Run workflow**.

Nếu thành công, log sẽ in ra JSON có `postUrl` và `mediaUrl`.

