<?php
/**
 * Plugin Name: Sendlane Plugin
 * Plugin URI: https://github.com/ondrejd/odwp-sendlane_plugin
 * Description: Plugin that enables using <a href="https://sendlane.com/" target="_blank">Sendlane</a> application for email automation in the <a href="https://wordpress.org/" target="_blank">WordPress</a>.
 * Version: 1.0.0
 * Author: Ondrej Donek
 * Author URI: https://ondrejd.com/
 * License: GPLv3
 * Requires at least: 4.7
 * Tested up to: 4.8.1
 * Tags: sendlane,mailing,users
 * Donate link: https://www.paypal.me/ondrejd
 *
 * Text Domain: odwp-sendlane_plugin
 * Domain Path: /languages/
 *
 * @author Ondrej Donek <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-sendlane_plugin for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-sendlane_plugin
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * This file is just a bootstrap. It checks if requirements of plugins
 * are met and accordingly either allow activating the plugin or stops
 * the activation process.
 *
 * Requirements can be specified either for PHP interperter or for
 * the WordPress self. In both cases you can specify minimal required
 * version and required extensions/plugins.
 *
 * If you are using copy of original file in your plugin you shoud change
 * prefix "odwpsp" and name "odwp-sendlane_plugin" to your own values.
 *
 * To set the requirements go down to line 133 and define array that
 * is used as a parameter for `odwpsp_check_requirements` function.
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Some constants
defined( 'SP_SLUG' ) || define( 'SP_SLUG', 'odwpsp' );
defined( 'SP_NAME' ) || define( 'SP_NAME', 'odwp-sendlane_plugin' );
defined( 'SP_PATH' ) || define( 'SP_PATH', dirname( __FILE__ ) . '/' );
defined( 'SP_FILE' ) || define( 'SP_FILE', __FILE__ );
defined( 'SP_LOG' )  || define( 'SP_LOG', WP_CONTENT_DIR . '/debug.log' );


if( ! function_exists( 'odwpsp_check_requirements' ) ) :
    /**
     * Checks requirements of our plugin.
     * @global string $wp_version
     * @param array $requirements
     * @return array
     * @since 1.0.0
     */
    function odwpsp_check_requirements( array $requirements ) {
        global $wp_version;

        // Initialize locales
        load_plugin_textdomain( SP_SLUG, false, SP_PATH . 'languages' );

        /**
         * @var array Hold requirement errors
         */
        $errors = [];

        // Check PHP version
        if( ! empty( $requirements['php']['version'] ) ) {
            if( version_compare( phpversion(), $requirements['php']['version'], '<' ) ) {
                $errors[] = sprintf(
                        __( 'PHP nesplňuje nároky pluginu na minimální verzi (vyžadována nejméně <b>%s</b>)!', 'odwp-sendlane_plugin' ),
                        $requirements['php']['version']
                );
            }
        }

        // Check PHP extensions
        if( count( $requirements['php']['extensions'] ) > 0 ) {
            foreach( $requirements['php']['extensions'] as $req_ext ) {
                if( ! extension_loaded( $req_ext ) ) {
                    $errors[] = sprintf(
                            __( 'Je vyžadováno rozšíření PHP <b>%s</b>, to ale není nainstalováno!', 'odwp-sendlane_plugin' ),
                            $req_ext
                    );
                }
            }
        }

        // Check WP version
        if( ! empty( $requirements['wp']['version'] ) ) {
            if( version_compare( $wp_version, $requirements['wp']['version'], '<' ) ) {
                $errors[] = sprintf(
                        __( 'Plugin vyžaduje vyšší verzi platformy <b>WordPress</b> (minimálně <b>%s</b>)!', 'odwp-sendlane_plugin' ),
                        $requirements['wp']['version']
                );
            }
        }

        // Check WP plugins
        if( count( $requirements['wp']['plugins'] ) > 0 ) {
            $active_plugins = (array) get_option( 'active_plugins', [] );
            foreach( $requirements['wp']['plugins'] as $req_plugin ) {
                if( ! in_array( $req_plugin, $active_plugins ) ) {
                    $errors[] = sprintf(
                            __( 'Je vyžadován plugin <b>%s</b>, ten ale není nainstalován!', 'odwp-sendlane_plugin' ),
                            $req_plugin
                    );
                }
            }
        }

        return $errors;
    }
endif;


if( ! function_exists( 'odwpsp_deactivate_raw' ) ) :
    /**
     * Deactivate plugin by the raw way (it updates directly WP options).
     * @return void
     * @since 1.0.0
     */
    function odwpsp_deactivate_raw() {
        $active_plugins = get_option( 'active_plugins' );
        $out = [];
        foreach( $active_plugins as $key => $val ) {
            if( $val != SP_NAME . '/' . SP_NAME . '.php' ) {
                $out[$key] = $val;
            }
        }
        update_option( 'active_plugins', $out );
    }
endif;


if( ! function_exists( 'odwpsp_error_log' ) ) :
    /**
     * @internal Write message to the `wp-content/debug.log` file.
     * @param string $message
     * @param integer $message_type (Optional.)
     * @param string $destination (Optional.)
     * @param string $extra_headers (Optional.)
     * @return void
     * @since 1.0.0
     */
    function odwpsp_error_log( string $message, $message_type = 0, $destination = null, $extra_headers = '' ) {
        if( ! file_exists( SP_LOG ) || ! is_writable( SP_LOG ) ) {
            return;
        }

        $record = '[' . date( 'd-M-Y H:i:s', time() ) . ' UTC] ' . $message;
        file_put_contents( SP_LOG, PHP_EOL . $record, FILE_APPEND );
    }
endif;


if( ! function_exists( 'odwpsp_write_log' ) ) :
    /**
     * Write record to the `wp-content/debug.log` file.
     * @param mixed $log
     * @return void
     * @since 1.0.0
     */
    function odwpsp_write_log( $log ) {
        if( is_array( $log ) || is_object( $log ) ) {
            odwpsp_error_log( print_r( $log, true ) );
        } else {
            odwpsp_error_log( $log );
        }
    }
endif;


if( ! function_exists( 'readonly' ) ) :
    /**
     * Prints HTML readonly attribute. It's an addition to WP original
     * functions {@see disabled()} and {@see checked()}.
     * @param mixed $value
     * @param mixed $current (Optional.) Defaultly TRUE.
     * @return string
     * @since 1.0.0
     */
    function readonly( $current, $value = true ) {
        if( $current == $value ) {
            echo ' readonly';
        }
    }
endif;


/**
 * Errors from the requirements check
 * @var array
 */
$odwpsp_errs = odwpsp_check_requirements( [
    'php' => [
        // Enter minimum PHP version you needs
        'version' => '7.0',
        // Enter extensions that your plugin needs
        'extensions' => [
            //'gd',
        ],
    ],
    'wp' => [
        // Enter minimum WP version you need
        'version' => '4.7',
        // Enter WP plugins that your plugin needs
        'plugins' => [
            //'woocommerce/woocommerce.php',
        ],
    ],
] );

// Check if requirements are met or not
if( count( $odwpsp_errs ) > 0 ) {
    // Requirements are not met
    odwpsp_deactivate_raw();

    // In administration print errors
    if( is_admin() ) {
        $err_head = __( '<b>Sendlane Plugin</b>: ', 'odwp-sendlane_plugin' );
        foreach( $odwpsp_errs as $err ) {
            printf( '<div class="error"><p>%s</p></div>', $err_head . $err );
        }
    }
} else {
    // Requirements are met so initialize the plugin.
    include( SP_PATH . 'src/SP_Screen_Prototype.php' );
    include( SP_PATH . 'src/SP_Plugin.php' );
    SP_Plugin::initialize();
}
