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
class AllProviders extends AbstractProvider
{
    /**
     * @return array
     */
    public function getAllOptions(): array
    {
        $result = [];
        foreach ($this->providerPool->getProviders() ?? [] as $provider) {
            $result[] = [
                'value' => $provider->getCode(),
                'label' => $provider->getName(),
            ];
        }

        return $result;
    }
}
