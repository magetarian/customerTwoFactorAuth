<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\ViewModel\Customer;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use MSP\TwoFactorAuth\Model\ProviderPool;
use Magetarian\CustomerTwoFactorAuth\Model\Config\ConfigProvider;

class Configuration implements ArgumentInterface
{
    private $providerPool;

    private $configProvider;

    private $enabledProviders = [];

    public function __construct(
        ProviderPool $providerPool,
        ConfigProvider $configProvider
    ) {
        $this->providerPool = $providerPool;
        $this->configProvider = $configProvider;
    }

    public function isEnabled(): bool
    {
        $enabled = false;
        if (!$this->configProvider->isEnabled()) {
            return $enabled;
        }

        if (!count($this->getAvailableProviders())) {
            return $enabled;
        }

        return !$enabled;
    }

    private function getEnabledProviders(): array
    {
        if (!count($this->enabledProviders)) {
            foreach ($this->providerPool->getProviders() as $provider) {
                if ($provider->isEnabled()) {
                    $this->enabledProviders[$provider->getCode()] = $provider->getName();
                }
            }
        }
        return $this->enabledProviders;
    }

    public function getAvailableProviders(): array
    {
        $forcedProviders = $this->configProvider->getForcedProviders();
        $providers = $this->getEnabledProviders();
        if (!count($forcedProviders)) {
            return $providers;
        }
        return array_intersect_key($providers, array_flip($forcedProviders));
    }
}
