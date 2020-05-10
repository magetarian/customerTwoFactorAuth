<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\ViewModel\Google;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Model\Session;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\Google;

/**
 * Class Authentication
 */
class Authentication  implements ArgumentInterface
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
     * Authentication constructor.
     *
     * @param ProviderPoolInterface $providerPool
     * @param Session $customerSession
     */
    public function __construct(
        ProviderPoolInterface $providerPool,
        Session $customerSession
    ) {
        $this->providerPool = $providerPool;
        $this->customerSession = $customerSession;
    }

    /**
     * @return bool
     */
    public function isConfigured(): bool
    {
        return  $this->getProvider()->isConfigured($this->getCustomerId());
    }

    /**
     * @return string
     */
    public function getSecretCode(): string
    {
        return $this->getProvider()->getEngine()->getSecretCode($this->getCustomerId());
    }

    /**
     * @return string
     */
    public function getProviderCode(): string
    {
        return $this->getProvider()->getEngine()->getCode();
    }

    /**
     * @return ProviderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProvider(): ProviderInterface
    {
        return $this->providerPool->getProviderByCode(Google::CODE);
    }

    private function getCustomerId(): int
    {
        return (int) $this->customerSession->getTwoFaCustomerId();
    }
}
