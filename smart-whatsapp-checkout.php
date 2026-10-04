<?php
/**
 * Plugin Name: Smart WhatsApp Checkout & Recovery Engine
 * Plugin URI: https://example.com/
 * Description: ووکامرس کے لیے جدید ون کلک واٹس ایپ آرڈر اور کارٹ ریکوری سسٹم مع خودکار آرڈر تخلیق۔
 * Version: 1.0.0
 * Author: Muhammad Usama
 * Text Domain: smart-whatsapp-checkout
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SWC_PLUGIN_VERSION', '1.0.0');
define('SWC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SWC_PLUGIN_URL', plugin_dir_url(__FILE__));

// ووکامرس کی موجودگی چیک کرنے والی کلاس
final class Smart_WhatsApp_Checkout {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('plugins_loaded', array($this, 'init_plugin'));
    }

    public function init_plugin() {
        // اگر ووکامرس انسٹال یا فعال نہ ہو تو پلگ ان کو روکیں اور ایڈمن وارننگ دکھائیں
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }

        $this->includes();
    }

    public function woocommerce_missing_notice() {
        echo '<div class="error"><p><strong>Smart WhatsApp Checkout:</strong> اس پلگ ان کے کام کرنے کے لیے WooCommerce کا فعال ہونا لازمی ہے۔</p></div>';
    }

    private function includes() {
        require_once SWC_PLUGIN_DIR . 'includes/class-order-handler.php';
        require_once SWC_PLUGIN_DIR . 'includes/class-admin.php';
        require_once SWC_PLUGIN_DIR . 'includes/class-public.php';

        if (is_admin()) {
            new SWC_Admin();
        }
        new SWC_Public();
        new SWC_Order_Handler();
    }
}

// پلگ ان شروع کریں
function run_smart_whatsapp_checkout() {
    return Smart_WhatsApp_Checkout::instance();
}
run_smart_whatsapp_checkout();