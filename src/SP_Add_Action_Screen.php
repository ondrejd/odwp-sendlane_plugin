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

if( ! class_exists( 'SP_Add_Action_Screen' ) ):

/**
 * Add action administration screen.
 * @since 1.0.0
 */
class SP_Add_Action_Screen extends SP_Screen_Prototype {

    /**
     * @const string
     * @since 1.0.0
     */
    const SLUG = SP_SLUG . '-add_action';

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
        $this->menu_title = __( 'Přidat akci', 'odwp-sendlane_plugin' );
        $this->page_title = __( 'Přidat akci', 'odwp-sendlane_plugin' );

        // Specify help tabs
        $this->help_tabs[] = [
            'id'      => self::SLUG . '-add-action-helptab',
            'title'   => __( 'Obecné', 'odwp-sendlane_plugin' ),
            'content' => SP_Plugin::load_template( 'screen-add_action-helptab' ),
        ];

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
        $this->hookname = add_submenu_page(
                SP_Actions_List_Screen::SLUG,
                $this->page_title,
                $this->menu_title,
                'manage_options',
                self::SLUG,
                [$this, 'render']
        );

        add_action( 'load-' . $this->hookname, [$this, 'screen_load'] );
    }

    /**
     * Action for `admin_enqueue_scripts` hook.
     * @param  array $params (Optional.) Array with additional parameters.
     * @return void
     * @see SP_Plugin::admin_enqueue_scripts()
     * @since 1.0.0
     */
    public function admin_enqueue_scripts( array $params = [] ) {
        if( $params['hook'] == $this->hookname ) {
            wp_enqueue_script( self::SLUG, plugins_url( 'assets/js/screen-add_action.js', SP_FILE ), ['jquery'] );
            wp_localize_script( self::SLUG, 'odwpsp1', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'defaults' => SP_Plugin::get_options(),
                'i18n' => [
                    'form_title' => __( 'Parametry akce', 'odwp-sendlane_plugin' ),
                    'label_title' => __( 'Parametr <code>%s</code>', 'odwp-sendlane_plugin' ),
                    'info_title' => __( 'Vybraná akce', 'odwp-sendlane_plugin' ),
                    'info_lbl1' => __( 'Název:', 'odwp-sendlane_plugin' ),
                    'info_lbl2' => __( 'Popis:', 'odwp-sendlane_plugin' ),
                    'info_lbl3' => __( 'Dokumentace:', 'odwp-sendlane_plugin' ),
                ],
            ] );
        }
    }

    /**
     * Render page self.
     * @param array $args (Optional.) Arguments for rendered template.
     * @return void
     * @since 1.0.0
     */
    public function render( $args = [] ) {
        $args['messages'] = $this->process_form();

        parent::render( $args );
    }

    /**
     * @internal Process add action form.
     * @return array
     * @since 1.0.0
     * @todo Check WP_nonce!!!
     */
    protected function process_form() {
        $msgs = [];

        $submit = filter_input( INPUT_POST, 'submit' );
        if( empty( $submit ) ) {
            return $msgs;
        }

        $data = [
            'page' => filter_input( INPUT_POST, 'page' ),
            'action' => filter_input( INPUT_POST, 'action' ),
            'params' => filter_input( INPUT_POST, 'action-param', FILTER_DEFAULT , FILTER_REQUIRE_ARRAY ),
        ];
        // XXX Process add action form!
echo '<pre>';
var_dump( $_POST );
var_dump( $data );
exit();

        return $msgs;
    }
}

endif;
