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


if( ! class_exists( 'Sendlane_Api_Calls' ) ) :

/**
 * Simple class used for describing Sendlane API calls.
 * @link http://help.sendlane.com/knowledgebase/api-docs/
 * @since 1.0.0
 */
class Sendlane_Api_Calls implements ArrayAccess {
    /**
     * @var array $calls
     * @since 1.0.0
     */
    protected $calls = [];

    /**
     * Constructor.
     * @return void
     * @since 1.0.0
     */
    public function __construct() {
        $this->calls['user-details'] = new Sendlane_Api_Call(
            'user-details',
            __( 'To get api and hash key.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            [
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Email ID (user account email). Valid email name@domain.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'password', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Password.', 'odwp-sendlane_plugin' ) ),
            ]
        );
        $this->calls['list-subscribers-add'] = new Sendlane_Api_Call(
            'list-subscribers-add',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_ids', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_names', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                // custom field 1
                // custom field 2
            ] )
            
        );
        $this->calls['list-subscriber-add'] = new Sendlane_Api_Call(
            'list-subscriber-add',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'first_name', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'last_name', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( '…', 'odwp-sendlane_plugin' ) ),
                // custom field 1
                // custom field 2
                new Sendlane_Api_Call_Param( 'tag_ids', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_names', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
            ])
        );
        $this->calls['subscribers-delete'] = new Sendlane_Api_Call(
            'subscribers-delete',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
            ] )
        );
        $this->calls['list-create'] = new Sendlane_Api_Call(
            'list-create',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'list_name', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'from_name', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'reply_email', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'default_reply_email', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'subject', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'short_reminder', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'company', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'address', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'city', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'zipcode', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'country', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'state', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'phone', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
            ] )
        );
        $this->calls['list-update'] = new Sendlane_Api_Call(
            'list-update',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'from_name', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'reply_email', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'default_reply_email', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'subject', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'short_reminder', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'company', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'address', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'city', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'zipcode', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'country', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'state', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'phone', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
            ] )
        );
        $this->calls['list-delete'] = new Sendlane_Api_Call(
            'list-delete',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( '…', 'odwp-sendlane_plugin' ) ),
            ] )
        );
        $this->calls['lists'] = new Sendlane_Api_Call(
            'lists',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'start', Sendlane_Api_Call_Param::TYPE_INT, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'limit', Sendlane_Api_Call_Param::TYPE_INT, false, __( '…', 'odwp-sendlane_plugin' ) ),
            ] )
        );
        $this->calls['opt-in-form'] = new Sendlane_Api_Call(
            'opt-in-form',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'form_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( '…', 'odwp-sendlane_plugin' ) ),
            ] )
        );
        $this->calls['opt-in-create'] = new Sendlane_Api_Call(
            'opt-in-create',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'form_name', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'first_name', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'last_name', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'send_opt_mail', Sendlane_Api_Call_Param::TYPE_INT, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'redirect_url', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
            ] )
        );
        $this->calls['subscriber-export'] = new Sendlane_Api_Call(
            'subscriber-export',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'start', Sendlane_Api_Call_Param::TYPE_INT, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'limit', Sendlane_Api_Call_Param::TYPE_INT, false, __( '…', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'format', Sendlane_Api_Call_Param::TYPE_STR, false, __( '…', 'odwp-sendlane_plugin' ) ),
            ] )
        );
        $this->calls['tags'] = new Sendlane_Api_Call(
            'tags',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'tag_id', Sendlane_Api_Call_Param::TYPE_INT, false, __( 'Tag id (<a href="http://help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">Valid tag_id #</a>)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'start', Sendlane_Api_Call_Param::TYPE_INT, false, __( 'Starting Value of the Result (By default : 1)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'limit', Sendlane_Api_Call_Param::TYPE_INT, false, __( 'Result Limit Per Result (By default : 10)', 'odwp-sendlane_plugin' ) ),
            ] )
        );
        $this->calls['tag-create'] = new Sendlane_Api_Call(
            'tag-create',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'name', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Tag name', 'odwp-sendlane_plugin' ) ),
            ] )
        );
        $this->calls['tag-subscriber-add'] = new Sendlane_Api_Call(
            'tag-subscriber-add',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Subscriber email (Valid email name@domain)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_ids', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Tag_id’s separated by comma. Eg. <a href="//help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">tagId1, tagId2,...</a>. Required if <code>tag_names</code> not provided. <a href="//help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">Valid tag_id #</a>.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_names', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Tag name’s separated by comma. Eg. <a href="//help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">Test Tag1,Test Tag2,....</a>. Required if <code>tag_ids</code> not provided.', 'odwp-sendlane_plugin' ) ),
            ] )
        );
        $this->calls['tag-subscriber-remove'] = new Sendlane_Api_Call(
            'tag-subscriber-remove',
            __( '…', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( [
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Subscriber email (Valid email name@domain)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_ids', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Tag_id’s separated by comma. Eg. <a href="//help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">tagId1, tagId2,...</a>. Required if <code>tag_names</code> not provided. <a href="//help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">Valid tag_id #</a>.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_names', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Tag name’s separated by comma. Eg. <a href="//help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">Test Tag1,Test Tag2,....</a>. Required if <code>tag_ids</code> not provided.', 'odwp-sendlane_plugin' ) ),
            ] )
        );
    }

    /**
     * @internal Used for getting default parameters for API calls (or to add some parameters to the defaults).
     * @param array $params
     * @return array
     */
    private function params( array $params = [] ) : array {
        $defaults = [
            new Sendlane_Api_Call_Param( 'api', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Your API key provided', 'odwp-sendlane_plugin' ) ),
            new Sendlane_Api_Call_Param( 'hash', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Your HASH key provided', 'odwp-sendlane_plugin' ) ),
        ];

        return array_merge( $defaults, $params );
    }

    /**
     * @internal Part of {@see ArrayAccess} implementation.
     * @param string $offset
     * @return bool
     * @see ArrayAccess::offsetExists()
     * @since 1.0.0
     */
    public function offsetExists( $offset ) : bool {
        return array_key_exists( $offset, $this->calls );
    }

    /**
     * @internal Part of {@see ArrayAccess} implementation.
     * @param string $offset
     * @return Sendlane_Api_Call
     * @see ArrayAccess::offsetGet()
     * @since 1.0.0
     */
    public function offsetGet( $offset ) : Sendlane_Api_Call {
        return $this->calls[$offset];
    }

    /**
     * @internal Part of {@see ArrayAccess} implementation.
     * @param string $offset
     * @param Sendlane_Api_Call $value
     * @return void
     * @see ArrayAccess::offsetSet()
     * @since 1.0.0
     */
    public function offsetSet( $offset, $value ) {
        $this->calls[$offset] = $value; 
    }

    /**
     * @internal Part of {@see ArrayAccess} implementation.
     * @param string $offset
     * @return void
     * @see ArrayAccess::offsetUnset()
     * @since 1.0.0
     */
    public function offsetUnset( $offset ) {
        unset( $this->calls[$offset] );
    }
}

endif;
