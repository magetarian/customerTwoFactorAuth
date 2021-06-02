/*
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'mage/utils/wrapper'
], function ($, storage, globalMessageList, $t, wrapper) {
    'use strict';

    return function (loginAction) {
        const providersCallback = [];
        const loginCallback = [];

        /**
         * @param {Function[]} callbacks
         * @param {Object} loginData
         * @param {Object|undefined} response
         */
        const invokeCallbacks = function (callbacks, loginData, response) {
            callbacks.forEach(function (callback) {
                callback(loginData, response);
            });
        };

        let wrappedFunction = wrapper.wrap(loginAction, function (
            originalAction,
            loginData,
            redirectUrl,
            isGlobal,
            messageContainer
        ) {
            messageContainer = messageContainer || globalMessageList;
            loginData['form_key'] = $.mage.cookies.get('form_key');

            if (typeof loginData.tfa_code !== 'undefined') {
                return originalAction(loginData, redirectUrl, isGlobal, messageContainer);
            }

            return storage.post(
                'tfa/customer/providers',
                JSON.stringify(loginData),
                isGlobal
            ).done(function (response) {
                if (response.errors) {
                    messageContainer.addErrorMessage(response);
                    invokeCallbacks(loginCallback, loginData, response);
                } else if (Object.keys(response.providers).length > 0) {
                    invokeCallbacks(providersCallback, loginData, response);
                } else {
                    return originalAction(loginData, redirectUrl, isGlobal, messageContainer);
                }
            }).fail(function () {
                messageContainer.addErrorMessage({
                    'message': $t('Could not get list of providers. Please try again later')
                });
                invokeCallbacks(loginCallback, loginData, undefined);
            });
        });

        /**
         * @param {Function} callback
         */
        wrappedFunction.registerLoginCallback = function (callback) {
            /*
             * Store the login callbacks so that they can be invoked later in cases
             * where the original Action is not called
             */
            loginCallback.push(callback);
            loginAction.registerLoginCallback(callback);
        };

        /**
         * @param {Function} callback
         */
        wrappedFunction.registerProvidersCallback = function (callback) {
            providersCallback.push(callback);
        };

        return wrappedFunction;
    };
});
