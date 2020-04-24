/*
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
    'Magetarian_CustomerTwoFactorAuth/js/duo/api',
    'domReady!',
    'jquery-ui-modules/widget'
], function ($, duo) {
    'use strict';

    $.widget('mage.twoFactorAuthProviderDuo', {
        options: {
        },

        /**
         * Initialize
         * @private
         */
        _create: function () {
            window.setTimeout(function () {
                duo.init();
                console.log('duoinit');
            }, 100);
        }
    });

    return $.mage.twoFactorAuthProviderDuo;
});
