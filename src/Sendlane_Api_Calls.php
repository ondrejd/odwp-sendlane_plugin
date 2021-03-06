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
class Sendlane_Api_Calls implements \ArrayAccess, \Iterator {

    /**
     * @internal Holds array of all API calls (instances of {@see Sendlane_Api_Call}.
     * @var array $calls
     * @since 1.0.0
     */
    protected $calls = [];

    /**
     * @internal Part of {@see \Iterator} implementation.
     * @var int $index
     * @since 1.0.0
     */
    protected $index = 0;

    /**
     * Constructor.
     * @return void
     * @since 1.0.0
     * @todo Reload all available Sendlane API actions from XML file `sendlane-api.xml`.
     */
    public function __construct() {
        $this->calls[] = new Sendlane_Api_Call(
            'user-details',
            __( 'To get api and hash key.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Email ID (user account email). Valid email <code>name@domain</code>.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'password', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'User\'s password.', 'odwp-sendlane_plugin' ) )
            )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'list-subscribers-add',
            __( 'To add Multiple subscribers with single list with custom fields.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Email format should be like the following and separated by comma firstname lastname<test@gmail.com>, firstname lastname<test1@gmail.com> or firstname<test@gmail.com>, firstname<test1@gmail.com> or <test@gmail.com>, <test1@gmail.com> or test@gmail.com, test1@gmail.com', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( "Zadejte <a href='http://help.sendlane.com/knowledgebase/api-docs/#list-id' target='_blank'>platné <code>list id</code></a>.", 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_ids', Sendlane_Api_Call_Param::TYPE_STR, false, __( "Tag_id's separated by comma. Eg. <code>tagId1</code>, <code>tagId2</code>,… (<a href='http://help.sendlane.com/knowledgebase/api-docs/#tag-id' target='_blank'>nápověda</a>)", 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_names', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Tag name\'s separated by comma. Eg. <code>Test Tag1</code>, <code>Test Tag2</code>,…<br><b>If give both <code>tag_ids</code> and <code>tag_names</code> then will not consider <code>tag_names</code>.</b>', 'odwp-sendlane_plugin' ) )
                // XXX Custom fields...
            ) )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'list-subscriber-add',
            __( 'To add a Single subscriber to a list with custom fields.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'first_name', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Křestní jméno kontaktu.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'last_name', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Příjmení kontaktu.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Platný email.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( 'Platný <a href="http://help.sendlane.com/knowledgebase/api-docs/#list-id" target="_blank">list_id</a>', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_ids', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Tag_id\'s separated by comma. Eg. <code>tagId1</code>, <code>tagId2</code>,… (<a href="http://help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">nápověda</a>)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_names', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Tag name\'s separated by comma. Eg. <code>Test Tag1</code>, <code>Test Tag2</code>,…<br><b>If give both <code>tag_ids</code> and <code>tag_names</code> then will not consider <code>tag_names</code>.</b>', 'odwp-sendlane_plugin' ) )
                // XXX Custom fields...
            ) )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'subscribers-delete',
            __( 'To delete subscriber from given lists.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( 'list_id’s separated by comma, where the subscriber email belongs to multiple list <code>listId_1</code>, <code>listId_2</code>,… (<a href="http://help.sendlane.com/knowledgebase/api-docs/#list-id" target="_blank">Nápověda</a>)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Platný email.', 'odwp-sendlane_plugin' ) )
            ) )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'list-create',
            __( 'To create list. Please reference this <a href="http://help.sendlane.com/knowledgebase/create-a-list/" target="_blank">guide</a> for more information on the requirements for creating a List.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'list_name', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'List name will be unique and it will be not allow duplicate list names.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'from_name', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'From name may not be greater than 100 characters and it will not allow regular expressions.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'reply_email', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Reply email Yahoo/AOL reject emails that claim to come from yahoo/AOL but actually originate at non-Yahoo/AOL servers Valid email <code>name@domain</code> (<a href="http://blog.sendlane.com/important-announcement/" target="_blank">custom email domain required</a>)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'default_reply_email', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Default Reply email Yahoo/AOL reject emails that claim to come from yahoo/AOL but actually originate at non-Yahoo/AOL servers. Valid email <code>name@domain</code> (<a href="http://blog.sendlane.com/important-announcement/" target="_blank">custom email domain required</a>)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'subject', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Enter the subject.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'short_reminder', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Enter the description.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'company', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Enter the company name.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'address', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Enter the address.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'city', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Enter the city.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'zipcode', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Enter the zipcode.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'country', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Enter the country.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'state', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Enter the state.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'phone', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Enter phone number Valid phone number – <code>9 digits no space,( ), -, +</code>', 'odwp-sendlane_plugin' ) )
            ) )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'list-update',
            __( 'To update list.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params(new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( 'Platné <a href="http://help.sendlane.com/knowledgebase/api-docs/#list-id" target="_blank"><code>list id</code></a>.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'from_name', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'From name may not be greater than 100 characters and it will not allow regular expressions.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'reply_email', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Reply email Yahoo/AOL reject emails that claim to come from yahoo/AOL but actually originate at non-Yahoo/AOL servers Valid email <code>name@domain</code> (<a href="http://blog.sendlane.com/important-announcement/" target="_blank">custom email domain required</a>)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'default_reply_email', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Default Reply email Yahoo/AOL reject emails that claim to come from yahoo/AOL but actually originate at non-Yahoo/AOL servers. Valid email <code>name@domain</code> (<a href="http://blog.sendlane.com/important-announcement/" target="_blank">custom email domain required</a>)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'subject', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Enter the subject.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'short_reminder', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Enter the description.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'company', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Enter the company name.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'address', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Enter the address.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'city', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Enter the city.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'zipcode', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Enter the zipcode.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'country', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Enter the country.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'state', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Enter the state.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'phone', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Enter phone number Valid phone number – <code>9 digits no space,( ), -, +</code>', 'odwp-sendlane_plugin' ) )
            ) )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'list-delete',
            __( 'To delete list.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( 'Zadejte <a href="http://help.sendlane.com/knowledgebase/api-docs/#list-id" target="_blank">platné <code>list id</code></a>.', 'odwp-sendlane_plugin' ) )
            ) )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'lists',
            __( 'To get lists.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( 'Zadejte <a href="http://help.sendlane.com/knowledgebase/api-docs/#list-id" target="_blank">platné <code>list id</code></a>.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'start', Sendlane_Api_Call_Param::TYPE_INT, false, __( 'Page number to start retrieving Lists from.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'limit', Sendlane_Api_Call_Param::TYPE_INT, false, __( 'Results per page returned (max:100).', 'odwp-sendlane_plugin' ) )
            ) )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'opt-in-form',
            __( 'To fetch opt in form.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'form_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( 'Platné <code>form_id</code>', 'odwp-sendlane_plugin' ) )
            ) )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'opt-in-create',
            __( 'To create opt in form.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'String should be like <code>1,2,3</code>', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'form_name', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Enter the form name.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'first_name', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Enter the first name caption.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'last_name', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Enter the last name caption.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Enter the email caption.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'send_opt_mail', Sendlane_Api_Call_Param::TYPE_INT, false, __( 'To send confirmation mail; <code>1-Yes,0-No</code>.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'redirect_url', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'To redirect thank you page.', 'odwp-sendlane_plugin' ) )
            ) )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'subscriber-export',
            __( 'To export Subscriber example.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'list_id', Sendlane_Api_Call_Param::TYPE_INT, true, __( 'Platné <a href="http://help.sendlane.com/knowledgebase/api-docs/#list-id" target="_blank"><code>list id</code></a>.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'start', Sendlane_Api_Call_Param::TYPE_INT, false, __( 'Starting Value of the Result (By default: <code>1</code>).', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'limit', Sendlane_Api_Call_Param::TYPE_INT, false, __( 'Result Limit Per Result (By default: <code>10</code>).', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'format', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Supported format <code>CSV</code>, <code>text</code> (By default: <code>CSV</code>)', 'odwp-sendlane_plugin' ) )
            ) )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'tags',
            __( 'To get the list of tags.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'tag_id', Sendlane_Api_Call_Param::TYPE_INT, false, __( 'Tag id (<a href="http://help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">Valid tag_id #</a>)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'start', Sendlane_Api_Call_Param::TYPE_INT, false, __( 'Starting Value of the Result (By default : <code>1</code>)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'limit', Sendlane_Api_Call_Param::TYPE_INT, false, __( 'Result Limit Per Result (By default : <code>10</code>)', 'odwp-sendlane_plugin' ) )
            ) )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'tag-create',
            __( 'To create a new Tag.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'name', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Tag name.', 'odwp-sendlane_plugin' ) )
            ) )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'tag-subscriber-add',
            __( 'To add a tag to a single existing subscriber.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Subscriber email (Valid email name@domain)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_ids', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Tag_id’s separated by comma. Eg. <a href="//help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">tagId1, tagId2,...</a>. Required if <code>tag_names</code> not provided. <a href="//help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">Valid tag_id #</a>.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_names', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Tag name’s separated by comma. Eg. <a href="//help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">Test Tag1,Test Tag2,....</a>. Required if <code>tag_ids</code> not provided.', 'odwp-sendlane_plugin' ) )
            ) )
        );
        $this->calls[] = new Sendlane_Api_Call(
            'tag-subscriber-remove',
            __( 'To remove a single existing subscriber\'s tag.', 'odwp-sendlane_plugin' ),
            Sendlane_Api_Call::TYPE_POST,
            $this->params( new Sendlane_Api_Call_Params(
                new Sendlane_Api_Call_Param( 'email', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Subscriber email (Valid email name@domain)', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_ids', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Tag_id’s separated by comma. Eg. <a href="//help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">tagId1, tagId2,...</a>. Required if <code>tag_names</code> not provided. <a href="//help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">Valid tag_id #</a>.', 'odwp-sendlane_plugin' ) ),
                new Sendlane_Api_Call_Param( 'tag_names', Sendlane_Api_Call_Param::TYPE_STR, false, __( 'Tag name’s separated by comma. Eg. <a href="//help.sendlane.com/knowledgebase/api-docs/#tag-id" target="_blank">Test Tag1,Test Tag2,....</a>. Required if <code>tag_ids</code> not provided.', 'odwp-sendlane_plugin' ) )
            ) )
        );
    }

    /**
     * @internal Used for getting default parameters for API calls (or to add some parameters to the defaults).
     * @param Sendlane_Api_Call_Params $params
     * @return array
     */
    private function params( Sendlane_Api_Call_Params $params ) : Sendlane_Api_Call_Params {
        $defaults = new Sendlane_Api_Call_Params(
            new Sendlane_Api_Call_Param( 'api', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Your API key provided', 'odwp-sendlane_plugin' ) ),
            new Sendlane_Api_Call_Param( 'hash', Sendlane_Api_Call_Param::TYPE_STR, true, __( 'Your HASH key provided', 'odwp-sendlane_plugin' ) )
        );

        return $defaults->merge( $params );
    }

    /**
     * @internal Part of {@see \ArrayAccess} implementation.
     * @param mixed $offset Either int index in inner array or name of action.
     * @return bool
     * @since 1.0.0
     */
    public function offsetExists( $offset ) : bool {
        if( is_numeric( $offset ) ) {
            return array_key_exists( $offset, $this->calls );
        }

        for( $i = 0; $i < count( $this->calls ); $i++ ) {
            if( $this->calls[$i]->get_name() == $offset ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @internal Part of {@see \ArrayAccess} implementation.
     * @param mixed $offset Either int index in inner array or name of action.
     * @return Sendlane_Api_Call
     * @since 1.0.0
     */
    public function offsetGet( $offset ) : Sendlane_Api_Call {
        if( is_numeric( $offset ) ) {
            return $this->calls[$offset];
        }

        for( $i = 0; $i < count( $this->calls ); $i++ ) {
            if( $this->calls[$i]->get_name() == $offset ) {
                return $this->calls[$i];
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
            $this->calls[$offset] = $value; 
        }

        $cnt = count( $this->calls );
        $is_new = true;

        for( $i = 0; $i < $cnt; $i++ ) {
            if( $this->calls[$i]->get_name() == $offset ) {
                $this->calls[$i] = $value;
                $is_new = false;
            }
        }

        if( $is_new ) {
            $this->calls[$cnt] = $value;
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
            unset( $this->calls[$offset] );
        }

        for( $i = 0; $i < count( $this->calls ); $i++ ) {
            if( $this->calls[$i]->get_name() == $offset ) {
                unset( $this->calls[$i] );
                return;
            }
        }
    }

    /**
     * @internal Part of {@see \Iterator} implementation.
     * @return Sendlane_Api_Call
     * @since 1.0.0
     */
    public function current() : Sendlane_Api_Call {
        return $this->calls[$this->index];
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
        return isset( $this->calls[$this->index] );
    }
}

endif;
