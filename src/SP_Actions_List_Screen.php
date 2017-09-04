<?php
/**
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-debug_log for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-sendlane_plugin
 * @since 1.0.0
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'SP_Actions_List_Screen' ) ):

/**
 * Administration screen for plugin's options.
 * @since 1.0.0
 */
class SP_Actions_List_Screen extends SP_Screen_Prototype {

    /**
     * @const string
     * @since 1.0.0
     */
    const SLUG = SP_SLUG . '-page_list';

    /**
     * Constructor.
     * @param WP_Screen $screen Optional.
     * @return void
     * @since 1.0.0
     * @todo Setting `$this->slug` is just convience - {@see SP_Actions_List_Screen::SLUG} shwould be used anywhere.
     */
    public function __construct( \WP_Screen $screen = null ) {
        // Main properties
        $this->slug = self::SLUG;
        $this->menu_title = __( 'Sendlane Plugin', 'odwp-sendlane_plugin' );
        $this->page_title = __( 'Sendlane Plugin', 'odwp-sendlane_plugin' );

        // Specify help tabs
        $this->help_tabs = [];

        // Specify help sidebars
        $this->help_sidebars = [];

        // Specify screen options
        $this->options = [];
        $this->enable_screen_options = false;

        // Finish screen constuction
        parent::__construct( $screen );
    }

    /**
     * Action for `admin_menu` hook.
     * @param  array $params (Optional.) Array with additional parameters.
     * @return void
     * @see SP_Plugin::admin_menu()
     * @since 1.0.0
     */
    public function admin_menu( array $params = [] ) {
        $this->hookname = add_menu_page(
                $this->page_title,
                $this->menu_title,
                'manage_options',
                self::SLUG,
                [$this, 'render'],
                null,
                100
        );

        add_action( 'load-' . $this->hookname, [$this, 'screen_load'] );
    }
}

endif;
