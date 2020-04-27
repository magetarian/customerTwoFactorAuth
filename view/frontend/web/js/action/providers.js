/*
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function ($, storage, globalMessageList, $t) {
    'use strict';

    var callbacks = [],

        /**
         * @param {Object} providersData
         * @param {String} redirectUrl
         * @param {*} isGlobal
         * @param {Object} messageContainer
         */
        action = function (providersData, redirectUrl, isGlobal, messageContainer) {
            messageContainer = messageContainer || globalMessageList;
            providersData['form_key'] = $.mage.cookies.get('form_key');

            return storage.post(
                'twofactorauth/customer/providers',
                JSON.stringify(providersData),
                isGlobal
            ).done(function (response) {
                if (response.errors) {
                    messageContainer.addErrorMessage(response);
                    callbacks.forEach(function (callback) {
                        callback(providersData);
                    });
                } else {
                    callbacks.forEach(function (callback) {
                        callback(providersData);
                    });
                }
            }).fail(function () {
                messageContainer.addErrorMessage({
                    'message': $t('Could not get list of providers. Please try again later')
                });
                callbacks.forEach(function (callback) {
                    callback(providersData);
                });
            });
        };

    /**
     * @param {Function} callback
     */
    action.registerProvidersCallback = function (callback) {
        callbacks.push(callback);
    };

    return action;
});
