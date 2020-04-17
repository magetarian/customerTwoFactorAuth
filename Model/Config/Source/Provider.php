<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use MSP\TwoFactorAuth\Model\ProviderPool;

/**
 * Class Provider
 */
class Provider implements ArrayInterface
{
    /**
     * @var ProviderPool
     */
    private $providerPool;

    /**
     * Provider constructor.
     *
     * @param ProviderPool $providerPool
     */
    public function __construct(
        ProviderPool $providerPool
    ) {
        $this->providerPool = $providerPool;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $providers = $this->providerPool->getProviders();
        $res = [];
        foreach ($providers as $provider) {
            $res[] = [
                'value' => $provider->getCode(),
                'label' => $provider->getName(),
            ];
        }

        return $res;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = $this->toOptionArray();
        $return = [];

        foreach ($options as $option) {
            $return[$option['value']] = $option['label'];
        }

        return $return;
    }
}
