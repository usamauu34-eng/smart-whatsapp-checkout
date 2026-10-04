<?php
if (!defined('ABSPATH')) {
    exit;
}

class SWC_Order_Handler {

    public function __construct() {
        // AJAX کے ذریعے فوری واٹس ایپ آرڈر بنانا
        add_action('wp_ajax_swc_create_whatsapp_order', array($this, 'create_whatsapp_order'));
        add_action('wp_ajax_nopriv_swc_create_whatsapp_order', array($this, 'create_whatsapp_order'));
    }

    public function create_whatsapp_order() {
        check_ajax_referer('swc_public_nonce', 'security');

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity   = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        $is_cart    = isset($_POST['is_cart']) && $_POST['is_cart'] === 'yes';

        try {
            // نیا ووکامرس آرڈر بنائیں
            $order = wc_create_order();

            if ($is_cart) {
                // اگر کارٹ سے آرڈر ہو رہا ہے
                foreach (WC()->cart->get_cart() as $cart_item) {
                    $order->add_product($cart_item['data'], $cart_item['quantity']);
                }
            } else {
                // اگر سنگل پروڈکٹ پیج سے آرڈر ہو رہا ہے
                $product = wc_get_product($product_id);
                if (!$product) {
                    wp_send_json_error('پروڈکٹ نہیں مل سکی۔');
                }
                $order->add_product($product, $quantity);
            }

            // آرڈر کی بنیادی تفصیلات سیٹ کریں
            $order->set_payment_method('whatsapp');
            $order->set_payment_method_title('WhatsApp Order');
            $order->set_status('pending', 'واٹس ایپ کے ذریعے موصول شدہ نیا آرڈر۔');
            $order->calculate_totals();
            $order->save();

            $order_id    = $order->get_id();
            $order_total = html_entity_decode(strip_tags(wc_price($order->get_total())));

            // آرڈر کے تمام آئٹمز کی فہرست
            $items_summary = '';
            foreach ($order->get_items() as $item) {
                $items_summary .= "• " . $item->get_name() . " (x" . $item->get_quantity() . ") - " . html_entity_decode(strip_tags(wc_price($item->get_total()))) . "\n";
            }

            // میسج ٹیمپلیٹ لے کر ٹیگز بدلیں
            $template = get_option('swc_message_template', "السلام علیکم! میں آرڈر کرنا چاہتا ہوں:\n\n*آرڈر نمبر:* {order_id}\n*سامان:*\n{order_items}\n*کل رقم:* {order_total}\n\nبراہ کرم کنفرم کریں۔");
            $phone    = get_option('swc_phone_number', '');

            $final_msg = str_replace(
                array('{order_id}', '{order_items}', '{order_total}'),
                array('#' . $order_id, trim($items_summary), $order_total),
                $template
            );

            $whatsapp_url = 'https://api.whatsapp.com/send?phone=' . esc_attr($phone) . '&text=' . rawurlencode($final_msg);

            wp_send_json_success(array(
                'whatsapp_url' => $whatsapp_url,
                'order_id'     => $order_id
            ));

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
}