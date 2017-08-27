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
     * @var string $name
     * @since 1.0.0
     */
    protected $name;

    /**
     * @var int $type
     * @since 1.0.0
     */
    protected $type = self::TYPE_STR;

    /**
     * @var bool $required
     * @since 1.0.0
     */
    protected $required = false;

    /**
     * Constructor.
     * @param string  $name      Name of the parameter.
     * @param integer $type      Parameter's type ({@see Sendlane_Api_Call_Param::TYPE_INT} or {@see Sendlane_Api_Call_Param::TYPE_INT}).
     * @param boolean $required  TRUE if parameter is required.
     * @return void
     */
    public function __construct( $name, integer $type, boolean $required ) {
        $this->name = $name;
        $this->type = $type;
        $this->required = $required;
    }

    /**
     * @return string Name of the parameter.
     * @since 1.0.0
     */
    public function get_name() : string {
        return $this->name;
    }

    /**
     * @return int Parameter's type ({@see Sendlane_Api_Call_Param::TYPE_INT} or {@see Sendlane_Api_Call_Param::TYPE_INT}).
     * @since 1.0.0
     */
    public function get_type() : int {
        return $this->type;
    }

    /**
     * @return bool TRUE if parameter is required.
     * @since 1.0.0
     */
    public function get_required() : bool {
        return $this->required;
    }
}

endif;
