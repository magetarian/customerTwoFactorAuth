/*
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
    'mage/url',
    'ko',
    'Magetarian_CustomerTwoFactorAuth/js/action/authy/register',
    'Magetarian_CustomerTwoFactorAuth/js/view/provider/default'
], function (
    $,
    urlBuilder,
    ko,
    registerAction,
    Component
) {
    'use strict';

    return Component.extend({
        country: ko.observable(''),
        phone: ko.observable(''),
        method: ko.observable(''),
        verifyCode: ko.observable(''),
        currentStep: ko.observable(''),

        defaults: {
            template: 'Magetarian_CustomerTwoFactorAuth/provider/authy',
            phoneSelector: '#tfa_authy_phone'
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            if (!this.isConfigured()) {
                this.currentStep('register');
            } else {
                this.currentStep('authentication');
            }
        },

        /**
         * @return {Array}
         */
        getCountries: function () {
            return this.additionalConfig.countryList;
        },

        doRegister: function (element) {

           console.log('asd');
           console.log(element);
            var loginData = {};
            $('body').trigger('processStart');
            registerAction(loginData).always(function () {
                $('body').trigger('processStop');
            });
        },

        /**
         * @return {Boolean}
         */
        allowRegisterAction: function () {
            return this.phone().length>1 && !/[^\d]/.test(this.phone()) ? true : false;
        },

    });
});
