/*
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

define([
    'jquery',
], function ($) {
    var baseUrl = '';

    return {

        setBaseUrl: function (url) {
            baseUrl = url;
        },

        verify: function (response) {
            console.log('asd');
            console.log(response);
            return 'test';
        }
    };
});
