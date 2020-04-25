<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\ViewModel\Duo;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Model\Session;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface;
use MSP\TwoFactorAuth\Api\ProviderPoolInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\DuoSecurity;
use Magento\Customer\Api\CustomerRepositoryInterface;

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

    public function __construct(
        ProviderPoolInterface $providerPool,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->providerPool = $providerPool;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return string
     */
    public function getApiHost(): string
    {
        return $this->getProvider()->getEngine()->getApiHostname();
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        $customer = $this->customerRepository->getById($this->getCustomerId());
        return $this->getProvider()->getEngine()->getRequestSignature($customer);
    }

    /**
     * @return string
     */
    public function getProviderCode(): string
    {
        return $this->getProvider()->getEngine()->getCode();
    }

    private function getProvider(): ProviderInterface
    {
        return $this->providerPool->getProviderByCode(DuoSecurity::CODE);
    }

    private function getCustomerId(): int
    {
        return (int) $this->customerSession->getTwoFaCustomerId();
    }
}
