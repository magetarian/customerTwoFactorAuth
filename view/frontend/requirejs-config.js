/*
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

var config = {
    map: {
        '*': {
            tfaProviderDuoApi:  'Magetarian_CustomerTwoFactorAuth/js/model/duo/api'
        }
    },
    config: {
        mixins: {
            'Magento_Customer/js/action/login': {
                'Magetarian_CustomerTwoFactorAuth/js/action/login-mixin': true
            }
        }
    }
};
