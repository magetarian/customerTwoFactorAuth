/*
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
    'Magento_Customer/js/action/login',
    'Magetarian_CustomerTwoFactorAuth/js/view/checkout-tfa',
    'domReady!'
], function (
    $,
    loginAction,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            loginFormSelector: '#login-form',
            buttonSelector: 'button[type="submit"]'
        },
        loginButton: null,

        /** @inheritdoc */
        initialize: function () {
            this._super();
            let self = this;
            this.loginButton = $(this.loginFormSelector).find(this.buttonSelector);

            $(this.loginFormSelector).on('submit', function(e) {
                let isValid = $(self.loginFormSelector).valid();
                if (isValid) {
                    e.preventDefault();
                    $(self.loginButton).attr('disabled', true);

                    let loginData = {},
                        formDataArray = $(self.loginFormSelector).serializeArray();

                    formDataArray.forEach(function (entry) {
                        let regexMatches = entry.name.match(/\[(.*?)\]/);
                        if (regexMatches && regexMatches.length>1) {
                            loginData[regexMatches[1]] = entry.value;
                        } else {
                            loginData[entry.name] = entry.value;
                        }
                    });

                    $('body').trigger('processStart');
                    loginAction(loginData).always(function () {
                        $('body').trigger('processStop');
                    });
                }
            });
        }
    });
});
