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
            host: null,
            sigRequest: null,
            postAction: null,
            postArgument: 'tfa_code'
        },

        /**
         * Initialize
         * @private
         */
        _create: function () {
            duo.init(
                {
                    host: this.options.host,
                    sig_request: this.options.sigRequest,
                    post_action: this.options.postAction,
                    post_argument: this.options.postArgument,
                    submit_callback: this._verifiedCallback
                }
            );
        },

        /**
         * Callback once 2FA completed
         * @private
         */
        _verifiedCallback: function (duoForm) {
            let loginForm = $(duoForm).parent().closest('form');
            console.log($(duoForm).closest('form'));
            $(duoForm).find('input').each(function () {
                $(duoForm).parent().append(this);
                $(duoForm).remove();
            });
            loginForm.submit();
        }
    });

    return $.mage.twoFactorAuthProviderDuo;
});
