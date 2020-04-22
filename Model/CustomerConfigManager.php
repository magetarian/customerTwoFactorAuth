<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model;

use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;

class CustomerConfigManager implements CustomerConfigManagerInterface
{

    /**
     * @inheritdoc
     */
    public function isProviderConfigurationActive($customerId, $providerCode): bool
    {
//        $config = $this->getProviderConfig($customerId, $providerCode);
//        return $config &&
//               isset($config[UserConfigManagerInterface::ACTIVE_CONFIG_KEY]) &&
//               $config[UserConfigManagerInterface::ACTIVE_CONFIG_KEY];

        return  false;
    }

    public function getProviderConfig($customerId, $providerCode): ?array
    {
        //@todo implementation
        return [];
    }

    public function setProviderConfig($customerId, $providerCode)
    {
        //@todo implementation
    }
}
