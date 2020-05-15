<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\ViewModel\Google;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\Google as MspGoogle;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerProviderConfigInterface;

/**
 * Class Customer
 * ViewModel for Google Authentication customer configuration
 */
class Customer implements ArgumentInterface, CustomerProviderConfigInterface
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
    public function isEnabled(): bool
    {
        return (bool) $this->getProvider()->isEnabled();
    }

    /**
     * @return string
     */
    public function getProviderName(): string
    {
        return $this->getProvider()->getName();
    }

    /**
     * @return mixed
     */
    public function getSecretCode()
    {
        return $this->getProvider()->getEngine()->getSecretCode($this->getCustomerId());
    }

    /**
     * @return int
     */
    private function getCustomerId(): int
    {
        return (int) $this->customerSession->getCustomerId();
    }

    /**
     * @return ProviderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProvider(): ProviderInterface
    {
        return $this->providerPool->getProviderByCode(MspGoogle::CODE);
    }
}
