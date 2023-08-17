<?php

/**
 * Fired during plugin activation
 *
 * @link       https://mehrdaddindar.ir
 * @since      1.0.0
 *
 * @package    Vanak
 * @subpackage Vanak/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Vanak
 * @subpackage Vanak/includes
 * @author     Mehrdad Dindar <mehrdad.dindar@live.com>
 */
class Vanak_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        $checkWoo = new Vanak_Activator();
        $checkWoo->checkWoocommerce();
	}

    private function checkWoocommerce()
    {
        /*<------------------- Check if WooCommerce is active ------------------> */
        // Woocommerce main file location
        $woocommerce = 'woocommerce/woocommerce.php';
        // woocommerce version to check
        $version_to_check = '7.8.0';

        // Require woocommerce plugin
        if (!is_plugin_active($woocommerce) && current_user_can('activate_plugins')) {
            wp_die(
                __('Please activate Woocommerce first.', 'vanak'),
                __('msg', 'vanak'),
                [
                    'code' => 'RequireWoocommerce',
                    'back_link' => true,
                ]
            );
        } else {
            $woocommerce_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $woocommerce);
            $woocommerce_error = (bool)version_compare(
                $woocommerce_data['Version'],
                $version_to_check,
                '>='
            );
            // Check compatible woocommerce version
            if (!$woocommerce_error) {
                wp_die(
					sprintf(
						__(
							'Your WooCommerce plugin version must be at least version <code>%s</code> .Please update it.',
							'vanak'
						),
						$version_to_check
					),
                    __('msg', 'vanak'),
                    [
                        'code' => 'checkDependencies',
                        'back_link' => true,
                    ]
                );
            }
        }
    }

}
