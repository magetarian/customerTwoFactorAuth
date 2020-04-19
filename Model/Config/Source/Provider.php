<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use MSP\TwoFactorAuth\Model\ProviderPool;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Provider
 * Provider for 2FA services from a customer
 */
class Provider extends AbstractSource implements OptionSourceInterface
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
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        $options = $this->toOptionArray();
        $return  = [];

        foreach ($options as $option) {
            $return[$option['value']] = $option['label'];
        }

        return $return;
    }

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
