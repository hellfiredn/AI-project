function escapeHtml(text) {
  return String(text)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

export function buildSeoPost({
  kind = "voucher_checklist",
  todayISO,
  seoTitle,
  metaDescription,
  disclosure,
  couponUrl,
  dealUrl,
  monthLabel,
}) {
  const safeCouponUrl = escapeHtml(couponUrl);
  const safeDealUrl = escapeHtml(dealUrl);

  const compareMarketplaces = `
<p><strong>${escapeHtml(metaDescription)}</strong></p>

<h2>Vì sao “cùng một món” nhưng giá lại chênh giữa các sàn?</h2>
<p>Khi mua hàng tạp hóa online (bột giặt, nước rửa chén, giấy vệ sinh, snack, đồ gia dụng…), giá cuối cùng bạn trả thường không chỉ là giá niêm yết. Nó còn phụ thuộc vào <strong>mã giảm giá</strong>, <strong>freeship</strong>, <strong>combo/khuyến mãi của shop</strong>, và <strong>phí vận chuyển</strong>. Vì vậy, cách đúng nhất là so sánh <em>giá sau khi áp mã + ship</em> ở thời điểm chốt đơn.</p>

<h2>So sánh nhanh: Shopee vs Lazada vs Tiki vs TikTok Shop</h2>
<h3>Shopee: nhiều mã, biến động nhanh theo khung giờ</h3>
<ul>
  <li>Phù hợp khi bạn chịu khó canh khung giờ mở mã và chia đơn tối ưu freeship.</li>
  <li>Nên ưu tiên shop uy tín/đánh giá tốt, xem kỹ điều kiện mã theo ngành hàng.</li>
</ul>

<h3>Lazada: mạnh về voucher theo ngành hàng và chiến dịch</h3>
<ul>
  <li>Thường có mã theo chiến dịch/ngành hàng, có lúc “ăn” hơn khi mua combo nhiều món.</li>
  <li>Nên kiểm tra danh mục áp mã, và tổng tiền sau mã trước khi bấm đặt.</li>
</ul>

<h3>Tiki: lợi thế vận chuyển/độ ổn định với sản phẩm chính hãng</h3>
<ul>
  <li>Hay hợp với các sản phẩm cần giao nhanh, tiêu chuẩn đóng gói rõ ràng.</li>
  <li>Nên so sánh giá sau ưu đãi với các sàn khác vì mã có thể ít hơn theo từng thời điểm.</li>
</ul>

<h3>TikTok Shop: giá tốt theo livestream/khung giờ, cần so sánh kỹ</h3>
<ul>
  <li>Có thể rẻ nhờ mã theo livestream, nhưng điều kiện/khung giờ thay đổi nhanh.</li>
  <li>Nên kiểm tra đánh giá shop, phí ship, và so sánh “giá sau mã” với các sàn khác trước khi chốt.</li>
</ul>

<h2>Checklist 5 bước để chọn nơi mua rẻ (không bị “ảo giảm giá”)</h2>
<h3>1) Chuẩn hóa sản phẩm để so sánh đúng</h3>
<ul>
  <li>So sánh cùng dung tích/khối lượng (ví dụ 3.6L vs 2.6L).</li>
  <li>Tính nhanh “giá trên 100g/100ml” với hàng tiêu dùng để tránh nhầm.</li>
</ul>

<h3>2) Tính “giá thật” = (giá niêm yết − mã) + phí ship</h3>
<ul>
  <li>Nếu mã xung đột, thử <strong>chia đơn</strong>: một đơn tối ưu freeship, một đơn tối ưu mã sàn.</li>
  <li>Kiểm tra điều kiện thanh toán (ví/thẻ) để tránh lỗi “mã không áp dụng”.</li>
</ul>

<h3>3) Ưu tiên shop uy tín hơn vài nghìn đồng chênh</h3>
<ul>
  <li>Xem đánh giá, tỷ lệ phản hồi, số đơn đã bán; đọc 1–2 review gần nhất.</li>
  <li>Với hàng tiêu dùng, ưu tiên hạn sử dụng rõ ràng, đóng gói chắc chắn.</li>
</ul>

<h3>4) Canh khung giờ sale để “bắt” freeship/mã sàn</h3>
<p>Các khung giờ phổ biến thường là 0h, 9h, 12h, 20h–21h (tùy sàn/chương trình). Bạn nên chuẩn bị giỏ trước 3–5 phút, vào sớm 1–2 phút trước giờ mở mã để tăng tỷ lệ áp được mã tốt.</p>

<h3>5) Lưu lại nguồn tổng hợp mã/deal để kiểm tra nhanh</h3>
<p>Để tiết kiệm thời gian mỗi lần mua sắm, bạn có thể ghé các trang tổng hợp trên Tạp Hóa Giảm Giá:</p>
<ul>
  <li>Trang tổng hợp mã giảm giá/coupon: <a href="${safeCouponUrl}" rel="nofollow">Xem coupon mới nhất</a></li>
  <li>Trang tổng hợp deal đáng mua: <a href="${safeDealUrl}" rel="nofollow">Xem deal đang hot</a></li>
</ul>

<h2>Gợi ý: mua gì thì sàn nào thường “dễ rẻ” hơn?</h2>
<ul>
  <li><strong>Đồ tạp hóa mua đều</strong> (snack, mì, đồ khô): thường rẻ khi mua combo + áp mã sàn/freeship.</li>
  <li><strong>Hóa mỹ phẩm</strong> (nước giặt, nước rửa chén): nên so sánh giá theo dung tích và canh ngày đôi/chiến dịch.</li>
  <li><strong>Đồ gia dụng nhỏ</strong>: ưu tiên shop uy tín, bảo hành rõ ràng; đừng hy sinh chất lượng vì chênh nhỏ.</li>
</ul>

<h2>Kết luận</h2>
<p>Không có một sàn nào rẻ nhất cho mọi đơn hàng. Cách nhanh nhất để mua rẻ là: so sánh <strong>giá thật sau mã + ship</strong>, canh đúng khung giờ, và ưu tiên shop uy tín. Khi cần cập nhật mã/deal theo ngày, bạn cứ ghé lại trang coupon/deal để kiểm tra trước khi chốt đơn.</p>

<hr />
<p><em>${escapeHtml(disclosure)}</em></p>
`.trim();

  const voucherChecklist = `
<p><strong>${escapeHtml(metaDescription)}</strong></p>

<h2>Vì sao nên “canh giờ vàng” để săn voucher?</h2>
<p>Trong các đợt sale, mã giảm giá thường có giới hạn số lượt dùng hoặc giới hạn ngân sách. Nếu bạn vào sớm, khả năng áp được mã tốt cao hơn; vào muộn, mã có thể hết hoặc điều kiện thay đổi. Vì vậy, một checklist săn deal theo từng bước sẽ giúp bạn tiết kiệm đều đặn mỗi ngày.</p>

<h2>Checklist săn voucher & freeship (áp dụng cho Shopee/Tiki/Lazada/TikTok Shop)</h2>
<h3>1) Chuẩn bị trước 3–5 phút</h3>
<ul>
  <li>Đăng nhập sẵn, cập nhật địa chỉ nhận hàng và phương thức thanh toán.</li>
  <li>Thêm sản phẩm vào giỏ và chọn đúng phân loại/màu/size (tránh mất thời gian).</li>
  <li>Kiểm tra điều kiện mã: đơn tối thiểu, ngành hàng, phương thức thanh toán, khung giờ.</li>
</ul>

<h3>2) Ưu tiên mã theo thứ tự “giá trị thật”</h3>
<ul>
  <li><strong>Freeship</strong>: giảm phí vận chuyển trực tiếp, thường “dễ hụt” nhất.</li>
  <li><strong>Mã sàn</strong>: thường giảm sâu nhưng điều kiện chặt (ngành hàng/khung giờ).</li>
  <li><strong>Mã shop</strong>: có thể cộng dồn với mã sàn ở một số chương trình.</li>
  <li><strong>Hoàn xu/hoàn tiền</strong>: phù hợp khi bạn mua đều, tối ưu về dài hạn.</li>
</ul>

<h3>3) Cách xử lý khi “mã không áp dụng”</h3>
<ul>
  <li>Đổi sản phẩm cùng ngành hàng (một số mã giới hạn danh mục).</li>
  <li>Chia đơn: một đơn để lấy mã sàn, một đơn để tối ưu freeship.</li>
  <li>Đổi kênh thanh toán (thẻ/ ví điện tử) nếu mã yêu cầu.</li>
  <li>Thử lại sau 30–60 giây: hệ thống có thể đang quá tải giờ cao điểm.</li>
</ul>

<h2>Mẹo tối ưu: “gom mã” theo ngày để đỡ bỏ sót</h2>
<p>Bạn có thể tạo thói quen kiểm tra nhanh danh sách mã theo ngày/tháng và lịch sale để chọn đúng thời điểm mua. Nếu cần một nơi tổng hợp để tham khảo nhanh, bạn xem:</p>
<ul>
  <li>Trang tổng hợp mã giảm giá: <a href="${safeCouponUrl}" rel="nofollow">Tổng hợp coupon mới nhất</a></li>
  <li>Trang tổng hợp deal hot: <a href="${safeDealUrl}" rel="nofollow">Danh sách deal đáng mua</a></li>
</ul>

<h2>Gợi ý thời điểm săn mã hôm nay (${escapeHtml(todayISO)})</h2>
<p>Khung giờ phổ biến thường rơi vào 0h, 9h, 12h, 20h và 21h (tùy sàn/chương trình). Bạn nên vào trước giờ mở mã 1–2 phút để sẵn sàng bấm áp mã.</p>

<h2>Lưu ý quan trọng khi săn deal</h2>
<ul>
  <li>So sánh giá sau khi áp mã (đừng chỉ nhìn % giảm).</li>
  <li>Ưu tiên shop uy tín, có đánh giá tốt và chính sách đổi trả rõ ràng.</li>
  <li>Kiểm tra phí vận chuyển và thời gian giao hàng trước khi chốt.</li>
</ul>

<h2>Kết luận</h2>
<p>Nếu bạn áp dụng checklist trên, việc săn voucher/freeship sẽ “dễ thở” hơn rất nhiều và tiết kiệm được đều đặn theo từng đơn. Khi cần cập nhật nhanh mã và deal đang chạy, bạn có thể ghé lại 2 trang tổng hợp bên trên để tham khảo.</p>

<hr />
<p><em>${escapeHtml(disclosure)}</em></p>
`.trim();

  const saleCalendar = `
<p><strong>${escapeHtml(metaDescription)}</strong></p>

<h2>Lịch sale ${escapeHtml(monthLabel || "")}: nên canh mốc nào?</h2>
<p>Các sàn TMĐT thường có “mốc đôi” (ví dụ 6.6, 7.7, 8.8…), ngày lương về, hoặc các chiến dịch theo mùa. Lịch dưới đây giúp bạn lên kế hoạch mua sắm sớm: gom giỏ, canh mã freeship, và chọn thời điểm chốt đơn để tối ưu giá sau khi áp mã.</p>
<p><strong>Lưu ý:</strong> khung giờ/mức ưu đãi có thể thay đổi theo từng chiến dịch và từng ngành hàng. Bạn nên kiểm tra trực tiếp trên app/sàn ở thời điểm mua.</p>

<h2>3 việc cần làm trước khi vào ngày sale</h2>
<h3>1) Gom giỏ & theo dõi giá</h3>
<ul>
  <li>Thêm sản phẩm vào giỏ trước 1–2 ngày để tránh “cháy” màu/size/phân loại.</li>
  <li>Chụp lại giá hoặc ghi chú giá tham chiếu để so sánh sau khi áp mã.</li>
  <li>Ưu tiên shop uy tín, đánh giá tốt và chính sách đổi trả rõ ràng.</li>
</ul>
<h3>2) Chuẩn bị mã: freeship → mã sàn → mã shop</h3>
<ul>
  <li>Freeship thường hết nhanh; vào sớm 1–2 phút trước giờ mở mã.</li>
  <li>Mã sàn thường giảm sâu nhưng có điều kiện (đơn tối thiểu/ngành hàng/thanh toán).</li>
  <li>Mã shop có thể cộng dồn (tùy chương trình), nên nhớ bấm “Lưu” sớm.</li>
</ul>
<h3>3) Chuẩn bị thanh toán</h3>
<ul>
  <li>Đăng nhập sẵn, cập nhật địa chỉ nhận hàng, kiểm tra phương thức thanh toán.</li>
  <li>Nếu mã yêu cầu ví/thẻ, hãy liên kết và xác thực trước ngày sale.</li>
</ul>

<h2>Lịch sale gợi ý theo sàn</h2>
<h3>Shopee</h3>
<ul>
  <li><strong>Mốc đôi:</strong> ${escapeHtml(monthLabel || "")} (ưu tiên canh ngày đôi và các đợt flash sale).</li>
  <li><strong>Khung giờ hay gặp:</strong> 0h, 9h, 12h, 20h–21h (tùy chiến dịch).</li>
  <li><strong>Mẹo:</strong> chia đơn để tối ưu freeship và mã sàn nếu điều kiện xung đột.</li>
</ul>

<h3>Lazada</h3>
<ul>
  <li><strong>Mốc đôi:</strong> thường bám sát ngày đôi và các chiến dịch theo mùa.</li>
  <li><strong>Mẹo:</strong> kiểm tra mã theo ngành hàng; nhiều mã giới hạn danh mục.</li>
  <li><strong>Gợi ý:</strong> canh voucher vào đầu khung giờ để tránh hết lượt.</li>
</ul>

<h3>Tiki</h3>
<ul>
  <li><strong>Mốc sale:</strong> thường có các đợt ưu đãi theo thương hiệu/ngành hàng và chương trình theo tuần.</li>
  <li><strong>Mẹo:</strong> ưu tiên sản phẩm Tiki Trading/TikiNow (nếu phù hợp) để tối ưu vận chuyển.</li>
  <li><strong>Gợi ý:</strong> xem điều kiện mã theo phương thức thanh toán.</li>
</ul>

<h3>TikTok Shop</h3>
<ul>
  <li><strong>Mốc sale:</strong> hay có ưu đãi theo livestream/khung giờ, cộng thêm mã theo chiến dịch.</li>
  <li><strong>Mẹo:</strong> canh mã trong livestream và so sánh giá sau mã với các sàn khác.</li>
  <li><strong>Gợi ý:</strong> kiểm tra phí ship và thời gian giao hàng trước khi chốt.</li>
</ul>

<h2>CTA: cập nhật nhanh mã & deal đang chạy</h2>
<p>Để tránh bỏ lỡ mã mới và deal theo ngày, bạn có thể ghé các trang tổng hợp sau trên Tạp Hóa Giảm Giá:</p>
<ul>
  <li><a href="${safeCouponUrl}" rel="nofollow">Trang tổng hợp mã giảm giá/coupon mới nhất</a></li>
  <li><a href="${safeDealUrl}" rel="nofollow">Trang tổng hợp deal đáng mua</a></li>
</ul>

<h2>Kết luận</h2>
<p>Lịch sale chỉ thật sự “có giá trị” khi bạn chuẩn bị sẵn giỏ hàng, hiểu thứ tự ưu tiên mã và canh đúng khung giờ. Nếu bạn mua đều trong tháng, hãy lưu lại bài này để đối chiếu nhanh và ghé trang coupon/deal để cập nhật ưu đãi mới.</p>

<hr />
<p><em>${escapeHtml(disclosure)}</em></p>
`.trim();

  const contentHtml =
    kind === "sale_calendar" ? saleCalendar : kind === "compare_marketplaces" ? compareMarketplaces : voucherChecklist;
  return { seoTitle, metaDescription, contentHtml };
}
