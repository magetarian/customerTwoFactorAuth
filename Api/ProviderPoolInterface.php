<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Api;

interface ProviderPoolInterface
{
    /**
     * Get a list of providers
     * @return \Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface[]
     */
    public function getProviders();

    /**
     * Get provider by code
     * @param string $code
     * @return \Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProviderByCode($code);
}
