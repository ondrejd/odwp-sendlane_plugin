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


if( ! class_exists( 'Sendlane_Api_Call_Param' ) ) :

/**
 * Simple class used for describing single Sendlane API call param.
 * @since 1.0.0
 */
class Sendlane_Api_Call_Param {

    /**
     * @const int
     * @since 1.0.0
     */
    const TYPE_INT = 0;

    /**
     * @const int
     * @since 1.0.0
     */
    const TYPE_STR = 1;

    /**
     * @var string $description Description of the Sendlane API call parameter.
     * @since 1.0.0
     */
    protected $description = '';

    /**
     * @var string $name Name of the Sendlane API call parameter.
     * @since 1.0.0
     */
    protected $name;

    /**
     * @var bool $required Is parameter of the Sendlane API call required?
     * @since 1.0.0
     */
    protected $required = false;

    /**
     * @var int $type Type of the Sendlane API call parameter.
     * @since 1.0.0
     */
    protected $type = self::TYPE_STR;

    /**
     * Constructor.
     * @param string  $name      Name of the parameter.
     * @param integer $type      Parameter's type ({@see Sendlane_Api_Call_Param::TYPE_INT} or {@see Sendlane_Api_Call_Param::TYPE_INT}).
     * @param boolean $required  TRUE if parameter is required.
     * @param string  $desc      Description of the parameter.
     * @return void
     */
    public function __construct( $name, int $type, bool $required, string $desc ) {
        $this->description = $desc;
        $this->name = $name;
        $this->required = $required;
        $this->type = $type;
    }

    /**
     * @return string Parameter's description.
     * @since 1.0.0
     */
    public function get_description() : string {
        return $this->description;
    }

    /**
     * @return string Name of the parameter.
     * @since 1.0.0
     */
    public function get_name() : string {
        return $this->name;
    }

    /**
     * @return bool TRUE if parameter is required.
     * @since 1.0.0
     */
    public function get_required() : bool {
        return $this->required;
    }

    /**
     * @return int Parameter's type ({@see Sendlane_Api_Call_Param::TYPE_INT} or {@see Sendlane_Api_Call_Param::TYPE_STR}).
     * @since 1.0.0
     */
    public function get_type() : int {
        return $this->type;
    }

    /**
     * Returns the parameter as a JSON string.
     * @return string
     * @since 1.0.0
     */
    public function to_json() : string {
        $name = str_replace( '"', '&quot;', $this->name );
        $desc = str_replace( '"', '&quot;', $this->description );
        $json = <<<JSON

    {
        "name": "{$name}",
        "description": "{$desc}",
        "type": {$this->type},
        "required": %s
    }
JSON;

        return str_replace( '%s', $this->get_required() ? '1' : '0', $json );
    }
}

endif;
