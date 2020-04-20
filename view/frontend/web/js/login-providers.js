/*
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.twoFactorAuthLoginProviders', {
        /**
         * Initialize
         * @private
         */
        _create: function () {
            console.log('asd');
        }
    });

    return $.mage.twoFactorAuthLoginProviders;
});
