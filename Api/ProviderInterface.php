<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Api;

use Magetarian\CustomerTwoFactorAuth\Api\EngineInterface;

/**
 * Interface ProviderInterface
 */
interface ProviderInterface
{
    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function isEnabled(): bool;

    /**
     * Get provider engine
     * @return \Magetarian\CustomerTwoFactorAuth\EngineInterface
     */
    public function getEngine(): EngineInterface;

    /**
     * Get provider code
     * @return string
     */
    public function getCode(): string;

    /**
     * Get provider name
     * @return string
     */
    public function getName(): string;

    /**
     * Reset provider configuration
     * @param int $customerId
     * @return \Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface
     */
    public function resetConfiguration(int $customerId): ProviderInterface;

    /**
     * Return true if this provider has been configured
     * @param int $customerId
     * @return bool
     */
    public function isConfigured(int $customerId): bool;
}
