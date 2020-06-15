<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model;

use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Api\EngineInterface;

/**
 * Class Provider
 * Generic model of a TFA provider
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
     * Provider constructor.
     *
     * @param EngineInterface $engine
     * @param CustomerConfigManagerInterface $customerConfigManager
     * @param $code
     * @param $name
     */
    public function __construct(
        EngineInterface $engine,
        CustomerConfigManagerInterface $customerConfigManager,
        $code,
        $name
    ) {
        $this->engine = $engine;
        $this->customerConfigManager = $customerConfigManager;
        $this->code = $code;
        $this->name = $name;
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
     * @inheritdoc
     */
    public function resetConfiguration(int $customerId): ProviderInterface
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
     * @param $customerId
     *
     * @return array|null
     */
    private function getConfiguration($customerId)
    {
        return $this->customerConfigManager->getProviderConfig($customerId, $this->getCode());
    }
}
