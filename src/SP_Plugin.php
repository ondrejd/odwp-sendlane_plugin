<?php
/**
 * @author Ondrej Donek <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-sendlane_plugin for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-sendlane_plugin
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// Some constants
include( SP_PATH . 'src/Sendlane_Api.php' );
include( SP_PATH . 'src/Sendlane_Api_Calls.php' );
include( SP_PATH . 'src/Sendlane_Api_Call.php' );
include( SP_PATH . 'src/Sendlane_Api_Call_Param.php' );
include( SP_PATH . 'src/Actions_List.php' );
include( SP_PATH . 'src/Actions_Table.php' );


if ( ! class_exists( 'SP_Plugin' ) ) :

/**
 * Main class.
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @since 1.0
 */
class SP_Plugin {
    /**
     * @const string Plugin's slug.
     * @since 1.0.0
     */
    const SLUG = 'odwp-sendlane_plugin';

    /**
     * @const string Plugin's version.
     * @since 1.0.0
     */
    const VERSION = '1.0.0';

    /**
     * @const string
     * @since 1.0.0
     */
    const SETTINGS_KEY = 'odwpsp_settings';

    /**
     * @const string
     * @since 1.0.0
     */
    const TABLE_NAME = 'odwpsp';

    /**
     * @var Sendlane_Api $sendlane
     * @since 1.0.0
     */
    protected static $sendlane;

    /**
     * Activates the plugin.
     * @global wpdb $wpdb
     * @return void
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
     */
    public static function init_textdomain() {
        $path = SP_PATH . 'languages';
        load_plugin_textdomain( self::SLUG, false, $path );
    }

    /**
     * Hook for "admin_init" action.
     * @return void
     * @since 1.0.0
     */
    public static function admin_init() {
        register_setting( self::SLUG, self::SETTINGS_KEY );

        $options = self::get_options();
        self::$sendlane = new Sendlane_Api( $options );

        $section1 = self::SETTINGS_KEY . '_section_1';
        add_settings_section(
                $section1,
                __( 'Sendlane API', 'odwp-sendlane_plugin' ),
                [__CLASS__, 'render_settings_section_1'],
                self::SLUG
        );

        add_settings_field(
                'api_key',
                __( 'API klíč', 'odwp-sendlane_plugin' ),
                [__CLASS__, 'render_setting_api_key'],
                self::SLUG,
                $section1
        );

        add_settings_field(
                'hash_key',
                __( '<em>Hash</em> klíč', 'odwp-sendlane_plugin' ),
                [__CLASS__, 'render_setting_hash_key'],
                self::SLUG,
                $section1
        );

        add_settings_field(
                'domain',
                __( 'Doména', 'odwp-sendlane_plugin' ),
                [__CLASS__, 'render_setting_domain'],
                self::SLUG,
                $section1
        );
    }

    /**
     * Hook for "admin_menu" action.
     * @return void
     * @since 1.0.0
     */
    public static function admin_menu() {
        add_menu_page(
                __( 'Sendlane plugin', 'odwp-sendlane_plugin' ),
                __( 'Sendlane plugin', 'odwp-sendlane_plugin' ),
                'manage_options',
                'odwpsp_menu',
                [__CLASS__, 'render_admin_page_list'],
                null,
                100
        );
        add_submenu_page(
                'odwpsp_menu',
                __( 'Přidat akci', 'odwp-sendlane_plugin' ),
                __( 'Přidat akci', 'odwp-sendlane_plugin' ),
                'manage_options',
                'odwpsp_menu_add',
                [__CLASS__, 'render_admin_page_add']
        );
        add_submenu_page(
                'odwpsp_menu',
                __( 'Nastavení pro Sendlane plugin', 'odwp-sendlane_plugin' ),
                __( 'Nastavení', 'odwp-sendlane_plugin' ),
                'manage_options',
                'odwpsp_menu_options',
                [__CLASS__, 'admin_options_page']
        );
    }

    /**
     * Hook for "admin_enqueue_scripts" action.
     * @param string $hook
     * @return void
     * @since 1.0.0
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
     * @since 1.0.0
     */
    public static function admin_options_page() {
        echo self::load_template( 'screen-options_page' );
    }

    /**
     * Hook for "plugins_loaded" action.
     * @return void
     * @since 1.0.0
     */
    public static function plugins_loaded() {
        //...
    }

    /**
     * Hook for "wp_enqueue_scripts" action.
     * @return void
     * @since 1.0.0
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
     * @since 1.0.0
     */
    public static function get_tags() {
        return self::$sendlane->tags();
    }

    /**
     * Renders settings section 1.
     * @return void
     * @since 1.0.0
     */
    public static function render_settings_section_1() {
        echo self::load_template( 'setting-section_1' );
    }

    /**
     * Renders input for "api_key" setting.
     * @return void
     * @since 1.0.0
     */
    public static function render_setting_api_key() {
        echo self::load_template( 'setting-api_key' );
    }

    /**
     * Renders input for "hash_key" setting.
     * @return void
     * @since 1.0.0
     */
    public static function render_setting_hash_key() {
        echo self::load_template( 'setting-hash_key' );
    }

    /**
     * Renders input for "domain" setting.
     * @return void
     * @since 1.0.0
     */
    public static function render_setting_domain() {
        echo self::load_template( 'setting-domain' );
    }

    /**
     * Renders plugin's administration page "List pages".
     * @return void
     * @since 1.0.0
     */
    public static function render_admin_page_list() {
        echo self::load_template( 'screen-page_list' );
    }

    /**
     * Renders plugin's administration page "Add page".
     * @return void
     * @since 1.0.0
     */
    public static function render_admin_page_add() {
        echo self::load_template( 'screen-page_add' );
    }

    /**
     * @internal Uninstalls the plugin.
     * @return void
     * @since 1.0.0
     */
    public static function uninstall() {
        if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            return;
        }

        // Nothing to do...
    }

    /**
     * @internal Prints user notice in correct WP amin style.
     * @param string $msg Error message.
     * @param string $type (Optional.) One of ['info','updated','error'].
     * @param boolean $dismissable (Optional.) Should be message dissmissable?
     * @return void
     * @since 1.0.0
     */
    protected static function print_notice( $msg, $type = 'info', $dismissable = true ) {
        $avail_types = ['error', 'info', 'updated'];
        $_type = in_array( $type, $avail_types ) ? $type : 'info';
        printf( '<div class="%s"><p>%s</p></div>', $_type, $msg );
    }

    /**
     * @internal Loads specified template with given arguments.
     * @param string $template
     * @param array  $args (Optional.)
     * @return string Output created by rendering template.
     * @since 1.0.0
     */
    protected static function load_template( $template, array $args = [] ) {
        extract( $args );
        $path = sprintf( '%spartials/%s.phtml', SP_PATH, $template );
        ob_start( function() {} );
        include( $path );
        return ob_get_flush();
    }
}

endif;
