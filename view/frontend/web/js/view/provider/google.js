/*
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'mage/url',
    'Magetarian_CustomerTwoFactorAuth/js/view/provider/default'
], function (
    urlBuilder,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magetarian_CustomerTwoFactorAuth/provider/google',
            qrCodeUrlKey: 'twofactorauth/google/qr'
        },

        /**
         * @return {String}
         */
        getQrCodeUrl: function () {
            return urlBuilder.build(this.qrCodeUrlKey);
        },

        /**
         * @return {String}
         */
        getSecretCode: function () {
            return this.additionalConfig.secretCode;
        }

    });
});
