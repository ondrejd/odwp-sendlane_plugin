<?php
/**
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-sendlane_plugin for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-sendlane_plugin
 */

if ( ! class_exists( 'odwpSendlaneApi' ) ) :

/**
 * Sendlane API.
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @since 1.0
 */
class odwpSendlaneApi {
    /**
     * @var string $api_key
     */
    protected $api_key;

    /**
     * @var string $hash_key
     */
    protected $hash_key;

    /**
     * Constructor.
     * @param array $options
     * @return void
     */
    public function __construct( $options ) {
        if (
                ! is_array( $options ) ||
                ( array_key_exists( 'api_key', $options ) &&
                  array_key_exists( 'hash_key', $options ) )
        ) {
            $options = odwpSendlanePlugin::get_default_options();
        }

        $this->api_key = $options['api_key'];
        $this->hash_key = $options['hash_key'];
    }

    /**
     * @return array Sendlane lists.
     * @todo Finish this!
     */
    public function get_lists() {
        $lists = array();
        // ...
        return $lists;
    }

    /**
     * @return array Sendlane tags.
     * @todo Finish this!
     */
    public function get_tags() {
        $tags = array();
        // ...
        return $tags;
    }
}

endif;