(function( $ ) {
	'use strict';

	$(document).ready(function() {
        
        // Cart Page Button
        $(document).on('click', '#whatsapp-cart-button', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            $btn.addClass('loading').text('Processing...');

            $.ajax({
                url: whatsapp_cart_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'whatsapp_cart_submit',
                    security: whatsapp_cart_obj.nonce,
                    is_cart: true
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.redirect_url;
                    } else {
                        alert('Error: ' + response.data.message);
                        $btn.removeClass('loading').text('Confirm on WhatsApp');
                    }
                },
                error: function(err) {
                    console.log(err);
                    alert('Something went wrong.');
                    $btn.removeClass('loading').text('Confirm on WhatsApp');
                }
            });
        });

        // Checkout Page Button
        $(document).on('click', '#whatsapp-checkout-button', function(e) {
            e.preventDefault();

            var $btn = $(this);
            // Basic validation
            var $form = $('form.checkout');
            if ( $form.length === 0 ) return;

            // Simple validation for required fields
            var required = ['billing_first_name', 'billing_last_name', 'billing_phone', 'billing_address_1', 'billing_city', 'billing_email'];
            var hasError = false;

            $.each(required, function(index, value) {
                var $field = $('#' + value);
                if ($field.length > 0 && $field.val() === '') {
                    $field.closest('.form-row').addClass('woocommerce-invalid woocommerce-invalid-required-field');
                    hasError = true;
                } else {
                    $field.closest('.form-row').removeClass('woocommerce-invalid woocommerce-invalid-required-field');
                }
            });

            if (hasError) {
                alert('Please fill in all required billing details.');
                $('html, body').animate({
                    scrollTop: $form.offset().top
                }, 500);
                return;
            }

            // Optional: Trigger WooCommerce validation if possible, but might be hard to intercept fully without submitting.
            // We'll just grab the data.
            
            $btn.addClass('loading').text('Processing...');

            var formData = $form.serialize();

            $.ajax({
                url: whatsapp_cart_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'whatsapp_cart_submit',
                    security: whatsapp_cart_obj.nonce,
                    is_checkout: true,
                    form_data: formData
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.redirect_url;
                    } else {
                        alert('Error: ' + response.data.message);
                        $btn.removeClass('loading').text('Confirm Order on WhatsApp');
                    }
                },
                error: function(err) {
                    console.log(err);
                    alert('Something went wrong.');
                    $btn.removeClass('loading').text('Confirm Order on WhatsApp');
                }
            });
        });

        // Product Page Button
        $(document).on('click', '#whatsapp-product-button', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var product_id = $btn.data('product_id');
            var $form = $('form.cart');
            var quantity = $form.find('input[name="quantity"]').val() || 1;
            
            // Check if variable product and variation selected
            var variation_id = $form.find('input[name="variation_id"]').val() || 0;
            if ( $form.find('.variations').length > 0 && variation_id == 0 ) {
                alert('Please select product options.');
                return;
            }

            $btn.addClass('loading').text('Processing...');

            $.ajax({
                url: whatsapp_cart_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'whatsapp_cart_submit',
                    security: whatsapp_cart_obj.nonce,
                    is_product: true,
                    product_id: product_id,
                    quantity: quantity,
                    variation_id: variation_id
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.redirect_url;
                    } else {
                        alert('Error: ' + response.data.message);
                        $btn.removeClass('loading').text('Order via WhatsApp');
                    }
                },
                error: function(err) {
                    console.log(err);
                    alert('Something went wrong.');
                    $btn.removeClass('loading').text('Order via WhatsApp');
                }
            });
        });

	});

})( jQuery );
