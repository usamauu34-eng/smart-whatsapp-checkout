<?php
if (!defined('ABSPATH')) {
    exit;
}

class SWC_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Smart WhatsApp Settings',
            'WhatsApp Checkout',
            'manage_woocommerce',
            'swc-settings',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting('swc_settings_group', 'swc_phone_number', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ));

        register_setting('swc_settings_group', 'swc_button_text', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'واٹس ایپ پر آرڈر کریں'
        ));

        register_setting('swc_settings_group', 'swc_message_template', array(
            'sanitize_callback' => 'sanitize_textarea_field',
            'default'           => "السلام علیکم! میں آرڈر کرنا چاہتا ہوں:\n\n*آرڈر نمبر:* {order_id}\n*سامان:*\n{order_items}\n*کل رقم:* {order_total}\n\nبراہ کرم کنفرم کریں۔"
        ));
    }

    public function render_settings_page() {
        // واٹس ایپ کے تمام آرڈرز ووکامرس سے نکالیں
        $orders = wc_get_orders(array(
            'payment_method' => 'whatsapp',
            'limit'          => 20,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));

        $total_whatsapp_revenue = 0;
        foreach ($orders as $o) {
            $total_whatsapp_revenue += floatval($o->get_total());
        }
        $currency_symbol = get_woocommerce_currency_symbol();
        ?>
        <div class="wrap" style="max-width: 950px; margin-top: 20px;">
            <h1 style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                <span class="dashicons dashicons-whatsapp" style="color: #25D366; font-size: 32px; width: 32px; height: 32px;"></span>
                Smart WhatsApp Checkout & Recovery Engine
            </h1>

            <!-- اینالیٹکس کاؤنٹرز کارڈز -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 25px;">
                <div style="background: #fff; padding: 20px; border-radius: 8px; border-left: 4px solid #25D366; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                    <div style="color: #64748b; font-size: 13px; font-weight: bold;">کل واٹس ایپ آرڈرز</div>
                    <div style="font-size: 28px; font-weight: bold; color: #1e293b; margin-top: 5px;"><?php echo count($orders); ?></div>
                </div>

                <div style="background: #fff; padding: 20px; border-radius: 8px; border-left: 4px solid #0284c7; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                    <div style="color: #64748b; font-size: 13px; font-weight: bold;">تخمینہ شدہ ریکوری آمدن</div>
                    <div style="font-size: 28px; font-weight: bold; color: #1e293b; margin-top: 5px;"><?php echo esc_html($currency_symbol . number_format($total_whatsapp_revenue, 2)); ?></div>
                </div>

                <div style="background: #fff; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                    <div style="color: #64748b; font-size: 13px; font-weight: bold;">سسٹم اسٹیٹس</div>
                    <div style="font-size: 18px; font-weight: bold; color: #16a34a; margin-top: 10px;">🟢 ایکٹیو اور منسلک</div>
                </div>
            </div>

            <!-- سیٹنگز فارم -->
            <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 30px;">
                <h2 style="margin-top: 0;">⚙️ جنرل کنفیگریشن</h2>
                <hr style="margin: 15px 0;">

                <form method="POST" action="options.php">
                    <?php
                    settings_fields('swc_settings_group');
                    do_settings_sections('swc_settings_group');

                    $phone    = get_option('swc_phone_number', '');
                    $btn_text = get_option('swc_button_text', 'واٹس ایپ پر آرڈر کریں');
                    $template = get_option('swc_message_template', "السلام علیکم! میں آرڈر کرنا چاہتا ہوں:\n\n*آرڈر نمبر:* {order_id}\n*سامان:*\n{order_items}\n*کل رقم:* {order_total}\n\nبراہ کرم کنفرم کریں۔");
                    ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="swc_phone_number">واٹس ایپ بزنس نمبر:</label></th>
                            <td>
                                <input type="text" id="swc_phone_number" name="swc_phone_number" value="<?php echo esc_attr($phone); ?>" class="regular-text" placeholder="923001234567" style="direction: ltr;">
                                <p class="description">ملکی کوڈ کے ساتھ نمبر درج کریں (بغیر + یا اسپیس کے، مثلاً: 923001234567)۔</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="swc_button_text">بٹن کا لیبل (Text):</label></th>
                            <td>
                                <input type="text" id="swc_button_text" name="swc_button_text" value="<?php echo esc_attr($btn_text); ?>" class="regular-text">
                                <p class="description">یہ بٹن پروڈکٹ پیج اور کارٹ پر نظر آئے گا۔</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="swc_message_template">خودکار میسج ٹیمپلیٹ:</label></th>
                            <td>
                                <textarea id="swc_message_template" name="swc_message_template" rows="6" class="large-text" style="line-height: 1.6;"><?php echo esc_textarea($template); ?></textarea>
                                <p class="description">
                                    <strong>دستیاب ٹیگز:</strong><br>
                                    <code>{order_id}</code> = آرڈر کا منفرد نمبر<br>
                                    <code>{order_items}</code> = مصنوعات کے نام اور مقدار<br>
                                    <code>{order_total}</code> = کل رقم بشمول کرنسی
                                </p>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button('سیٹنگز محفوظ کریں'); ?>
                </form>
            </div>

            <!-- حالیہ واٹس ایپ آرڈرز لاگ -->
            <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <h2 style="margin-top: 0;">📋 حالیہ واٹس ایپ لیڈز اور آرڈرز</h2>
                <hr style="margin: 15px 0;">

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>آرڈر ID</th>
                            <th>تاریخ</th>
                            <th>اسٹیٹس</th>
                            <th>کل رقم</th>
                            <th>تفصیل دیکھیں</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)) : ?>
                            <tr><td colspan="5">ابھی تک واٹس ایپ کے ذریعے کوئی آرڈر موصول نہیں ہوا۔</td></tr>
                        <?php else : ?>
                            <?php foreach ($orders as $order) : ?>
                                <tr>
                                    <td><strong>#<?php echo esc_html($order->get_id()); ?></strong></td>
                                    <td><?php echo esc_html($order->get_date_created()->date_i18n('Y-m-d H:i')); ?></td>
                                    <td><span style="background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 4px; font-weight: bold;"><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></span></td>
                                    <td><strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong></td>
                                    <td><a href="<?php echo esc_url($order->get_edit_order_url()); ?>" class="button button-small">ووکامرس میں دیکھیں</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
}