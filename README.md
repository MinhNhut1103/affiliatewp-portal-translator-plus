# AffiliateWP Portal Translator Plus

Dịch toàn bộ cổng thông tin AffiliateWP (Affiliate Portal) sang tiếng Việt + chuyển đổi định dạng tiền tệ VNĐ.

## Yêu cầu

- WordPress 5.0+
- PHP 7.4+
- [AffiliateWP](https://affiliatewp.com/)
- [AffiliateWP - Affiliate Portal](https://affiliatewp.com/add-ons/pro/affiliate-portal/)

## Tính năng

### Dịch tiếng Việt
Tự động dịch ~320+ chuỗi trong toàn bộ Affiliate Portal sang tiếng Việt:

- **Điều hướng:** Trang chủ, URL Liên kết, Thống kê, Biểu đồ, Hoa hồng, Thanh toán, Lượt truy cập, Ấn phẩm, Mã giảm giá, Cài đặt
- **Bảng điều khiển:** Thu nhập, tỷ lệ chuyển đổi, hoạt động gần đây
- **Đăng nhập / Đăng ký:** Form đầy đủ
- **Cài đặt hồ sơ:** Email thanh toán, thông báo
- **Cài đặt thanh toán:** Bank/Card, Payouts Service
- **URL Generator:** Trình tạo liên kết giới thiệu
- **Chiến dịch:** Campaign, Unique Links
- **Direct Links / Landing Pages / Custom Slug**
- **Tích hợp:** Lifetime Commissions, Multi-Tier, Order Details, Pushover, Store Credit
- **Admin Portal Settings:** Menu Links, Per Page, Sharing Options, Feedback
- **Bộ lọc thời gian:** Hôm nay, Hôm qua, Tuần này, Tháng này, Quý này, Năm nay...

### Định dạng tiền tệ VNĐ
Chuyển tất cả số tiền sang định dạng Việt Nam:
- Dấu phân cách hàng nghìn: `.` (1.000.000)
- Không có số thập phân
- Ký hiệu `₫` ở cuối (1.000.000₫)

### Yêu cầu plugin
Plugin chỉ hoạt động khi **AffiliateWP - Affiliate Portal** được kích hoạt. Nếu thiếu, plugin hiển thị thông báo trong admin và tự động vô hiệu hóa tất cả chức năng.

## Cài đặt

1. Tải plugin và giải nén vào `wp-content/plugins/affiliatewp-portal-translator-plus/`
2. Vào **Plugins** > kích hoạt **AffiliateWP Portal Translator Plus**
3. Vào menu **AffiliateWP Translator** trong admin sidebar
4. Bật **"Dịch sang tiếng Việt"** và/hoặc **"Định dạng tiền tệ VNĐ"**
5. Lưu cài đặt

## Cách hoạt động

Plugin sử dụng WordPress `gettext` filter để dịch chuỗi real-time, không ghi đè file gốc và không cần file .mo/.po. Hai text domain được xử lý:

- `affiliate-wp` — template files của AffiliateWP core
- `affiliatewp-affiliate-portal` — views, components, integrations của Portal add-on

## Tác giả

**Đỗ Minh Nhựt**

- Website: [https://dominhnhut.com](https://dominhnhut.com)
- GitHub: [@MinhNhut1103](https://github.com/MinhNhut1103)

## License

GPL v2 or later
