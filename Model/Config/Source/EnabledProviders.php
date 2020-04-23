<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model\Config\Source;

/**
 * Class Provider
 * Provider for 2FA services from a customer
 */
class EnabledProviders extends AbstractProvider
{
    /**
     * @return array
     */
    public function getAllOptions(): array
    {
        $result = [];
        foreach ($this->getAvailableProviders() ?? [] as $code => $name) {
            $result[] = [
                'value' => $code,
                'label' => $name,
            ];
        }

        return $result;
    }


    /**
     * @return array
     */
    private function getEnabledProviders(): array
    {
        $enabledProviders = [];
        foreach ($this->providerPool->getProviders() as $provider) {
            if ($provider->isEnabled()) {
                $enabledProviders[$provider->getCode()] = $provider->getName();
            }
        };

        return $enabledProviders;
    }


    /**
     * @return array
     */
    private function getAvailableProviders(): array
    {
        $forcedProviders = $this->configProvider->getForcedProviders();
        $providers = $this->getEnabledProviders();
        if (!count($forcedProviders)) {
            return $providers;
        }
        return array_intersect_key($providers, array_flip($forcedProviders));
    }
}
