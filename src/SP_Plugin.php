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
    const SETTINGS_KEY = SP_SLUG . '_settings';

    /**
     * @const string
     * @since 1.0.0
     */
    const TABLE_NAME = SP_SLUG;

    /**
     * @var array $admin_screens Array with admin screens.
     * @since 1.0.0
     */
    public static $admin_screens = [];

    /**
     * @var string
     * @since 1.0.0
     */
    public static $options_page_hook;

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
     * @internal Deactivates the plugin.
     * @return void
     * @since 1.0.0
     */
    public static function deactivate() {
        //...
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
     * @param mixed $default Option's default value.
     * @return mixed Option's value.
     * @since 1.0.0
     */
    public static function get_option( $key, $default = null ) {
        $options = self::get_options();

        if( array_key_exists( $key, $options ) ) {
            return $options[$key];
        }

        return $default;
    }

    /**
     * Initializes the plugin.
     * @return void
     * @since 1.0.0
     */
    public static function initialize() {
        register_activation_hook( SP_FILE, [__CLASS__, 'activate'] );
        register_deactivation_hook( SP_FILE, [__CLASS__, 'deactivate'] );
        register_uninstall_hook( SP_FILE, [__CLASS__, 'uninstall'] );

        add_action( 'init', [__CLASS__, 'init'] );
        add_action( 'admin_init', [__CLASS__, 'admin_init'] );
        add_action( 'admin_menu', [__CLASS__, 'admin_menu'] );
        add_action( 'admin_bar_menu', [__CLASS__, 'admin_menu_bar'], 100 );
        add_action( 'plugins_loaded', [__CLASS__, 'plugins_loaded'] );
        add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts'] );
        add_action( 'admin_enqueue_scripts', [__CLASS__, 'admin_enqueue_scripts'] );
    }

    /**
     * Hook for "init" action.
     * @return void
     * @since 1.0.0
     */
    public static function init() {
        // Initialize locales
        $path = SP_PATH . 'languages';
        load_plugin_textdomain( SP_SLUG, false, $path );

        // Initialize options
        $options = self::get_options();

        // Initialize custom post types
        self::init_custom_post_types();

        // Initialize shortcodes
        self::init_shortcodes();

        // Initialize admin sceens
        self::init_screens();
        self::screens_call_method( 'init' );

        // Load Sendlane API sources
        include( SP_PATH . 'src/Sendlane_Api_Call_Param.php' );
        include( SP_PATH . 'src/Sendlane_Api_Call.php' );
        include( SP_PATH . 'src/Sendlane_Api_Calls.php' );
        include( SP_PATH . 'src/Sendlane_Api.php' );
    }

    /**
     * Initialize custom post types.
     * @return void
     * @since 1.0.0
     */
    public static function init_custom_post_types() {
        //...
    }

    /**
     * Registers our shortcodes.
     * @return void
     * @since 1.0.O
     */
    public static function init_shortcodes() {
        //...
    }

    /**
     * Initialize settings using <b>WordPress Settings API</b>.
     * @link https://developer.wordpress.org/plugins/settings/settings-api/
     * @return void
     * @since 1.0.0
     */
    protected static function init_settings() {
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
     * Initialize admin screens.
     * @return void
     * @since 1.0.0
     */
    protected static function init_screens() {
        include( SP_PATH . 'src/SP_Screen_Prototype.php' );
        include( SP_PATH . 'src/SP_Options_Screen.php' );
        include( SP_PATH . 'src/SP_Actions_List_Screen.php' );
        include( SP_PATH . 'src/SP_Add_Action_Screen.php' );

        /**
         * @var SP_Options_Screen $options_screen
         */
        $options_screen = new SP_Options_Screen();
        self::$admin_screens[$options_screen->get_slug()] = $options_screen;

        /**
         * @var SP_Actions_List_Screen $actions_list_screen
         */
        $actions_list_screen = new SP_Actions_List_Screen();
        self::$admin_screens[$actions_list_screen->get_slug()] = $actions_list_screen;

        /**
         * @var SP_Add_Action_Screen $add_action_screen
         */
        $add_action_screen = new SP_Add_Action_Screen();
        self::$admin_screens[$add_action_screen->get_slug()] = $add_action_screen;
    }

    /**
     * Hook for "admin_init" action.
     * @return void
     * @since 1.0.0
     */
    public static function admin_init() {
        register_setting( self::SLUG, self::SETTINGS_KEY );

        self::check_environment();
        self::init_settings();
        self::screens_call_method( 'admin_init' );
        self::admin_init_widgets();
    }

    /**
     * @internal Initializes WP admin dashboard widgets.
     * @return void
     * @since 1.0.0
     */
    public static function admin_init_widgets() {
        include( SP_PATH . 'src/SP_Dashboard_Widget.php' );
        add_action( 'wp_dashboard_setup', ['SP_Dashboard_Widget', 'init'] );
    }

    /**
     * Hook for "admin_menu" action.
     * @return void
     * @since 1.0.0
     */
    public static function admin_menu() {
        // Call action for `admin_menu` hook on all screens.
        self::screens_call_method( 'admin_menu' );
    }

    /**
     * Hook for "admin_menu_bar" action.
     * @link https://codex.wordpress.org/Class_Reference/WP_Admin_Bar/add_menu
     * @param \WP_Admin_Bar $bar
     * @return void
     * @since 1.0.0
     */
    public static function admin_menu_bar( \WP_Admin_Bar $bar ) {
        //...
    }

    /**
     * Hook for "admin_enqueue_scripts" action.
     * @param string $hook
     * @return void
     * @since 1.0.0
     */
    public static function admin_enqueue_scripts( $hook ) {
        wp_enqueue_script( SP_SLUG, plugins_url( 'assets/js/admin.js', SP_FILE ), ['jquery'] );
        wp_localize_script( SP_SLUG, 'odwpsp', [
            //...
        ] );
        wp_enqueue_style( SP_SLUG, plugins_url( 'assets/css/admin.css', SP_FILE ) );
    }

    /**
     * Checks environment we're running and prints admin messages if needed.
     * @return void
     * @since 1.0.0
     */
    public static function check_environment() {
        //...
    }

    /**
     * Loads specified template with given arguments.
     * @param string $template
     * @param array  $args (Optional.)
     * @return string Output created by rendering template.
     * @since 1.0.0
     */
    public static function load_template( $template, array $args = [] ) {
        extract( $args );
        $path = sprintf( '%spartials/%s.phtml', SP_PATH, $template );
        ob_start( function() {} );
        include( $path );
        return ob_get_flush();
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
        wp_enqueue_script( SP_SLUG, plugins_url( 'assets/js/public.js', SP_FILE ), ['jquery'] );
        wp_localize_script( SP_SLUG, 'odwpsp', [
            //...
        ] );
        wp_enqueue_style( SP_SLUG, plugins_url( 'assets/css/public.css', SP_FILE ) );
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
     * @internal Uninstalls the plugin.
     * @return void
     * @since 1.0.0
     */
    public static function uninstall() {
        if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            return;
        }

        //...
    }

    /**
     * @internal Prints error message in correct WP amin style.
     * @param string $msg Error message.
     * @param string $type (Optional.) One of ['error','info','success','warning'].
     * @param boolean $dismissible (Optional.) Is notice dismissible?
     * @return void
     * @since 1.0.0
     */
    public static function print_admin_notice( $msg, $type = 'info', $dismissible = true ) {
        $class = 'notice';

        if( in_array( $type, ['error','info','success','warning'] ) ) {
            $class .= ' notice-' . $type;
        } else {
            $class .= ' notice-info';
        }

        if( $dismissible === true) {
            $class .= ' s-dismissible';
        }
        
        printf( '<div class="%s"><p>%s</p></div>', $class, $msg );
    }

    /**
     * On all screens call method with given name.
     *
     * Used for calling hook's actions of the existing screens.
     * See {@see SP_Plugin::admin_menu} for an example how is used.
     *
     * If method doesn't exist in the screen object it means that screen
     * do not provide action for the hook.
     *
     * @access private
     * @param  string  $method
     * @return void
     * @since 1.0.0
     */
    private static function screens_call_method( $method ) {
        foreach ( self::$admin_screens as $slug => $screen ) {
            if( method_exists( $screen, $method ) ) {
                call_user_func( [ $screen, $method ] );
            }
        }
    }
}

endif;
