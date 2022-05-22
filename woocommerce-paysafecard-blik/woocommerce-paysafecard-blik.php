<?php
/*
 * Plugin Name: WooCommerce Manual Paysafecard And BLIK Payments
 * Plugin URI: https://juliankorgol.com/
 * Description: Paysafecard and BLIK Manual WooCommerce Payments
 * Author: Julian Korgol
 * Author URI: https://juliankorgol.com/
 * Version: 1.1.0
 */



/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'paysafecard_blik_jk_add_gateway_class' );
function paysafecard_blik_jk_add_gateway_class( $gateways ) {
    $gateways[] = 'WC_paysafecard_blik_jk_Gateway'; // your class name is here
    return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'paysafecard_blik_jk_init_gateway_class' );
function paysafecard_blik_jk_init_gateway_class() {

    class WC_paysafecard_blik_jk_Gateway extends WC_Payment_Gateway {

        /**
         * Class constructor, more about it in Step 3
         */
        public function __construct() {


            $this->id = 'paysafecard_blik'; // payment gateway plugin ID
            $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = 'PaySafeCard lub BLIK';
            $this->method_description = 'Manualna płatność po kontakcie z administratorem poprzez PaySafeCard lub BLIK.'; // will be displayed on the options page

            // gateways can support subscriptions, refunds, saved payment methods,
            // but in this tutorial we begin with simple payments
            $this->supports = array(
                'products'
            );

            // Method with all the options fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );
            $this->enabled = $this->get_option( 'enabled' );

            // This action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

            // We need custom JavaScript to obtain a token
            add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

            // You can also register a webhook here
            // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );

        }

        /**
         * Plugin options, we deal with it in Step 3 too
         */
        public function init_form_fields(){


            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable WooCommerce Manual Paysafecard And BLIK Payments',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => 'Title',
                    'type'        => 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default'     => 'PaySafeCard lub BLIK',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'Manualna płatność po kontakcie z administratorem poprzez PaySafeCard lub BLIK.',
                    'default'     => 'Manualna płatność po kontakcie z administratorem poprzez PaySafeCard lub BLIK.',
                ),
            );

        }

        /**
         * You will need it if you want your custom credit card form, Step 4 is about it
         */
        public function payment_fields() {

            echo '<p><b>Uwaga, administrator sklepu będzie się z Tobą kontaktował za pomocą adresu e-mail, który podałeś powyżej lub za pomocą Messengera/Discorda, do którego możesz wkleić link w dodatkowych informacjach, do zamówienia. (Jeśli link nie wchodzi, może być identyfikator Discord). Pamiętaj, że administrator użyje tylko zaufanej metody kontaktu. <a href="https://s.exoticrp.eu/kontakt/" target="_blank">To są zaufane profile</a>, z których może pochodzić kontakt od nas.</b></p>';

        }

        public function process_payment( $order_id ) {

            $order = wc_get_order( $order_id );

            $order->reduce_order_stock();
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url( $order )
            );

        }
    }
}
