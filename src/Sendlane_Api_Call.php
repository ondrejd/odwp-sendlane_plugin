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


if( ! class_exists( 'Sendlane_Api_Call' ) ) :

/**
 * Simple class used for describing Sendlane API call.
 * @since 1.0.0
 */
class Sendlane_Api_Call {

    /**
     * @const string
     * @since 1.0.0
     */
    const TYPE_GET = 'GET';

    /**
     * @const string
     * @since 1.0.0
     */
    const TYPE_POST = 'POST';

    /**
     * @var string $name
     * @since 1.0.0
     */
    protected $name;

    /**
     * @var string $name
     * @since 1.0.0
     */
    protected $description;

    /**
     * @var Sendlane_Api_Call_Params $parameters
     * @since 1.0.0
     */
    protected $parameters;

    /**
     * @var string $type Either {@see Sendlane_Api_Call::TYPE_GET} or {@see Sendlane_Api_Call::TYPE_POST}.
     * @since 1.0.0
     */
    protected $type;

    /**
     * Constructor.
     * @param string $name Name of the API call.
     * @param string $desc Description of the API call.
     * @param string $type Either {@see Sendlane_Api_Call::TYPE_GET} or {@see Sendlane_Api_Call::TYPE_POST}.
     * @param Sendlane_Api_Call_Params $params Parameters of the API call.
     * @return void
     * @since 1.0.0
     */
    public function __construct( $name, $desc, $type, Sendlane_Api_Call_Params $params ) {
        $this->name        = $name;
        $this->description = $desc;
        $this->type        = $type;
        $this->parameters  = $params;
    }

    /**
     * @return string Name of the API call.
     * @since 1.0.0
     */
    public function get_name() : string {
        return $this->name;
    }

    /**
     * @return string Description of the API call.
     * @since 1.0.0
     */
    public function get_description() : string {
        return $this->description;
    }

    /**
     * @return string Type of the API call (either POST or GET).
     * @since 1.0.0
     */
    public function get_type() : string {
        return $this->type;
    }

    /**
     * @return Sendlane_Api_Call_Params Parameters of the API call.
     * @since 1.0.0
     */
    public function get_parameters() : Sendlane_Api_Call_Params {
        return $this->parameters;
    }

    /**
     * @return string JSON string describing the Sendlane API call.
     * @since 1.0.0
     */
    public function to_json() : string {
        $name = str_replace( '"', '&quot;', $this->name );
        $desc = str_replace( '"', '&quot;', $this->description );
        $json = <<<JSON
{
    "description": "{$desc}",
    "name": "{$name}",
    "parameters": {$this->get_parameters()->to_json()},
    "type": "{$this->get_type()}"
}
JSON;

        return $json;
    }
}

endif;
