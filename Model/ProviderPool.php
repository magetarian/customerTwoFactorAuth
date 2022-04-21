<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;

/**
 * Class ProviderPool
 * Provider Pool Model
 */
class ProviderPool implements ProviderPoolInterface
{
    /**
     * @var array
     */
    private $providers = [];

    /**
     * ProviderPool constructor.
     *
     * @param array $providers
     */
    public function __construct(
        $providers = []
    ) {
        $this->providers = $providers;
    }

    /**
     * Get providers
     *
     * @return \Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Get list of enabled providers
     *
     * @return \Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface[]
     */
    public function getEnabledProviders(): array
    {
        $enabledProviders = [];
        /** @var \Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface $provider */
        foreach ($this->getProviders() as $provider) {
            if ($provider->isEnabled()) {
                $enabledProviders[] = $provider;
            }
        }
        return $enabledProviders;
    }

    /**
     * Get provider by code
     * @param string $code
     *
     * @return ProviderInterface
     * @throws NoSuchEntityException
     */
    public function getProviderByCode($code): ProviderInterface
    {
        if ($code) {
            $providers = $this->getProviders();
            if (isset($providers[$code])) {
                return $providers[$code];
            }
        }

        throw new NoSuchEntityException(__('Unknown provider %1', $code));
    }
}
