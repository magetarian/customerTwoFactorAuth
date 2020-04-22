<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Api;

interface CustomerConfigManagerInterface
{
    /**
     * Return true if a provider configuration has been activated
     * @param int $customerId
     * @param string $providerCode
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isProviderConfigurationActive($customerId, $providerCode): bool;

    public function getProviderConfig($customerId, $providerCode): ?array;

    public function setProviderConfig($customerId, $providerCode);
}
