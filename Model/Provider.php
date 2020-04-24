<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model;

use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Api\EngineInterface;

/**
 * Class Provider
 */
class Provider implements ProviderInterface
{
    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var CustomerConfigManagerInterface
     */
    private $customerConfigManager;

    /**
     * @var bool
     */
    private $canReset;

    /**
     * Provider constructor.
     *
     * @param EngineInterface $engine
     * @param CustomerConfigManagerInterface $customerConfigManager
     * @param $code
     * @param $name
     * @param bool $canReset
     */
    public function __construct(
        EngineInterface $engine,
        CustomerConfigManagerInterface $customerConfigManager,
        $code,
        $name,
        $canReset = true
    ) {
        $this->engine = $engine;
        $this->customerConfigManager = $customerConfigManager;
        $this->code = $code;
        $this->name = $name;
        $this->canReset = $canReset;
    }

    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function isEnabled(): bool
    {
        return $this->getEngine()->isEnabled();
    }

    /**
     * Get provider engine
     * @return EngineInterface
     */
    public function getEngine():  EngineInterface
    {
        return $this->engine;
    }

    /**
     * Get provider code
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Get provider name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return true if this provider configuration can be reset
     * @return boolean
     */
    public function isResetAllowed(): bool
    {
        return $this->canReset;
    }

    /**
     * Return true if this provider allows trusted devices
     * @return boolean
     */
    public function isTrustedDevicesAllowed(): bool
    {
        return $this->engine->isTrustedDevicesAllowed();
    }

    /**
     * @inheritdoc
     */
    public function resetConfiguration($customerId): ProviderInterface
    {
        $this->customerConfigManager->setProviderConfig($customerId, $this->getCode(), null);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isConfigured(int $customerId): bool
    {
        return $this->getConfiguration($customerId) !== null;
    }

    /**
     * @inheritdoc
     */
    public function getConfiguration($customerId)
    {
        return $this->customerConfigManager->getProviderConfig($customerId, $this->getCode());
    }
}
