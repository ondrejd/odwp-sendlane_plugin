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
     * @var string $domain
     */
    protected $domain;

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
        $this->domain = array_key_exists( 'domain', $options ) ? $options['domain'] : '';
    }

    /**
     * @param string $cmd
     * @return string
     */
    protected function get_api_url( $cmd ) {
        $domain = empty( $this->domain ) ? '' : $this->domain . '.';

        return 'https://' . $domain . 'sendlane.com/api/v1/' . $cmd .
                '?api=' . $this->api_key . '&hash=' . $this->hash_key;        
    }

    /**
     * @internal
     * @param string $url
     * @return mixed
     */
    private function call_server( $url ) {
        $response = wp_remote_get( $url );
        $http_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( $http_code == 200 ) {
            return json_decode( $body );
        }

        return null;
    }

    /**
     * @link http://help.sendlane.com/knowledgebase/api-docs/#lists
     * @return array Sendlane lists.
     * @todo Finish this!
     */
    public function get_lists() {
        $url = $this->get_api_url( 'list' );
        $lists = $this->call_server( $url );

        if( ! is_array( $lists ) ) {
            $lists = array();
        }

        return $lists;
    }

    /**
     * @link http://help.sendlane.com/knowledgebase/api-docs/#tags
     * @return array Sendlane tags.
     * @todo Finish this!
     */
    public function get_tags() {
        $url = $this->get_api_url( 'tags' );
        $tags = $this->call_server( $url );

        if( ! is_array( $tags ) ) {
            $tags = array();
        }

        return $tags;
    }
}

endif;