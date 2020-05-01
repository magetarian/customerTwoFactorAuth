/*
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
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
        let callbacks = [];

        let wrappedFunction =  wrapper.wrap(loginAction, function (
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
                'twofactorauth/customer/providers',
                JSON.stringify(loginData),
                isGlobal
            ).done(function (response) {
                if (response.errors) {
                    messageContainer.addErrorMessage(response);
                } else {
                    if (Object.keys(response.providers).length > 0) {
                        callbacks.forEach(function (callback) {
                            callback(loginData, response);
                        });
                    } else {
                        return originalAction(loginData, redirectUrl, isGlobal, messageContainer);
                    }
                }
            }).fail(function () {
                messageContainer.addErrorMessage({
                    'message': $t('Could not get list of providers. Please try again later')
                });
            });
        });

        wrappedFunction.registerLoginCallback = loginAction.registerLoginCallback;
        /**
         * @param {Function} callback
         */
        wrappedFunction.registerProvidersCallback = function (callback) {
            callbacks.push(callback);
        };

        return wrappedFunction;
    };
});
