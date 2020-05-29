/*
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
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
         * @param {Object} verifyData
         * @param {*} isGlobal
         * @param {Object} messageContainer
         */
        action = function (verifyData, isGlobal, messageContainer) {
            messageContainer = messageContainer || globalMessageList;
            verifyData['form_key'] = $.mage.cookies.get('form_key');

            return storage.post(
                'tfa/authy/verifypost',
                JSON.stringify(verifyData),
                isGlobal
            ).done(function (response) {
                if (response.errors) {
                    messageContainer.addErrorMessage(response);
                } else {
                    callbacks.forEach(function (callback) {
                        callback(verifyData, response);
                    });
                }
            }).fail(function () {
                messageContainer.addErrorMessage({
                    'message': $t('Could not authenticate using Authy. Please try again later')
                });
            });
        };

    /**
     * @param {Function} callback
     */
    action.registerAuthyVerifyCallback = function (callback) {
        callbacks.push(callback);
    };

    return action;
});
