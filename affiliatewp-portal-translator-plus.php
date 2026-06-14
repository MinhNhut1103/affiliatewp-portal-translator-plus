<?php
/**
 * Plugin Name: AffiliateWP Portal Translator Plus
 * Plugin URI: https://dominhnhut.com
 * Description: Dịch toàn bộ cổng thông tin AffiliateWP (Affiliate Portal) sang tiếng Việt + chuyển đổi định dạng tiền tệ VNĐ.
 * Version: 1.0.0
 * Author: Đỗ Minh Nhựt
 * Author URI: https://dominhnhut.com
 * Text Domain: affiliatewp-portal-translator-plus
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AffiliateWP_Portal_Translator_Plus {

    private static $instance = null;
    private $loaded = false;
    const OPTION_KEY = 'affwp_translator_plus_settings';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'check_requirements' ), 0 );
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

        if ( $this->get_option( 'enable_translation' ) ) {
            $this->load_translation_hooks();
        }

        if ( $this->get_option( 'enable_vnd' ) ) {
            add_filter( 'affwp_currency', array( $this, 'vnd_currency_code' ) );
            add_filter( 'affwp_format_amount', array( $this, 'vnd_format_amount' ), 10, 5 );
            add_filter( 'affwp_vnd_currency_filter_before', array( $this, 'vnd_currency_filter' ), 10, 3 );
            add_filter( 'affwp_vnd_currency_filter_after', array( $this, 'vnd_currency_filter' ), 10, 3 );
            add_filter( 'affwp_decimal_count', '__return_zero' );
        }
    }

    public function check_requirements() {
        if ( class_exists( 'AffiliateWP_Affiliate_Portal' ) ) {
            return;
        }
        $this->remove_hooks();
        add_action( 'admin_notices', array( $this, 'missing_portal_notice' ) );
    }

    private function remove_hooks() {
        remove_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        remove_action( 'admin_init', array( $this, 'register_settings' ) );
        remove_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
        remove_filter( 'gettext', array( $this, 't_affwp' ) );
        remove_filter( 'gettext', array( $this, 't_portal' ) );
        remove_filter( 'ngettext', array( $this, 'tn_affwp' ) );
        remove_filter( 'gettext_with_context', array( $this, 'tc_affwp' ) );
        remove_filter( 'gettext_with_context', array( $this, 'tc_portal' ) );
        remove_filter( 'affwp_currency', array( $this, 'vnd_currency_code' ) );
        remove_filter( 'affwp_format_amount', array( $this, 'vnd_format_amount' ) );
        remove_filter( 'affwp_vnd_currency_filter_before', array( $this, 'vnd_currency_filter' ) );
        remove_filter( 'affwp_vnd_currency_filter_after', array( $this, 'vnd_currency_filter' ) );
        remove_filter( 'affwp_decimal_count', '__return_zero' );
    }

    public function missing_portal_notice() {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><strong>AffiliateWP Portal Translator Plus:</strong> yêu cầu <strong>AffiliateWP - Affiliate Portal</strong> để hoạt động. Vui lòng cài đặt và kích hoạt plugin này trước.</p>
        </div>
        <?php
    }

    private function get_option( $key ) {
        $settings = get_option( self::OPTION_KEY, array() );
        return isset( $settings[ $key ] ) ? (bool) $settings[ $key ] : false;
    }

    public function load_translation_hooks() {
        if ( $this->loaded ) {
            return;
        }
        $this->loaded = true;

        add_filter( 'gettext', array( $this, 't_affwp' ), 10, 3 );
        add_filter( 'gettext', array( $this, 't_portal' ), 10, 3 );
        add_filter( 'ngettext', array( $this, 'tn_affwp' ), 10, 5 );
        add_filter( 'gettext_with_context', array( $this, 'tc_affwp' ), 10, 4 );
        add_filter( 'gettext_with_context', array( $this, 'tc_portal' ), 10, 4 );
    }

    public function vnd_currency_code() {
        return 'VND';
    }

    public function vnd_format_amount( $formatted, $amount, $decimals, $decimal_sep, $thousands_sep ) {
        return number_format( (float) $amount, 0, ',', '.' );
    }

    public function vnd_currency_filter( $formatted, $currency, $amount ) {
        return $amount . '₫';
    }

    public function add_settings_page() {
        add_menu_page(
            'AffiliateWP Portal Translator Plus',
            'AffiliateWP Translator',
            'manage_options',
            'affwp-translator-plus',
            array( $this, 'render_settings_page' ),
            'dashicons-admin-site',
            30
        );
    }

    public function plugin_action_links( $links ) {
        $links[] = '<a href="' . admin_url( 'admin.php?page=affwp-translator-plus' ) . '">Cài đặt</a>';
        return $links;
    }

    public function register_settings() {
        register_setting( 'affwp_translator_plus_group', self::OPTION_KEY, array(
            'sanitize_callback' => array( $this, 'sanitize_settings' ),
        ) );
    }

    public function sanitize_settings( $input ) {
        return array(
            'enable_translation' => isset( $input['enable_translation'] ) ? 1 : 0,
            'enable_vnd'         => isset( $input['enable_vnd'] ) ? 1 : 0,
        );
    }

    public function render_settings_page() {
        $settings = get_option( self::OPTION_KEY, array() );
        ?>
        <div class="wrap">
            <h1>AffiliateWP Portal Translator Plus</h1>
            <p>Tác giả: <a href="https://dominhnhut.com" target="_blank">Đỗ Minh Nhựt</a></p>
            <form method="post" action="options.php">
                <?php settings_fields( 'affwp_translator_plus_group' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Dịch sang tiếng Việt</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[enable_translation]" value="1" <?php checked( 1, isset( $settings['enable_translation'] ) ? $settings['enable_translation'] : 0 ); ?>>
                                Bật dịch toàn bộ cổng thông tin AffiliateWP sang tiếng Việt
                            </label>
                            <p class="description">Dịch tất cả text trong Affiliate Portal: trang chủ, thống kê, hoa hồng, lượt truy cập, ấn phẩm, cài đặt, mã giảm giá, thanh toán...</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Định dạng tiền tệ VNĐ</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[enable_vnd]" value="1" <?php checked( 1, isset( $settings['enable_vnd'] ) ? $settings['enable_vnd'] : 0 ); ?>>
                                Chuyển định dạng số sang VNĐ: <code>1.000.000₫</code>
                            </label>
                            <p class="description">Dấu phân cách hàng nghìn là <code>.</code>, không có số thập phân, ký hiệu <code>₫</code> ở cuối.</p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary">Lưu cài đặt</button>
                </p>
            </form>
        </div>
        <?php
    }

    // ===== AFFILIATE-WP TEXT DOMAIN =====

    public function t_affwp( $translated, $original, $domain ) {
        if ( 'affiliate-wp' !== $domain ) {
            return $translated;
        }
        $map = array(
            'Affiliate URLs'                 => 'URL Liên kết',
            'Creatives'                      => 'Ấn phẩm',
            'Statistics'                     => 'Thống kê',
            'Graphs'                         => 'Biểu đồ',
            'Referrals'                      => 'Hoa hồng',
            'Payouts'                        => 'Thanh toán',
            'Visits'                         => 'Lượt truy cập',
            'Coupons'                        => 'Mã giảm giá',
            'Settings'                       => 'Cài đặt',
            'Log out'                        => 'Đăng xuất',
            'Feedback'                       => 'Phản hồi',

            'Your affiliate account is pending approval' => 'Tài khoản cộng tác viên của bạn đang chờ phê duyệt',
            'Your affiliate account is not active'       => 'Tài khoản cộng tác viên của bạn chưa được kích hoạt',
            'Your affiliate account request has been rejected' => 'Yêu cầu tài khoản cộng tác viên của bạn đã bị từ chối',
            'Your affiliate profile has been updated'    => 'Hồ sơ cộng tác viên của bạn đã được cập nhật',

            'Active'                         => 'Hoạt động',
            'Inactive'                       => 'Không hoạt động',
            'Pending'                        => 'Đang chờ',
            'Rejected'                       => 'Bị từ chối',

            'Percentage (%)'                 => 'Phần trăm (%)',
            'Flat %s'                        => 'Cố định %s',
            'Flat Rate Commission Per Product Sold' => 'Hoa hồng cố định theo sản phẩm',
            'Flat Rate Commission Per Order' => 'Hoa hồng cố định theo đơn hàng',

            'Unpaid Referrals'               => 'Hoa hồng chưa thanh toán',
            'Paid Referrals'                 => 'Hoa hồng đã thanh toán',
            'Conversion Rate'                => 'Tỷ lệ chuyển đổi',
            'Unpaid Earnings'                => 'Thu nhập chưa thanh toán',
            'Paid Earnings'                  => 'Thu nhập đã thanh toán',
            'Commission Rate'                => 'Tỷ lệ hoa hồng',
            'Campaign'                       => 'Chiến dịch',
            'Campaigns'                      => 'Chiến dịch',
            'Unique Links'                   => 'Liên kết duy nhất',
            'Converted'                      => 'Đã chuyển đổi',
            'None set'                       => 'Chưa thiết lập',
            'You have no referrals or visits that included a campaign name.' => 'Bạn chưa có hoa hồng hoặc lượt truy cập nào có tên chiến dịch.',

            'Reference'                      => 'Tham chiếu',
            'Amount'                         => 'Số tiền',
            'Description'                    => 'Mô tả',
            'Date'                           => 'Ngày',
            'You have not made any referrals yet.' => 'Bạn chưa có hoa hồng nào.',

            'Referral URL Visits'            => 'Lượt truy cập URL',
            'URL'                            => 'URL',
            'Referring URL'                  => 'URL giới thiệu',
            'Direct traffic'                 => 'Truy cập trực tiếp',
            'Visit converted: %s'            => 'Lượt truy cập đã chuyển đổi: %s',
            'You have not received any visits yet.' => 'Bạn chưa nhận được lượt truy cập nào.',
            'Column one lists the visit URL in relative format, column two lists the referrer, and column three indicates whether the visit converted into a referral.' => 'Cột một liệt kê URL truy cập, cột hai liệt kê URL giới thiệu, cột ba cho biết lượt truy cập đã chuyển đổi thành hoa hồng hay chưa.',

            'Sorry, there are currently no creatives available.' => 'Xin lỗi, hiện không có ấn phẩm nào.',
            'No creatives found.'            => 'Không tìm thấy ấn phẩm nào.',

            'Referral Graphs'                => 'Biểu đồ hoa hồng',

            'Referral Payouts'               => 'Thanh toán hoa hồng',
            'Payout Method'                  => 'Phương thức thanh toán',
            'Payout Account'                 => 'Tài khoản thanh toán',
            'Estimated Arrival Date'         => 'Ngày dự kiến nhận',
            'None of your referrals have been paid out yet.' => 'Chưa có khoản hoa hồng nào được thanh toán.',

            'Coupon Code'                    => 'Mã giảm giá',
            'There are currently no coupon codes to display.' => 'Hiện không có mã giảm giá nào để hiển thị.',

            'Profile Settings'               => 'Cài đặt hồ sơ',
            'Your Payment Email'             => 'Email thanh toán của bạn',
            'Notification Settings'          => 'Cài đặt thông báo',
            'Enable New Referral Notifications' => 'Bật thông báo hoa hồng mới',
            'Save Profile Settings'          => 'Lưu cài đặt hồ sơ',

            'Log into your account'          => 'Đăng nhập tài khoản',
            'Username'                       => 'Tên đăng nhập',
            'Password'                       => 'Mật khẩu',
            'Remember Me'                    => 'Ghi nhớ đăng nhập',
            'Log In'                         => 'Đăng nhập',
            'Log in'                         => 'Đăng nhập',
            'Lost your password?'            => 'Quên mật khẩu?',
            'Email Address'                  => 'Địa chỉ Email',

            'Register a new affiliate account' => 'Đăng ký tài khoản cộng tác viên mới',
            'Your Name'                      => 'Họ và tên',
            'Account Email'                  => 'Email tài khoản',
            'Payment Email'                  => 'Email thanh toán',
            'Payment Email Address'          => 'Địa chỉ email thanh toán',
            'Website URL'                    => 'URL trang web',
            'How will you promote us?'       => 'Bạn sẽ quảng bá chúng tôi như thế nào?',
            'Confirm Password'               => 'Xác nhận mật khẩu',
            'Agree to our Terms of Use and Privacy Policy' => 'Đồng ý với Điều khoản sử dụng và Chính sách bảo mật',
            'Register'                       => 'Đăng ký',

            'Copy and paste the following:'  => 'Sao chép và dán nội dung sau:',

            'To see the Affiliate Area, log in as an existing affiliate...' => 'Để xem Khu vực Cộng tác viên, vui lòng đăng nhập với tư cách cộng tác viên...',
            'The affiliate area is available only for registered affiliates.' => 'Khu vực cộng tác viên chỉ dành cho các cộng tác viên đã đăng ký.',
            'To see the Affiliate Area, log in as an existing affiliate, or add your account as an affiliate.' => 'Để xem Khu vực Cộng tác viên, vui lòng đăng nhập hoặc đăng ký tài khoản cộng tác viên.',

            'You have subscribed successfully.' => 'Bạn đã đăng ký thành công.',
            'First Name'                     => 'Tên',
            'Last Name'                      => 'Họ',
            'Subscribe'                      => 'Đăng ký',

            'Payout Settings'                => 'Cài đặt thanh toán',
            'Payout settings'                => 'Cài đặt thanh toán',
            'Account Type'                   => 'Loại tài khoản',
            'Personal Account'               => 'Tài khoản cá nhân',
            'Personal account'               => 'Tài khoản cá nhân',
            'Business Account'               => 'Tài khoản doanh nghiệp',
            'Business account'               => 'Tài khoản doanh nghiệp',
            'Your Country of Residence'      => 'Quốc gia cư trú',
            'Country of residence'           => 'Quốc gia cư trú',
            'Your Business Name'             => 'Tên doanh nghiệp',
            'Your business name'             => 'Tên doanh nghiệp',
            'I am the owner of the business legal entity' => 'Tôi là chủ sở hữu pháp lý của doanh nghiệp',
            'Your First Name'                => 'Tên của bạn',
            'First name'                     => 'Tên',
            'Your Last Name'                 => 'Họ của bạn',
            'Last name'                      => 'Họ',
            'Your Email'                     => 'Email của bạn',
            'Email address'                  => 'Địa chỉ email',
            'Date of Birth'                  => 'Ngày sinh',
            'Date of birth'                  => 'Ngày sinh',
            'Day'                            => 'Ngày',
            'Month'                          => 'Tháng',
            'Year'                           => 'Năm',
            'Register for Payouts Service'   => 'Đăng ký dịch vụ thanh toán',
            'Add Payout Method'              => 'Thêm phương thức thanh toán',
            'Add payout method'              => 'Thêm phương thức thanh toán',
            'Payouts Service'                => 'Dịch vụ Thanh toán',

            'Want to change your payout method? Do that <a href="%s">here</a>.' => 'Muốn thay đổi phương thức thanh toán? <a href="%s">nhấn vào đây</a>.',
            'Want to change your payout method? Do that <a href="%s">here</a>' => 'Muốn thay đổi phương thức thanh toán? <a href="%s">nhấn vào đây</a>',
            'Your earnings will be paid into the account below.' => 'Thu nhập của bạn sẽ được thanh toán vào tài khoản bên dưới.',
            'Your earnings will be paid into the card below.' => 'Thu nhập của bạn sẽ được thanh toán vào thẻ bên dưới.',
            'Your earnings will be paid into the following account.' => 'Thu nhập của bạn sẽ được thanh toán vào tài khoản sau.',
            'Your earnings will be paid to the following card.' => 'Thu nhập của bạn sẽ được thanh toán vào thẻ sau.',

            '<strong>Bank Name: </strong> %s' => '<strong>Tên ngân hàng: </strong> %s',
            '<strong>Account Holder Name: </strong> %s' => '<strong>Tên chủ tài khoản: </strong> %s',
            '<strong>Account Number: </strong> %s' => '<strong>Số tài khoản: </strong> %s',
            '<strong>Card: </strong> %s'     => '<strong>Thẻ: </strong> %s',
            '<strong>Expiry: </strong> %s'   => '<strong>Ngày hết hạn: </strong> %s',
            'Bank name'                      => 'Tên ngân hàng',
            'Account holder name'            => 'Tên chủ tài khoản',
            'Account number'                 => 'Số tài khoản',
            'Card'                           => 'Thẻ',
            'Expiry'                         => 'Ngày hết hạn',
            'Change payout method'           => 'Đổi phương thức thanh toán',

            'January'                        => 'Tháng 1',
            'February'                       => 'Tháng 2',
            'March'                          => 'Tháng 3',
            'April'                          => 'Tháng 4',
            'May'                            => 'Tháng 5',
            'June'                           => 'Tháng 6',
            'July'                           => 'Tháng 7',
            'August'                         => 'Tháng 8',
            'September'                      => 'Tháng 9',
            'October'                        => 'Tháng 10',
            'November'                       => 'Tháng 11',
            'December'                       => 'Tháng 12',

            'Search'                         => 'Tìm kiếm',
            'Filter'                         => 'Lọc',
            'Name'                           => 'Tên',
            'Status'                         => 'Trạng thái',
            'All'                            => 'Tất cả',

            'Already have a %1$s account? Connect it <a href="%2$s">here</a>' => 'Đã có tài khoản %1$s? <a href="%2$s">Kết nối tại đây</a>',
            'Already have a %1$s account? %2$s' => 'Đã có tài khoản %1$s? %2$s',
            'Connect it here'                => 'Kết nối tại đây',

            'An email has been sent to %s with a link to change the payout method' => 'Email đã được gửi đến %s với liên kết để đổi phương thức thanh toán',

            'Click <a href="%s">here</a> to add a payout method where you will receive your affiliate earnings.' => 'Nhấn <a href="%s">vào đây</a> để thêm phương thức thanh toán cho thu nhập của bạn.',
            'To receive your affiliate earnings, add a payout method below.' => 'Để nhận thu nhập, vui lòng thêm phương thức thanh toán bên dưới.',

            'Sign out'                       => 'Đăng xuất',
            'Back to site'                   => 'Quay lại trang',
            'Signed in as'                   => 'Đã đăng nhập với tư cách',
            'Give us your feedback'           => 'Gửi phản hồi',
        );
        if ( isset( $map[ $original ] ) ) {
            return $map[ $original ];
        }
        return $translated;
    }

    public function tn_affwp( $translated, $single, $plural, $number, $domain ) {
        if ( 'affiliate-wp' !== $domain && 'affiliatewp-affiliate-portal' !== $domain ) {
            return $translated;
        }
        $map = array(
            'Affiliate'                      => 'Cộng tác viên',
            'Submit your domain or individual domain path below for approval.' => 'Gửi tên miền hoặc đường dẫn của bạn bên dưới để phê duyệt.',
            'Submit your domain or individual domain paths below for approval.' => 'Gửi tên miền hoặc đường dẫn của bạn bên dưới để phê duyệt.',
            'Note, your domain must be HTTP (not HTTPS) based.' => 'Lưu ý, tên miền của bạn phải dựa trên HTTP (không phải HTTPS).',
            'Note, your domains must be HTTP (not HTTPS) based.' => 'Lưu ý, tên miền của bạn phải dựa trên HTTP (không phải HTTPS).',
        );
        $text = $number > 1 ? $plural : $single;
        if ( isset( $map[ $text ] ) ) {
            return $map[ $text ];
        }
        return $translated;
    }

    // ===== AFFILIATEWP-AFFILIATE-PORTAL TEXT DOMAIN =====

    public function t_portal( $translated, $original, $domain ) {
        if ( 'affiliatewp-affiliate-portal' !== $domain ) {
            return $translated;
        }
        $map = array(
            'Home'                           => 'Trang chủ',
            'Dashboard'                      => 'Bảng điều khiển',
            'Affiliate URLs'                 => 'URL Liên kết',
            'Statistics'                     => 'Thống kê',
            'Earnings'                       => 'Thu nhập',
            'Graphs'                         => 'Biểu đồ',
            'Referrals'                      => 'Hoa hồng',
            'Payouts'                        => 'Thanh toán',
            'Visits'                         => 'Lượt truy cập',
            'Creatives'                      => 'Ấn phẩm',
            'Coupons'                        => 'Mã giảm giá',
            'Settings'                       => 'Cài đặt',

            'Last 30 days'                   => '30 ngày qua',
            'All-time'                       => 'Tổng quan',
            'Conversion Rate'                => 'Tỷ lệ chuyển đổi',
            'Conversion rate'                => 'Tỷ lệ chuyển đổi',
            'Unpaid Referrals'               => 'Hoa hồng chưa thanh toán',
            'Unpaid referrals'               => 'Hoa hồng chưa thanh toán',
            'Paid Referrals'                 => 'Hoa hồng đã thanh toán',
            'Paid referrals'                 => 'Hoa hồng đã thanh toán',
            'Unpaid Earnings'                => 'Thu nhập chưa thanh toán',
            'Unpaid earnings'                => 'Thu nhập chưa thanh toán',
            'Total Earnings'                 => 'Tổng thu nhập',
            'Paid earnings'                  => 'Thu nhập đã thanh toán',
            'Commission rate'                => 'Tỷ lệ hoa hồng',
            'Recent referral activity'       => 'Hoạt động hoa hồng gần đây',

            'Campaign'                       => 'Chiến dịch',
            'Campaigns'                      => 'Chiến dịch',
            'Unique Links'                   => 'Liên kết duy nhất',
            'Converted'                      => 'Đã chuyển đổi',

            'URL'                            => 'URL',
            'Referring URL'                  => 'URL giới thiệu',
            'Direct Traffic'                 => 'Truy cập trực tiếp',
            'Date'                           => 'Ngày',
            'Visit %s'                       => 'Lượt truy cập %s',

            'User settings'                  => 'Cài đặt người dùng',
            'Save user settings'             => 'Lưu cài đặt',
            'Payments'                       => 'Thanh toán',
            'Payment email'                  => 'Email thanh toán',
            'This is your payment email. Used by PayPal and the likes' => 'Đây là email thanh toán của bạn. Được sử dụng bởi PayPal và các hệ thống tương tự',
            'Invalid email address.'         => 'Địa chỉ email không hợp lệ.',
            'Notifications'                  => 'Thông báo',
            'Enable referral notifications'  => 'Bật thông báo hoa hồng',
            'Receive a notification when a referral is generated.' => 'Nhận thông báo khi có hoa hồng mới được tạo.',

            'Coupon Code'                    => 'Mã giảm giá',
            'Amount'                         => 'Số tiền',
            'Enter a new coupon code'        => 'Nhập mã giảm giá mới',

            'All Categories'                 => 'Tất cả danh mục',

            'Submit your feedback'            => 'Gửi phản hồi',
            'Give us your feedback'           => 'Gửi phản hồi cho chúng tôi',

            'Affiliate Portal'               => 'Cổng thông tin Cộng tác viên',
            'Enable the Affiliate Portal'    => 'Bật Cổng thông tin Cộng tác viên',
            'Check this box to enable the Affiliate Portal.' => 'Đánh dấu để bật Cổng thông tin Cộng tác viên.',
            'Affiliate Feedback'             => 'Phản hồi',
            'Allow affiliate feedback.'      => 'Cho phép cộng tác viên gửi phản hồi.',
            'Referral Link Sharing'          => 'Chia sẻ liên kết',
            'Sharing Options'                => 'Tùy chọn chia sẻ',
            'More add-ons'                   => 'Thêm tiện ích',
            'Get more add-ons for AffiliateWP' => 'Tải thêm tiện ích cho AffiliateWP',

            'X'                              => 'X (Twitter)',
            'Facebook'                       => 'Facebook',
            'Email'                          => 'Email',
            'X Text'                         => 'Nội dung X',
            'Email Sharing Subject'          => 'Tiêu đề email',
            'Email Sharing Message'          => 'Nội dung email',
            'I thought you might be interested in this:' => 'Tôi nghĩ bạn có thể quan tâm đến:',

            'Menu Links'                     => 'Liên kết Menu',
            'Per Page Settings'              => 'Cài đặt Số lượng/Trang',
            'Creatives Per Page'             => 'Số ấn phẩm mỗi trang',
            'The number of creatives to display.' => 'Số lượng ấn phẩm hiển thị.',
            'Items Per Page'                 => 'Số mục mỗi trang',
            'The number of items to show per page in most tables.' => 'Số lượng mục hiển thị trên mỗi trang trong hầu hết các bảng.',
            'Expand all menu links'          => 'Mở rộng tất cả',
            'Collapse all menu links'        => 'Thu gọn tất cả',
            'Add New Link'                   => 'Thêm liên kết mới',
            'Click and drag to re-order'     => 'Kéo và thả để sắp xếp',
            'Link Title'                     => 'Tiêu đề liên kết',
            'Enter a label for the link.'    => 'Nhập nhãn cho liên kết.',
            'Link Content'                   => 'Nội dung liên kết',
            'Select which page will be used for the link\'s content. This page will be blocked for non-affiliates.' => 'Chọn trang sẽ được sử dụng cho nội dung liên kết. Trang này sẽ bị chặn đối với người không phải cộng tác viên.',
            'Delete link'                    => 'Xóa liên kết',
            'Save settings'                  => 'Lưu cài đặt',
            'Saved'                          => 'Đã lưu',

            'New Custom Link'                => 'Liên kết tùy chỉnh mới',
            'Are you sure you want to delete this link?' => 'Bạn có chắc chắn muốn xóa liên kết này?',

            'Payout Account'                 => 'Tài khoản thanh toán',
            'Estimated Arrival Date'         => 'Ngày dự kiến nhận',

            'Welcome %s'                     => 'Chào mừng %s',
            'Share your referral URL with your audience to earn commission.' => 'Chia sẻ URL giới thiệu của bạn với khán giả để kiếm hoa hồng.',
            'Referral URL generator'         => 'Trình tạo URL giới thiệu',
            'Use this form to generate a referral link.' => 'Sử dụng biểu mẫu này để tạo liên kết giới thiệu.',
            'Page URL'                       => 'URL trang',
            'Campaign name'                  => 'Tên chiến dịch',
            'Enter an optional campaign name to help track performance.' => 'Nhập tên chiến dịch (không bắt buộc) để theo dõi hiệu suất.',
            'Generated referral URL'         => 'URL giới thiệu đã tạo',
            'Share this URL with your audience.' => 'Chia sẻ URL này với khán giả của bạn.',
            'Share this URL'                 => 'Chia sẻ URL này',

            'View all'                       => 'Xem tất cả',
            'Increased by'                   => 'Tăng',
            'Decreased by'                   => 'Giảm',
            'Filter'                         => 'Lọc',
            'Copy to clipboard'              => 'Sao chép vào clipboard',
            'View'                           => 'Xem',
            'Copy'                           => 'Sao chép',
            'Preview'                        => 'Xem trước',
            'Code'                           => 'Mã',
            'Request'                        => 'Yêu cầu',
            'Awaiting review: %s'            => 'Đang chờ xem xét: %s',

            'Unpaid Referral Earnings'       => 'Thu nhập hoa hồng chưa thanh toán',
            'Pending Referral Earnings'      => 'Thu nhập hoa hồng đang chờ',
            'Rejected Referral Earnings'     => 'Thu nhập hoa hồng bị từ chối',
            'Paid Referral Earnings'         => 'Thu nhập hoa hồng đã thanh toán',

            'Reference'                      => 'Tham chiếu',
            'Description'                    => 'Mô tả',

            'Today'                          => 'Hôm nay',
            'Yesterday'                      => 'Hôm qua',
            'This Week'                      => 'Tuần này',
            'Last Week'                      => 'Tuần trước',
            'This Month'                     => 'Tháng này',
            'Last Month'                     => 'Tháng trước',
            'This Quarter'                   => 'Quý này',
            'Last Quarter'                   => 'Quý trước',
            'This Year'                      => 'Năm nay',
            'Last Year'                      => 'Năm trước',

            'Copied!'                        => 'Đã sao chép!',
            'Could not copy to clipboard'     => 'Không thể sao chép vào clipboard',
            'Loading...'                     => 'Đang tải...',
            'Copy link'                      => 'Sao chép liên kết',
            'Invalid URL'                    => 'URL không hợp lệ',

            'None set'                       => 'Chưa thiết lập',

            'Custom affiliate slug'          => 'Slug cộng tác viên tùy chỉnh',
            'A custom affiliate slug allows you to have a unique referral URL using your chosen slug.' => 'Slug cộng tác viên tùy chỉnh cho phép bạn có URL giới thiệu riêng.',
            'Save custom slug'               => 'Lưu slug',
            'Your custom slug'               => 'Slug của bạn',
            'Custom slug'                    => 'Slug tùy chỉnh',
            'Slugs can only contain lowercase letters and numbers.' => 'Slug chỉ có thể chứa chữ thường và số.',
            'Slugs cannot only contain numbers.' => 'Slug không thể chỉ chứa số.',
            'Slugs cannot be longer than 60 characters.' => 'Slug không được dài quá 60 ký tự.',
            'That slug cannot be used.'      => 'Slug đó không thể sử dụng.',
            'Slugs do not match.'            => 'Slug không khớp.',
            'Confirm affiliate slug'         => 'Xác nhận slug cộng tác viên',
            'By changing your affiliate slug you acknowledge that any existing links using an older affiliate slug may no longer work. Type your new custom slug one more time to confirm.' => 'Khi thay đổi slug, các liên kết cũ sử dụng slug cũ có thể không hoạt động. Gõ lại slug mới để xác nhận.',
            'You must confirm removal.'      => 'Bạn phải xác nhận xóa.',
            'Confirm Removal'                => 'Xác nhận xóa',
            'By removing your affiliate slug you acknowledge that any existing links using an older affiliate slug may no longer work.' => 'Khi xóa slug, các liên kết cũ sử dụng slug cũ có thể không hoạt động.',

            'Dismiss this notice'            => 'Bỏ qua thông báo này',
            'Direct link domains'            => 'Tên miền liên kết trực tiếp',
            'Direct links allow you to link directly to this site, from your own website, without an affiliate link.' => 'Liên kết trực tiếp cho phép bạn dẫn link trực tiếp đến trang này mà không cần link affiliate.',
            'Save direct links'              => 'Lưu liên kết trực tiếp',
            'Submit your domain or individual domain path below for approval.' => 'Gửi tên miền hoặc đường dẫn của bạn bên dưới để phê duyệt.',
            'Your direct link(s) have been updated' => 'Liên kết trực tiếp của bạn đã được cập nhật',
            'An invalid domain was submitted.' => 'Tên miền không hợp lệ.',
            'You have domains that were not approved:' => 'Bạn có tên miền chưa được phê duyệt:',
            'Direct link domain'             => 'Tên miền liên kết trực tiếp',
            'Validating domain, please wait...' => 'Đang xác thực tên miền, vui lòng đợi...',
            'Domains cannot be empty.'       => 'Tên miền không được để trống.',
            'This domain is duplicated.'     => 'Tên miền này bị trùng lặp.',
            'Domain was entered incorrectly.' => 'Tên miền được nhập không chính xác.',
            'Add new domain'                 => 'Thêm tên miền mới',
            'Direct Links'                   => 'Liên kết trực tiếp',

            'Landing pages'                  => 'Trang đích',
            'Landing pages allow you to share the page URL without needing to use your affiliate link.' => 'Trang đích cho phép bạn chia sẻ URL trang mà không cần sử dụng link affiliate.',
            'Your landing pages:'            => 'Trang đích của bạn:',

            'Not provided'                   => 'Không cung cấp',
            'Lifetime Customers'             => 'Khách hàng trọn đời',
            'Lifetime customers'             => 'Khách hàng trọn đời',
            'Lifetime Referral'              => 'Hoa hồng trọn đời',

            'Network'                        => 'Mạng lưới',
            'Your Network Link'              => 'Liên kết mạng lưới của bạn',
            'Invite other affiliates to your network using this link.' => 'Mời cộng tác viên khác vào mạng lưới của bạn bằng liên kết này.',
            'Your Network'                   => 'Mạng lưới của bạn',

            'Order Details'                  => 'Chi tiết đơn hàng',
            'Order Total'                    => 'Tổng đơn hàng',
            'Order Number'                   => 'Mã đơn hàng',
            'Order Date'                     => 'Ngày đặt hàng',
            'Customer Name'                  => 'Tên khách hàng',
            'Customer Email'                 => 'Email khách hàng',
            'Customer Phone'                 => 'Số điện thoại',
            'Customer Shipping Address'      => 'Địa chỉ giao hàng',
            'Customer Billing Address'       => 'Địa chỉ thanh toán',
            'Referral Amount'                => 'Số tiền hoa hồng',
            'Return to all orders'           => 'Quay lại tất cả đơn hàng',
            'Enable Details in the Affiliate Portal Table' => 'Bật Chi tiết trong bảng Cổng thông tin',
            'Orders per page'                => 'Số đơn hàng mỗi trang',
            'The number of orders to display in the Order Details table beginning with the most recent first.' => 'Số lượng đơn hàng hiển thị trong bảng Chi tiết Đơn hàng.',

            'Pushover user key'              => 'Mã người dùng Pushover',
            'Receive referral notifications via Pushover' => 'Nhận thông báo hoa hồng qua Pushover',

            'Enable payout via store credit' => 'Bật thanh toán qua tín dụng cửa hàng',
            'Receive your payouts in store credit.' => 'Nhận thanh toán của bạn dưới dạng tín dụng cửa hàng.',
            'Available store credit'         => 'Tín dụng cửa hàng khả dụng',

            'What\'s your referral link?'    => 'Liên kết giới thiệu của bạn là gì?',
            'Share your referral link'       => 'Chia sẻ liên kết giới thiệu của bạn',

            'Upload or choose a logo to be displayed at the top of Affiliate Portal.' => 'Tải lên hoặc chọn logo hiển thị ở đầu Cổng thông tin Cộng tác viên.',
            'The default text that will show when an affiliate shares to X. Leave blank to use Site Title.' => 'Văn bản mặc định khi cộng tác viên chia sẻ lên X. Để trống để sử dụng Tiêu đề trang.',
            'The default text that will show in the email subject line. Leave blank to use Site Title.' => 'Văn bản mặc định trong dòng tiêu đề email. Để trống để sử dụng Tiêu đề trang.',
            'The default text that will show in the email message. The affiliate\'s referral URL will be automatically appended to the email.' => 'Văn bản mặc định trong nội dung email. URL giới thiệu sẽ được tự động thêm vào cuối email.',
        );
        if ( isset( $map[ $original ] ) ) {
            return $map[ $original ];
        }
        return $translated;
    }

    public function tc_affwp( $translated, $original, $context, $domain ) {
        if ( 'affiliate-wp' !== $domain ) {
            return $translated;
        }
        if ( 'None set' === $original && 'campaign' === $context ) {
            return 'Chưa thiết lập';
        }
        if ( 'None set' === $original && 'visit URL' === $context ) {
            return 'Chưa có URL';
        }
        return $translated;
    }

    public function tc_portal( $translated, $original, $context, $domain ) {
        if ( 'affiliatewp-affiliate-portal' !== $domain ) {
            return $translated;
        }
        $map = array(
            'None set' . "\0" . 'campaign'             => 'Chưa thiết lập',
            'None set' . "\0" . 'visit URL'            => 'Chưa có URL',
            'Copy' . "\0" . 'creative code'            => 'Sao chép',
            'Preview' . "\0" . 'creative modal'        => 'Xem trước',
            'Increased by' . "\0" . 'statistical comparison' => 'Tăng',
            'Decreased by' . "\0" . 'statistical comparison' => 'Giảm',
            'Request' . "\0" . 'Request vanity coupon code' => 'Yêu cầu',
            'Not provided' . "\0" . 'customer first name' => 'Không cung cấp',
            'Not provided' . "\0" . 'customer last name'  => 'Không cung cấp',
        );
        $key = $original . "\0" . $context;
        if ( isset( $map[ $key ] ) ) {
            return $map[ $key ];
        }
        return $translated;
    }
}

function affiliatewp_portal_translator_plus_init() {
    return AffiliateWP_Portal_Translator_Plus::get_instance();
}
add_action( 'plugins_loaded', 'affiliatewp_portal_translator_plus_init', -999 );


