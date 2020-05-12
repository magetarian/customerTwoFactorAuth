<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Api;

use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;

interface ProviderPoolInterface
{
    /**
     * Get a list of providers
     * @return \Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface[]
     */
    public function getProviders(): array;

    /**
     * Get a list of enabled providers
     * @return \Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface[]
     */
    public function getEnabledProviders(): array;

    /**
     * Get provider by code
     * @param string $code
     * @return \Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProviderByCode($code): ProviderInterface;
}
