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
    'Magetarian_CustomerTwoFactorAuth/js/action/authy/verify',
    'Magento_Ui/js/model/messageList',
    'Magetarian_CustomerTwoFactorAuth/js/view/provider/default'
], function (
    $,
    urlBuilder,
    ko,
    registerAction,
    verifyAction,
    messageList,
    Component
) {
    'use strict';

    return Component.extend({
        country: ko.observable(''),
        phone: ko.observable(''),
        method: ko.observable(''),
        currentStep: ko.observable(''),
        secondsToExpire: ko.observable(''),
        timer: null,
        authButton: null,

        defaults: {
            template: 'Magetarian_CustomerTwoFactorAuth/provider/authy',
            tfaCodeFieldSelector: '#tfa_code'
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            let self = this;

            if (!this.isConfigured()) {
                this.currentStep('register');
            } else {
                this.currentStep('authentication');
            }
            registerAction.registerAuthyRegisterCallback(function(registerData, response) {
                self.currentStep('verify');
                self.setTimer(response.data.secondsToExpire);
            });
            verifyAction.registerAuthyVerifyCallback(function(verifyData, response) {
                if (verifyData.method !== 'onetouch') {
                    self.currentStep('verify');
                } else {
                    self.validateOneTouch(response.data.oneTouchCode, response.data.oneTouchStatus);
                }
            });
            return this;
        },

        /** @inheritdoc */
        isConfigured: function () {
            return this.configured() && this.additionalConfig.phoneConfirmed;
        },

        /**
         * @param {String} code
         * @param {String} status
         */
        validateOneTouch: function (code, status) {
            if (status == 'approved') {
                $(this.authButton).closest("form").find(this.tfaCodeFieldSelector).val(code);
                $(this.authButton).hide();
                $(this.authButton).closest("form").submit();
            } else if (status == 'denied') {
                messageList.addErrorMessage({ message: 'The authentication request denied.' });
                $('body').trigger('processStop');
            } else if (status == 'pending') {
                let verifyData = this.collectFormData(this.authButton);
                verifyData['method'] = this.method();
                verifyData['code'] = code;
                $('body').trigger('processStart');
                verifyAction(verifyData).always(function () {
                    $('body').trigger('processStop');
                });
            } else {
                messageList.addErrorMessage({ message: 'The authentication status '+status });
                $('body').trigger('processStop');
            }
        },

        /**
         * @param {Number} sec
         */
        setTimer: function (sec) {
            let self = this;

            if (!this.timer) {
                this.secondsToExpire(sec);
                this.timer = setInterval(function() {
                    var newSecondsToExpire = self.secondsToExpire() - 1;

                    self.secondsToExpire(newSecondsToExpire <= 0 ? clearInterval(self.timer) : newSecondsToExpire);
                }, 1000);
            }
        },

        /**
         * @return {Array}
         */
        getCountries: function () {
            return this.additionalConfig.countryList;
        },

        /**
         * @param {Object} element
         * @return {Object}
         */
        collectFormData: function (element) {
            let formData = {},
                formDataArray = $(element).closest("form").serializeArray();

            formDataArray.forEach(function (entry) {
                let regexMatches = entry.name.match(/\[(.*?)\]/);

                if (regexMatches && regexMatches.length>1) {
                    formData[regexMatches[1]] = entry.value;
                } else {
                    formData[entry.name] = entry.value;
                }
            });
            return formData;
        },

        /**
         * @param {Object} element
         */
        doRegister: function (element) {
            let registerData = this.collectFormData(element);

            registerData['country'] = this.country();
            registerData['phone'] = this.phone();
            registerData['method'] = this.method();
            $('body').trigger('processStart');
            registerAction(registerData).always(function () {
                $('body').trigger('processStop');
            });
        },

        /**
         * @param {Object} element
         * @param {String} method
         * @return {Object}
         */
        AuthClick: function (element, method) {
            this.method(method);
            this.authButton = element;
            if (this.method() === 'token') {
                this.currentStep('verify');
                return this;
            }
            let verifyData = this.collectFormData(element);

            verifyData['method'] = this.method();
            $('body').trigger('processStart');
            verifyAction(verifyData).always(function () {
                $('body').trigger('processStop');
            });
        },

        /**
         * @return {Boolean}
         */
        allowRegisterAction: function () {
            return this.phone().length>1 && !/[^\d]/.test(this.phone()) ? true : false;
        }

    });
});
