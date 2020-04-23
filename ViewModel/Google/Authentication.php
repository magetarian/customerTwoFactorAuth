<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\ViewModel\Google;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\RequestInterface;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Model\Provider\Engine\Google;

class Authentication  implements ArgumentInterface
{
    private $request;

    private $customerAccountManagement;

    private $customerConfigManager;

    private $google;

    public function __construct(
        RequestInterface $request,
        AccountManagementInterface $customerAccountManagement,
        CustomerConfigManagerInterface $customerConfigManager,
        Google $google
    ) {
        $this->request     = $request;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerConfigManager = $customerConfigManager;
        $this->google = $google;
    }

    public function isActive(): bool
    {
        //@todo replace google with constant
        $customer = $this->getCustomer();
        return $this->customerConfigManager->isProviderConfigurationActive($customer->getId(), 'google');
    }

    public function getCustomerId()
    {
        return $this->getCustomer()->getId();
    }

    public function getSecretCode()
    {
        return $this->google->getSecretCode($this->getCustomer());
    }

    private function getCustomer(): CustomerInterface
    {
        $login = $this->request->getPost('login');
        return $this->customerAccountManagement->authenticate($login['username'], $login['password']);
    }
}
