/*
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
    'mage/url',
    'Magetarian_CustomerTwoFactorAuth/js/model/selectedProvider',
    'uiComponent'
], function (
    $,
    urlBuilder,
    selectedProvider,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            configured: false,
            isActive: false
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().
            observe(['isActive', 'configured']);
            return this;
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            return this;
        },

        /**
         * @param {Object}
         */
        activate: function (data) {
            this.isActive(true);
            selectedProvider(this.getCode());
        },

        /**
         * @return {String}
         */
        getCode: function () {
           return this.code;
        },

        /**
         * @return {String}
         */
        getName: function () {
            return this.label;
        },

        /**
         * @return {Boolean}
         */
        isConfigured: function () {
            return this.configured();
        },

        /**
         * @return {Boolean}
         */
        isButtonVisible: function () {
            return !selectedProvider();
        }

    });
});
