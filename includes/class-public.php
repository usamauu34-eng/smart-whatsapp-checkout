<?php
if (!defined('ABSPATH')) {
    exit;
}

class SWC_Public {

    public function __construct() {
        add_action('woocommerce_after_add_to_cart_button', array($this, 'render_product_whatsapp_button'));
        add_action('woocommerce_proceed_to_checkout', array($this, 'render_cart_whatsapp_button'), 20);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('wp_footer', array($this, 'render_floating_whatsapp_button'));
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'swc-public-style',
            SWC_PLUGIN_URL . 'assets/css/public.css',
            array(),
            SWC_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'swc-checkout-script',
            SWC_PLUGIN_URL . 'assets/js/checkout.js',
            array('jquery'),
            SWC_PLUGIN_VERSION,
            true
        );

        wp_localize_script('swc-checkout-script', 'swc_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('swc_public_nonce')
        ));
    }

    /**
     * واٹس ایپ کا باضابطہ ویکٹر SVG آئیکن
     */
    private function get_whatsapp_svg($size = 20) {
        return '<svg width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" viewBox="0 0 24 24" fill="currentColor" style="display: inline-block; vertical-align: middle;">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>';
    }

    public function render_product_whatsapp_button() {
        global $product;
        if (!$product) return;

        $phone = get_option('swc_phone_number', '');
        if (empty($phone)) return;

        $btn_text = get_option('swc_button_text', 'واٹس ایپ پر آرڈر کریں');

        echo '<div class="swc-btn-wrapper" style="margin-top: 12px;">';
        echo '<button type="button" class="swc-order-trigger button" data-product-id="' . esc_attr($product->get_id()) . '" data-is-cart="no" style="background: #25D366; color: #fff; border: none; padding: 12px 22px; border-radius: 6px; font-weight: bold; font-size: 15px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">';
        echo $this->get_whatsapp_svg(20);
        echo '<span>' . esc_html($btn_text) . '</span>';
        echo '</button>';
        echo '</div>';
    }

    public function render_cart_whatsapp_button() {
        $phone = get_option('swc_phone_number', '');
        if (empty($phone) || WC()->cart->is_empty()) return;

        $btn_text = get_option('swc_button_text', 'واٹس ایپ پر آرڈر کریں');

        echo '<div style="margin-top: 15px;">';
        echo '<button type="button" class="swc-order-trigger button" data-is-cart="yes" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; background: #128C7E; color: #fff; border: none; padding: 14px 20px; border-radius: 6px; font-weight: bold; font-size: 16px; cursor: pointer;">';
        echo $this->get_whatsapp_svg(22);
        echo '<span>' . esc_html($btn_text) . ' (پوری کارٹ)</span>';
        echo '</button>';
        echo '</div>';
    }

    public function render_floating_whatsapp_button() {
        $phone = get_option('swc_phone_number', '');
        if (empty($phone)) return;

        $default_msg = rawurlencode("السلام علیکم! مجھے مزید معلومات چاہیے ہیں۔");
        $wa_link     = "https://api.whatsapp.com/send?phone=" . esc_attr($phone) . "&text=" . $default_msg;

        echo '<a href="' . esc_url($wa_link) . '" target="_blank" class="swc-floating-btn" title="ہم سے رابطہ کریں">';
        echo $this->get_whatsapp_svg(22);
        echo '<span>واٹس ایپ چیٹ</span>';
        echo '</a>';
    }
}