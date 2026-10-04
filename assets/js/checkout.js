jQuery(document).ready(function($) {
    $(document).on('click', '.swc-order-trigger', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var originalText = $btn.html();
        var productId = $btn.data('product-id') || 0;
        var isCart = $btn.data('is-cart') || 'no';
        var qty = $('input[name="quantity"]').val() || 1;

        $btn.html('⏳ آرڈر بن رہا ہے...').prop('disabled', true);

        $.ajax({
            url: swc_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'swc_create_whatsapp_order',
                security: swc_ajax_obj.nonce,
                product_id: productId,
                quantity: qty,
                is_cart: isCart
            },
            success: function(response) {
                if (response.success && response.data.whatsapp_url) {
                    window.open(response.data.whatsapp_url, '_blank');
                } else {
                    alert('خرابی: ' + (response.data || 'آرڈر نہیں بن سکا۔'));
                }
            },
            error: function() {
                alert('سرور سے رابطہ نہ ہو سکا۔');
            },
            complete: function() {
                $btn.html(originalText).prop('disabled', false);
            }
        });
    });
});