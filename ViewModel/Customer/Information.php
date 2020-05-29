<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\ViewModel\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;

/**
 * Class Information
 * ViewModel for provide customer configuration
 */
class Information implements ArgumentInterface
{
    /**
     * @var ProviderPoolInterface
     */
    private $providerPool;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Customer constructor.
     *
     * @param ProviderPoolInterface $providerPool
     * @param Session $customerSession
     */
    public function __construct(
        ProviderPoolInterface $providerPool,
        Session $customerSession
    ) {
        $this->providerPool     = $providerPool;
        $this->customerSession  = $customerSession;
    }

    /**
     * @return bool
     */
    public function isEnabled(string $providerCode): bool
    {
        return (bool) $this->getProvider($providerCode)->isEnabled();
    }

    /**
     * @return string
     */
    public function getProviderName(string $providerCode): string
    {
        return $this->getProvider($providerCode)->getName();
    }

    /**
     * @param string $providerCode
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAdditionalConfig(string $providerCode): array
    {
        return $this->getProvider($providerCode)
                    ->getEngine()
                    ->getAdditionalConfig($this->customerSession->getCustomerData());
    }

    /**
     * @param string $providerCode
     *
     * @return ProviderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProvider(string $providerCode): ProviderInterface
    {
        return $this->providerPool->getProviderByCode($providerCode);
    }
}
