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


if( ! class_exists( 'Sendlane_Api_Call_Params' ) ) :

/**
 * Class that holds parameters.of the single Sendlane API call.
 * @since 1.0.0
 */
class Sendlane_Api_Call_Params implements \ArrayAccess, \Iterator {

    /**
     * @internal Holds array of parameters.
     * @var array $params
     * @since 1.0.0
     */
    protected $params;

    /**
     * @internal Part of {@see \Iterator} implementation.
     * @var int $index
     * @since 1.0.0
     */
    protected $index = 0;
    
    /**
     * Constructor.
     * @param array $param
     * @return void
     * @since 1.0.0
     */
    public function __construct( Sendlane_Api_Call_Param ...$param ) {
        $this->params = $param;
    }

    /**
     * Constructor.
     * @param scalar $param Array of {@see Sendlane_Api_Call_Param}.
     * @return \Sendlane_Api_Call_Params
     * @since 1.0.0
     */
    public function merge( Sendlane_Api_Call_Params $params) : Sendlane_Api_Call_Params {
        for( $i = 0; $i < $params->count(); $i++ ) {
            $this->params[] = $params[$i];
        }
        return $this;
    }

    /**
     * @return int Parameters count.
     * @since 1.0.0
     */
    public function count() {
        return count( $this->params );
    }

    /**
     * @internal Part of {@see \ArrayAccess} implementation.
     * @param mixed $offset Either int index in inner array or name of action.
     * @return bool
     * @since 1.0.0
     */
    public function offsetExists( $offset ) : bool {
        if( is_numeric( $offset ) ) {
            return array_key_exists( $offset, $this->params );
        }

        for( $i = 0; $i < count( $this->params ); $i++ ) {
            if( $this->params[$i]->get_name() == $offset ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @internal Part of {@see \ArrayAccess} implementation.
     * @param mixed $offset Either int index in inner array or name of action.
     * @return Sendlane_Api_Call_Param
     * @since 1.0.0
     */
    public function offsetGet( $offset ) : Sendlane_Api_Call_Param {
        if( is_numeric( $offset ) ) {
            return $this->params[$offset];
        }

        for( $i = 0; $i < count( $this->params ); $i++ ) {
            if( $this->params[$i]->get_name() == $offset ) {
                return $this->params[$i];
            }
        }

        return null;
    }

    /**
     * @internal Part of {@see \ArrayAccess} implementation.
     * @param mixed $offset Either int index in inner array or name of action.
     * @param Sendlane_Api_Call $value
     * @return void
     * @since 1.0.0
     */
    public function offsetSet( $offset, $value ) {
        if( is_numeric( $offset ) ) {
            $this->params[$offset] = $value; 
        }

        $cnt = count( $this->params );
        $is_new = true;

        for( $i = 0; $i < $cnt; $i++ ) {
            if( $this->params[$i]->get_name() == $offset ) {
                $this->params[$i] = $value;
                $is_new = false;
            }
        }

        if( $is_new ) {
            $this->params[$cnt] = $value;
        }
    }

    /**
     * @internal Part of {@see \ArrayAccess} implementation.
     * @param mixed $offset Either int index in inner array or name of action.
     * @return void
     * @since 1.0.0
     */
    public function offsetUnset( $offset ) {
        if( is_numeric( $offset ) ) {
            unset( $this->params[$offset] );
        }

        for( $i = 0; $i < count( $this->params ); $i++ ) {
            if( $this->params[$i]->get_name() == $offset ) {
                unset( $this->params[$i] );
                return;
            }
        }
    }

    /**
     * @internal Part of {@see \Iterator} implementation.
     * @return Sendlane_Api_Call_Param
     * @since 1.0.0
     */
    public function current() : Sendlane_Api_Call_Param {
        return $this->params[$this->index];
    }

    /**
     * @internal Part of {@see \Iterator} implementation.
     * @return int
     * @since 1.0.0
     */
    public function key() {
        return $this->index;
    }

    /**
     * @internal Part of {@see \Iterator} implementation.
     * @return void
     * @since 1.0.0
     */
    public function next() {
        ++$this->index;
    }

    /**
     * @internal Part of {@see \Iterator} implementation.
     * @return void
     * @since 1.0.0
     */
    public function rewind() {
        $this->index = 0;
    }

    /**
     * @internal Part of {@see \Iterator} implementation.
     * @return bool
     * @since 1.0.0
     */
    public function valid() : bool {
        return isset( $this->params[$this->index] );
    }

    /**
     * Returns the parameters as a JSON string.
     * @return string
     * @since 1.0.0
     */
    public function to_json() : string {
        $json = '[';

        foreach( $this->params as /*Sendlane_Api_Call_Param*/$param ) {
            $json .= ( strlen( $json ) > 1 ? ',' : '' ) . $param->to_json();
        }

        return $json . ']';
    }
}

endif;