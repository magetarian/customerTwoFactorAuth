/*
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
    'mage/url',
    'Magetarian_CustomerTwoFactorAuth/js/model/duo/api',
    'Magento_Customer/js/action/login',
    'uiComponent',
    'domReady!',
    'mage/validation'
], function (
    $,
    urlBuilder,
    duo,
    loginAction,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magetarian_CustomerTwoFactorAuth/provider/duo',
            providerLabel: null,
            providerCode: null,
            configured: false,
            additinalConfig: {},
            loginFormUrlKey: null,
            isActive: false,
            postAction: null,
            postArgument: 'tfa_code'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().
            observe(['isActive', 'configured']);
            return this;
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
        },

        activate: function (data) {
            duo.init(
                {
                    host: this.getApiHost(),
                    sig_request: this.getSignature(),
                    post_action: this.postAction,
                    post_argument: this.postArgument,
                    submit_callback: this.verifiedCallback
                }
            );
            this.isActive(true);
        },

        getCode: function () {
           return this.code;
        },

        getName: function () {
            return this.label
        },

        getApiHost: function () {
            return this.additionalConfig.apiHost;
        },

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
