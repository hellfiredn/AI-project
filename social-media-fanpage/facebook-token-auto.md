# Tu dong lay Facebook Page token

Facebook khong cho script tu lay token bang username/password. Cach dung dung la OAuth:

1. Tao Facebook app trong Meta for Developers.
2. Them Valid OAuth Redirect URI dung bang:

```text
https://www.facebook.com/connect/login_success.html
```

3. Trong `facebook-env.local.ps1`, them cac bien:

```powershell
$env:FACEBOOK_APP_ID = "APP_ID_CUA_BAN"
$env:FACEBOOK_APP_SECRET = "APP_SECRET_CUA_BAN"
$env:FACEBOOK_REDIRECT_URI = "https://www.facebook.com/connect/login_success.html"
```

4. Chay lenh ket noi token:

```powershell
. .\facebook-env.local.ps1
.\connect-facebook-page.ps1 -Save
```

Script se mo trinh duyet. Ban dang nhap Facebook va cap quyen mot lan. Sau khi trinh duyet chuyen den trang `facebook.com/connect/login_success.html`, copy toan bo URL tren thanh dia chi va paste vao terminal. Script se tu doi OAuth code lay Page token va luu lai vao `facebook-env.local.ps1`.

5. Dang anh:

```powershell
. .\facebook-env.local.ps1
$env:FACEBOOK_DRY_RUN = "0"
.\publish-facebook-page-photo.ps1 -ImageFile .\poster-yen-chung-facebook.png -MessageFile .\post-yen-chung-caption.txt
```

Quyen OAuth can co:

```text
pages_show_list,pages_manage_posts,pages_read_engagement
```
