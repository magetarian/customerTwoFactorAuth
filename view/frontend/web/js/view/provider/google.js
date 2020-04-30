/*
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
    'mage/url',
    'uiComponent'
], function (
    $,
    urlBuilder,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magetarian_CustomerTwoFactorAuth/provider/google',
            providerLabel: null,
            providerCode: null,
            configured: false,
            additinalConfig: {},
            qrCodeUrlKey: 'twofactorauth/google/qr',
            isActive: false
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

        //@todo make it observable that when selected other button were hidden
        activate: function (data) {
            console.log(data);
            console.log(this.getCode());
            this.isActive(true);
        },

        getCode: function () {
           return this.code;
        },

        getName: function () {
            return this.label
        },

        isConfigured: function () {
            return this.configured();
        },

        getQrCodeUrl: function () {
            return urlBuilder.build(this.qrCodeUrlKey);
        },

        getSecretCode: function () {
            return this.additionalConfig.secretCode;
        }

    });
});
