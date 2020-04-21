/*
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
    'mage/url',
    'mage/template',
    'jquery-ui-modules/widget'
], function ($, urlBuilder, template) {
    'use strict';

    $.widget('mage.twoFactorAuthLoginProviders', {
        options: {
            buttonSelector: 'button[type="submit"]',
            templateSelector: '#provider-list-template',
            containerSelector: '#two-factor-auth-provider-list',
            providerListUrlKey: 'twofactorauth/customer/providers',
            providerAuthUrlKey: 'twofactorauth/{code}/authentication',
            twoFactorAuthButtonSelector: 'button[id^="login-using-"]',
            codeProperty: 'code'
        },
        loginButton: null,
        twoFactorAuthPassed: false,

        /**
         * Initialize
         * @private
         */
        _create: function () {
            this.loginButton = $(this.options.buttonSelector, this.element);
            this._bind();
        },

        _bind: function () {
            let self = this;

            this.element.on('submit', function(e) {
                var isvalid = self.element.valid();
                if (isvalid && !self.twoFactorAuthPassed) {
                    e.preventDefault();
                    self.loginButton.attr('disabled', true);
                    self._renderProviders();
                }
            });
        },

        _renderProviders: function () {
            let self = this;
            let providersUrl = urlBuilder.build(this.options.providerListUrlKey);

            $.ajax({
                url: providersUrl,
                data: self.element.serialize(),
                type: 'post',
                dataType: 'json',

                /** Show loader before send */
                beforeSend: function () {
                    $('body').trigger('processStart');
                }
            }).always(function () {
                $('body').trigger('processStop');
            }).done(function (response) {
                if (response.errors) {
                    self.loginButton.attr('disabled', false);
                } else {
                    if ($.isEmptyObject(response.providers)) {
                        self.twoFactorAuthPassed = true;
                        self.element.submit();
                    } else {
                        self._parseResponse(response);
                        self.loginButton.hide();
                    }
                }
            }).fail(function () {
                self.loginButton.attr('disabled', false);
            });
        },

        _parseResponse: function (response) {
            console.log(response);
            let self = this;
            let providerListTemplate = template(this.options.templateSelector);
            $(self.options.containerSelector).empty();
            $.each(response.providers, function(itemCode, itemLabel) {
                var provider = providerListTemplate({
                    data: {
                        name: itemLabel,
                        code: itemCode
                    }
                });
                $(self.options.containerSelector).append(provider);
            });
            this._bindButtons();
            this.loginButton.attr('disabled', false);
        },

        _bindButtons: function () {
            let self = this;
            let buttons = $(this.options.twoFactorAuthButtonSelector, this.element);
            $(buttons).each(function () {
                $(this).click(function () {
                    self._bindButtonClick($(this).data(self.options.codeProperty))
                });
            });
        },

        /**
         * @todo refactor into separate js classes
         * @param code
         * @private
         */
        _bindButtonClick: function (code) {
            let self = this;
            let providerPost = urlBuilder.build(this.options.providerAuthUrlKey.replace('{code}',code));
            $.ajax({
                url: providerPost,
                data: self.element.serialize(),
                type: 'post',
                dataType: 'json',

                /** Show loader before send */
                beforeSend: function () {
                    $('body').trigger('processStart');
                }
            }).always(function () {
                $('body').trigger('processStop');
            }).done(function (response) {
                console.log(response);
                if (response.errors) {
                    self.loginButton.attr('disabled', false);
                } else {
                    // if ($.isEmptyObject(response.providers)) {
                    //     self.twoFactorAuthPassed = true;
                    //     self.element.submit();
                    // } else {
                    //     self._parseResponse(response);
                    //     self.loginButton.hide();
                    // }
                }
            }).fail(function () {
                self.loginButton.attr('disabled', false);
            });
        }

    });

    return $.mage.twoFactorAuthLoginProviders;
});
