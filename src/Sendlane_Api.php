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


if ( ! class_exists( 'Sendlane_Api' ) ) :

/**
 * Sendlane API.
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @since 1.0
 */
class Sendlane_Api {

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
            ! ( array_key_exists( 'api_key', $options ) &&
                array_key_exists( 'hash_key', $options ) )
        ) {
            $options = odwpSendlanePlugin::get_default_options();
        }

        $this->api_key = $options['api_key'];
        $this->hash_key = $options['hash_key'];
        $this->domain = array_key_exists( 'domain', $options ) ? $options['domain'] : '';
    }

    /**
     * @internal Retrieves correct API call's URL by given call name.
     * @param string $cmd
     * @return string
     */
    protected function get_api_url( $call ) {
        $domain = empty( $this->domain ) ? '' : $this->domain . '.';

        return 'https://' . $domain . 'sendlane.com/api/v1/' . $call .
                '?api=' . $this->api_key . '&hash=' . $this->hash_key;        
    }

    /**
     * @internal Call Sendlane server.
     * @param string $url
     * @param boolean $get (Optional) Set on TRUE if you want use GET instead of POST.
     * @return mixed
     */
    protected function call_server( $url, $get = false ) {
        $response = null;

        if( $get === true ) {
            $response = wp_remote_get( $url );
        } else {
            $response = wp_remote_post( $url );
        }

        /*$http_code = wp_remote_retrieve_response_code( $response );
        if( empty( $http_code ) || (int) $http_code >= 400 ) {
            // TODO Probably should be WP admin error here better than on other place!
        }*/

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        return $data;
    }

    /**
     * @link http://help.sendlane.com/knowledgebase/api-docs/#lists
     * @return array Sendlane lists.
     * @todo Finish this!
     */
    public function lists() {
        $url = $this->get_api_url( 'lists' );
        $lists = $this->call_server( $url );

        if( ! is_array( $lists ) ) {
            $lists = [];
        }

        return $lists;
    }

    /**
     * @link http://help.sendlane.com/knowledgebase/api-docs/#tags
     * @return array Sendlane tags.
     * @todo Finish this!
     */
    public function tags() {
        $url = $this->get_api_url( 'tags' );
        $tags = $this->call_server( $url );

        if( ! is_array( $tags ) ) {
            $tags = [];
        }

        return $tags;
    }
}

endif;
