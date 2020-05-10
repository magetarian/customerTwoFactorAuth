<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;

/**
 * Class ProviderPool
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
     * @return array
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
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
