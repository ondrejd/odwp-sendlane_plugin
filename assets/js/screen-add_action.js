/**
 * @author Ondrej Donek <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-sendlane_plugin for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-sendlane_plugin
 * @see SP_Add_Action_Screen::admin_enqueue_scripts()
 */

jQuery( document ).ready( function( $ ) {

    /**
     * @global Object odwpsp1
     * Object {
     *      ajax_url: String,
     *      form_title: String,
     *      defaults: Object {
     *          api_key: String,
     *          hash_key: String,
     *          domain: String,
     *      }
     * }
     */

    /**
     * Returns HTML with info for the Sendlane API action.
     * @param {Object} aAction
     * @returns {String}
     */
    var getActionInfo = function( aAction ) {
        var url = "http://help.sendlane.com/knowledgebase/api-docs/#" + aAction.name;
        var ret = "";
        ret += "<h2>" + odwpsp1.i18n.info_title + "</h2>";
        ret += "<p>" + odwpsp1.i18n.info_lbl1 + " <code>" + aAction.name + "</code><br>";
        ret += odwpsp1.i18n.info_lbl2 + " <em>" + aAction.description + "</em><br>";
        ret += odwpsp1.i18n.info_lbl3 + ' <a href="' + url + '" target="_blank">' + url + '</a></p>';
        return ret;
    };

    /**
     * Returns correct HTML input for the Sendlane API action's parameter.
     * @param {Object} aParam
     * @returns {String}
     */
    var getInputForActionParam = function( aParam, aIndex ) {
        var ret  = '';
        var name = 'action-param[' + aIndex + ']';
        var id   = 'odwpsp-action-param'  + aIndex;

        if( aParam.name == "api" ) {
            ret = '<input type="text" id="' + id + '" name="' + name + '" value="' + odwpsp1.defaults.api_key + '" class="regular-text" disabled>';
        }
        else if( aParam.name == "hash" ) {
            ret = '<input type="text" id="' + id + '" name="' + name + '" value="' + odwpsp1.defaults.hash_key + '" class="regular-text" disabled>';
        }
        else if( aParam.name == "list_id" ) {
            ret = odwpsp_create_list_id_select( id, name );
        }
        else if( aParam.name == "tag_id" ) {
            ret = odwpsp_create_tag_id_select( id, name );
        }
        else if( aParam.type == 0 ) {
            ret = '<input type="number" id="' + id + '" name="' + name + '" value="">';
        }
        else {
            ret = '<input type="text" id="'  + id + '" name="' + name + '" value="" class="regular-text">';
        }

        ret += '<p class="description">' + aParam.description + '</p>';

        return ret;
    };

    /**
     * Handles AJAX call "get_api_action".
     * @param {String} aResponse
     * @returns {void}
     */
    var handleGetActionAjaxCall = function( aResponse ) {
        var action = JSON.parse( aResponse );
        var title  = new String( odwpsp1.i18n.form_title ).replace( "%s", action.name );
        var html   = "" +
                getActionInfo( action ) +
                "<h2>" + title + "</h2>" +
                '<table class="form-table">' +
                    '<tbody id="odwpsp-action_parameters_tbody">' +
                        '%s' +
                    '</tbody>' +
                '</table>';
        var params  = "";

        for( var i = 0; i < action.parameters.length; i++ ) {
            var param = action.parameters[i];
            var input = getInputForActionParam( param, i );
            var label = new String( odwpsp1.i18n.label_title ).replace( "%s", param.name );
            params += "" +
                    '<tr>' +
                        '<th scope="row">' +
                            '<label for="odwpsp-action-param'  + i + '">' + label + '</label>' +
                        '</th>' +
                        '<td>' +
                            input +
                        '</td>' +
                    '</tr>';
        }

        var final_html = html.replace( "%s", params );
        jQuery( "#odwpsp-additional_action_settings" ).html( final_html );
    };

    /**
     * Handles "onChange" event for "odwps-action" selectbox.
     * @param {Object} aEvent
     * @returns {void}
     */
    var handleActionChange = function( aEvent ) {
        var action = jQuery( this ).val();

        if( action == "-1" ) {
            clearAdditionalParameters();
        }
        else {
            jQuery.post(
                    odwpsp1.ajax_url,
                    {
                        action: "get_api_action",
                        api_action: action
                    },
                    function( aResponse ) {
                        try {
                            handleGetActionAjaxCall( aResponse );
                        } catch( e ) {
                            clearAdditionalParameters();
                        }
                    },
                    "json"
            );
        }
    };

    /**
     * @returns {void} Clears additional parameters form.
     */
    var clearAdditionalParameters = function() {
        jQuery( "#odwpsp-additional_action_settings" ).html( "" );
    };

    jQuery( "#odwpsp-action" ).change( handleActionChange );
} );