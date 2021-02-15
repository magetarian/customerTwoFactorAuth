/*
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
    'Magento_Customer/js/action/login',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Customer/js/view/authentication-popup',
    'mageUtils',
    'uiLayout',
    'uiComponent'
], function (
    $,
    loginAction,
    fullScreenLoader,
    authenticationPopup,
    utils,
    layout,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magetarian_CustomerTwoFactorAuth/tfa-login',
            isVisible: false,
            loginFormSelector: 'form[data-role=email-with-possible-login]'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().
            observe(['isVisible']);
            return this;
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            let self = this;
            console.log(authenticationPopup);
            loginAction.registerProvidersCallback(function(providersData, response) {
                self.renderProviders(providersData, response);
            });
            return this;
        },

        /**
         * @param {object} providersData
         * @param {object} response
         */
        renderProviders: function (providersData, response) {
            let self = this;

            $(this.loginFormSelector).find('.actions-toolbar').hide();
            $.each(response.providers, function(key, providerConfig) {
                layout([self.createComponent(
                    {
                        config: providerConfig,
                        component: 'Magetarian_CustomerTwoFactorAuth/js/view/provider/'+providerConfig.code,
                        code: providerConfig.code,
                        displayArea: 'provider'
                    }
                )]);
            });
            authenticationPopup().isLoading(false);
            this.isVisible(true);
        },

        /**
         * @returns
         */
        createComponent: function (provider) {
            var rendererTemplate,
                rendererComponent,
                templateData;

            templateData = {
                parentName: this.name,
                name: provider.code
            };
            rendererTemplate = {
                parent: '${ $.$data.parentName }',
                name: '${ $.$data.name }',
                displayArea: provider.displayArea,
                component: provider.component
            };
            rendererComponent = utils.template(rendererTemplate, templateData);
            utils.extend(rendererComponent, {
                item: {},
                config: provider.config
            });

            return rendererComponent;
        }

    });
});
