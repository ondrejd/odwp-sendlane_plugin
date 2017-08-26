<?php
/**
 * Plugin Name: Sendlane plugin
 * Plugin URI: https://github.com/ondrejd/odwp-sendlane_plugin
 * Description: Plugin pro integraci přihlášení uživatelů k hromadným emailům obsluhovaným pomocí <a href="https://sendlane.com/" target="_blank">Sendlane API</a>.
 * Version: 1.0.0
 * Author: Ondřej Doněk
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
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-sendlane_plugin for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-sendlane_plugin
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Some constants
include( dirname( __FILE__ ) . '/src/Sendlane_Api.php' );
include( dirname( __FILE__ ) . '/src/Actions_List.php' );
include( dirname( __FILE__ ) . '/src/Actions_Table.php' );


if ( ! class_exists( 'odwpSendlanePlugin' ) ) :

/**
 * Main class.
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @since 1.0
 */
class odwpSendlanePlugin {
    /**
     * @const string Plugin's slug.
     */
    const SLUG = 'odwp-sendlane_plugin';

    /**
     * @const string Plugin's version.
     */
    const VERSION = '1.0.0';

    /**
     * @const string
     */
    const SETTINGS_KEY = 'odwpsp_settings';

    /**
     * @const string
     */
    const TABLE_NAME = 'odwpsp';

    /**
     * @var Sendlane_Api $sendlane
     */
    protected static $sendlane;

    /**
     * Activates the plugin.
     * @global wpdb $wpdb
     * @return void
     */
    public static function activate() {
        global $wpdb;

        // Create our database table if needed
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var('SHOW TABLES LIKE "'.$table_name.'" ') != $table_name) {
            $sql = "CREATE TABLE `$table_name` (".
                    "    `id` INTEGER ( 20 ) NOT NULL AUTO_INCREMENT ,".
                    "    `page_id` INTEGER( 20 ) NOT NULL ,".
                    "    `type` ENUM ( 'subscribe', 'unsubscribe', 'tag_add', 'tag_remove' ) NOT NULL ,".
                    "    `list_id` INTEGER ( 20 ) ,".
                    "    `tag_id` INTEGER ( 20 ) ,".
                    "    PRIMARY KEY `id` ( `id` )".
                    ") $charset_collate; ";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }

    /**
     * @internal Deactivates the plugin directly by updating WP option `active_plugins`.
     * @link https://developer.wordpress.org/reference/functions/deactivate_plugins/
     * @return void
     * @todo Check if using `deactivate_plugins` whouldn't be better.
     */
    public static function deactivate_raw() {
        $active_plugins = get_option( 'active_plugins' );
        $out = [];
        foreach( $active_plugins as $key => $val ) {
            if( $val != sprintf( "%$1s/%$1s.php", self::SLUG ) ) {
                $out[$key] = $val;
            }
        }
        update_option( 'active_plugins', $out );
    }

    /**
     * @return array Default values for settings of the plugin.
     */
    public static function get_default_options() {
        return [
            'api_key' => '',
            'hash_key' => '',
            'domain' => '',
        ];
    }

    /**
     * @return array Settings of the plugin.
     */
    public static function get_options() {
        $defaults = self::get_default_options();
        $options = get_option( self::SETTINGS_KEY, [] );
        $update = false;

        // Fill defaults for the options that are not set yet
        foreach( $defaults as $key => $val ) {
            if( ! array_key_exists( $key, $options ) ) {
                $options[$key] = $val;
                $update = true;
            }
        }

        // Updates options if needed
        if( $update === true) {
            update_option( self::SETTINGS_KEY, $options );
        }

        return $options;
    }

    /**
     * Returns value of option with given key.
     * @param string $key Option's key.
     * @return mixed Option's value.
     * @throws Exception Whenever option with given key doesn't exist.
     */
    public static function get_option( $key ) {
        $options = self::get_options();

        if( ! array_key_exists( $key, $options ) ) {
            throw new Exception( 'Option "'.$key.'" is not set!' );
        }

        return $options[$key];
    }

    /**
     * Initializes the plugin.
     * @return void
     */
    public static function init() {
        register_activation_hook( __FILE__, [__CLASS__, 'activate'] );
        register_uninstall_hook( __FILE__, [__CLASS__, 'uninstall'] );

        add_action( 'init', [__CLASS__, 'init_textdomain'] );
        add_action( 'admin_init', [__CLASS__, 'admin_init'] );
        add_action( 'admin_menu', [__CLASS__, 'admin_menu'] );
        add_action( 'plugins_loaded', [__CLASS__, 'plugins_loaded'] );
        add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts'] );
        add_action( 'admin_enqueue_scripts', [__CLASS__, 'admin_enqueue_scripts'], 10 );
    }

    /**
     * Hook for "init" action.
     * @return void
     */
    public static function init_textdomain() {
        $path = dirname( __FILE__ ) . '/languages';
        load_plugin_textdomain( self::SLUG, false, $path );
    }

    /**
     * Hook for "admin_init" action.
     * @return void
     */
    public static function admin_init() {
        register_setting( self::SLUG, self::SETTINGS_KEY );

        $options = self::get_options();
        self::$sendlane = new Sendlane_Api( $options );

        $section1 = self::SETTINGS_KEY . '_section_1';
        add_settings_section(
                $section1,
                __( 'Sendlane API' ),
                [__CLASS__, 'render_settings_section_1'],
                self::SLUG
        );

        add_settings_field(
                'api_key',
                __( 'API klíč', self::SLUG ),
                [__CLASS__, 'render_setting_api_key'],
                self::SLUG,
                $section1
        );

        add_settings_field(
                'hash_key',
                __( '<em>Hash</em> klíč', self::SLUG ),
                [__CLASS__, 'render_setting_hash_key'],
                self::SLUG,
                $section1
        );

        add_settings_field(
                'domain',
                __( 'Doména', self::SLUG ),
                [__CLASS__, 'render_setting_domain'],
                self::SLUG,
                $section1
        );
    }

    /**
     * Hook for "admin_menu" action.
     * @return void
     */
    public static function admin_menu() {
        add_menu_page(
                __( 'Sendlane plugin', self::SLUG ),
                __( 'Sendlane plugin', self::SLUG ),
                'manage_options',
                'odwpsp_menu',
                [__CLASS__, 'render_admin_page_list'],
                null,
                100
        );
        add_submenu_page(
                'odwpsp_menu',
                __( 'Přidat akci', self::SLUG ),
                __( 'Přidat akci', self::SLUG ),
                'manage_options',
                'odwpsp_menu_add',
                [__CLASS__, 'render_admin_page_add']
        );
        add_submenu_page(
                'odwpsp_menu',
                __( 'Nastavení pro Sendlane plugin', self::SLUG ),
                __( 'Nastavení', self::SLUG ),
                'manage_options',
                'odwpsp_menu_options',
                [__CLASS__, 'admin_options_page']
        );
        /*add_options_page(
                __( 'Nastavení pro Sendlane plugin', self::SLUG ),
                __( 'Sendlane plugin', self::SLUG ),
                'manage_options',
                self::SLUG . '-options',
                [__CLASS__, 'admin_options_page']
        );*/
    }

    /**
     * Hook for "admin_enqueue_scripts" action.
     * @param string $hook
     * @return void
     */
    public static function admin_enqueue_scripts( $hook ) {
        wp_enqueue_script( self::SLUG, plugins_url( 'assets/js/admin.js', __FILE__ ), ['jquery'] );
        wp_localize_script( self::SLUG, 'odwpsp', [
            //...
        ] );
        wp_enqueue_style( self::SLUG, plugins_url( 'assets/css/admin.css', __FILE__ ) );
    }

    /**
     * Renders plugin's options page.
     * @return void
     */
    public static function admin_options_page() {
?>
<form action="options.php" method="post">
    <h2><?php _e( 'Nastavení pro Sendlane plugin', self::SLUG ) ?></h2>
<?php
        settings_fields( self::SLUG );
        do_settings_sections( self::SLUG );
        submit_button();
?>
</form>
<?php
    }

    /**
     * Hook for "plugins_loaded" action.
     * @return void
     */
    public static function plugins_loaded() {
        //...
    }

    /**
     * Hook for "wp_enqueue_scripts" action.
     * @return void
     */
    public static function enqueue_scripts() {
        //...
    }

    /**
     * @return array Sendlane lists.
     */
    public static function get_lists() {
        return self::$sendlane->lists();
    }

    /**
     * @return array Sendlane tags.
     */
    public static function get_tags() {
        return self::$sendlane->tags();
    }

    /**
     * Renders settings section 1.
     * @return void
     */
    public static function render_settings_section_1() {
?>
<p class="description">
    <?php printf(
            __( 'Pro přístup ke službě <b>Sendlane</b> potřebujete <abbr title="Application Program Interface">API</abbr> a <em>hash</em> klíč (více na stránce <a href="%s" target="blank">What is an API Key?</a>). Nezapomeňte zadat také příslušnou doménu pro zadané klíče.', self::SLUG ),
            'http://help.sendlane.com/knowledgebase/api-key/'
    ) ?>
</p>
<?php
    }

    /**
     * Renders input for "api_key" setting.
     * @return void
     */
    public static function render_setting_api_key() {
        $options = self::get_options();
?>
<input type="text" name="odwpsp_settings[api_key]" value="<?= $options['api_key'] ?>" class="regular-text">
<?php
    }

    /**
     * Renders input for "hash_key" setting.
     * @return void
     */
    public static function render_setting_hash_key() {
        $options = self::get_options();
?>
<input type="text" name="odwpsp_settings[hash_key]" value="<?= $options['hash_key'] ?>" class="regular-text">
<?php
    }

    /**
     * Renders input for "domain" setting.
     * @return void
     */
    public static function render_setting_domain() {
        $options = self::get_options();
?>
<input type="text" name="odwpsp_settings[domain]" value="<?= $options['domain'] ?>" class="regular-text">
<?php
    }

    /**
     * Renders plugin's administration page "List pages".
     * @return void
     */
    public static function render_admin_page_list() {
        ob_start( function() {} );
        include( dirname( __FILE__ ) . '/partials/setting-page_list.phtml' );
        echo ob_get_flush();
    }

    /**
     * Renders plugin's administration page "Add page".
     * @return void
     * @todo Add `wpnonce` for the security!
     */
    public static function render_admin_page_add() {
        ob_start( function() {} );
        include( dirname( __FILE__ ) . '/partials/setting-page_add.phtml' );
        echo ob_get_flush();
    }

    /**
     * @internal Uninstalls the plugin.
     * @return void
     */
    private static function uninstall() {
        if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            return;
        }

        // Nothing to do...
    }

    /**
     * @internal Prints error message in correct WP amin style.
     * @param string $msg Error message.
     * @param string $type (Optional.) One of ['info','updated','error'].
     * @return void
     */
    protected static function print_error( $msg, $type = 'info' ) {
        $avail_types = ['error', 'info', 'updated'];
        $_type = in_array( $type, $avail_types ) ? $type : 'info';
        printf( '<div class="%s"><p>%s</p></div>', $_type, $msg );
    }
} // End of odwpSendlanePlugin

endif;

// Initialize plugin
odwpSendlanePlugin::init();
