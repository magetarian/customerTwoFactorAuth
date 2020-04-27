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
    'uiComponent'
], function (
    $,
    loginAction,
    providersAction,
    fullScreenLoader,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {

        },

        initialize: function () {
            this._super();
            let self = this;
            loginAction.registerLoginCallback(function(loginData) {

               //@todo check if doesnt have 2fa field
               // @todo make request for providers
               // show providers

               self.getProvidersList(loginData);
            });
        },

        getProvidersList: function (loginData) {
            fullScreenLoader.startLoader();
            providersAction(loginData).always(function () {
                fullScreenLoader.stopLoader();
            });
            console.log('asd');
        }
    });
});
