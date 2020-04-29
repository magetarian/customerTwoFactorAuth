/*
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
    'Magento_Customer/js/action/login',
    'Magetarian_CustomerTwoFactorAuth/js/action/providers',
    'Magento_Checkout/js/model/full-screen-loader',
    'mageUtils',
    'uiLayout',
    'uiComponent'
], function (
    $,
    loginAction,
    providersAction,
    fullScreenLoader,
    utils,
    layout,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magetarian_CustomerTwoFactorAuth/checkout-tfa',
            isVisible: false
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().
            observe(['isVisible']);

            return this;
        },

        initialize: function () {
            this._super();
            let self = this;
            loginAction.registerLoginCallback(function(loginData) {

               //@todo check if doesnt have 2fa field
               self.getProvidersList(loginData);
            });

            providersAction.registerProvidersCallback(function(providersData, response) {
                self.renderProviders(providersData, response);
            });
        },

        getProvidersList: function (loginData) {
            let self = this;
            fullScreenLoader.startLoader();

            providersAction(loginData).always(function () {
                fullScreenLoader.stopLoader();
                self.isVisible(true);
            });
        },

        renderProviders: function (providersData, response) {
            let self = this;
            console.log(providersData);
            console.log(response);
            if (response !== null ) {
                $.each(response.providers, function(key, providerConfig) {
                    console.log(providerConfig.code);
                    if (providerConfig.code =='google') {
                        layout([self.createComponent(
                            {
                                config: providerConfig,
                                component: 'Magetarian_CustomerTwoFactorAuth/js/view/provider/'+providerConfig.code,
                                name: providerConfig.code,
                                method: {},
                                item: {},
                                displayArea: 'provider'
                            }
                        )]);
                    }

                });
            }
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
                name: provider.name
            };
            rendererTemplate = {
                parent: '${ $.$data.parentName }',
                name: '${ $.$data.name }',
                displayArea: provider.displayArea,
                component: provider.component
            };
            rendererComponent = utils.template(rendererTemplate, templateData);
            utils.extend(rendererComponent, {
                item: provider.item,
                config: provider.config
            });

            return rendererComponent;
        }

    });
});
