<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\OptionSourceInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;

/**
 * Class Provider
 * Provider for 2FA services from a customer
 */
class EnabledProviders extends AbstractSource implements OptionSourceInterface
{

    /**
     * @var ProviderPoolInterface
     */
    protected $providerPool;

    /**
     * Constructor
     *
     * @param ProviderPoolInterface $providerPool
     */
    public function __construct(
        ProviderPoolInterface $providerPool
    ) {
        $this->providerPool   = $providerPool;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->getAllOptions();
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        $result = [];
        foreach ($this->getEnabledProviders() ?? [] as $code => $name) {
            $result[] = [
                'value' => $code,
                'label' => $name,
            ];
        }

        return $result;
    }

    /**
     * Get enabled providers
     *
     * @return array
     */
    private function getEnabledProviders(): array
    {
        $enabledProviders = [];
        foreach ($this->providerPool->getEnabledProviders() as $provider) {
            $enabledProviders[$provider->getCode()] = $provider->getName();
        }
        return $enabledProviders;
    }
}
