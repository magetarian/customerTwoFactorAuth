/*
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
    'Magetarian_CustomerTwoFactorAuth/js/model/duo/api',
    'Magetarian_CustomerTwoFactorAuth/js/view/provider/default',
    'domReady!'
], function (
    $,
    duo,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magetarian_CustomerTwoFactorAuth/provider/duo',
            loginFormUrlKey: null,
            postAction: null,
            postArgument: 'tfa_code'
        },

        /** @inheritdoc */
        // eslint-disable-next-line no-unused-vars
        activate: function (data) {
            duo.init(
                {
                    iframe: this.getIframeId(),
                    host: this.getApiHost(),
                    sig_request: this.getSignature(),
                    post_action: this.postAction,
                    post_argument: this.postArgument,
                    submit_callback: this.verifiedCallback
                }
            );
            this._super();
        },

        /**
         * @return {String}
         */
        getIframeId: function () {
            return this.parentName.toLowerCase().replace(/[^a-z]/g,'');
        },

        /**
         * @return {String}
         */
        getApiHost: function () {
            return this.additionalConfig.apiHost;
        },

        /**
         * @return {String}
         */
        getSignature: function () {
            return this.additionalConfig.signature;
        },

        /**
         * Callback once 2FA completed
         * @private
         */
        verifiedCallback: function (duoForm) {
            let loginForm = $(duoForm).parent().closest('form');

            $(duoForm).find('input').each(function () {
                $(duoForm).parent().append(this);
                $(duoForm).remove();
            });
            loginForm.submit();
        }

    });
});
