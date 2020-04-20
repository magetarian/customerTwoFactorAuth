<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Plugin\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\Account\LoginPost;
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTwoFactorAuthAttributes;
use Magento\Framework\Controller\Result\RedirectFactory;

class LoginPostPlugin
{
    private $customerAccountManagement;

    private $resultRedirectFactory;

    public function __construct(
        AccountManagementInterface $customerAccountManagement,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function aroundExecute(LoginPost $subject, callable $proceed)
    {

        if ($subject->getRequest()->isPost()) {
            $login = $subject->getRequest()->getPost('login');
            try {
                $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                if (
                    $customer->getCustomAttribute(CreateCustomerTwoFactorAuthAttributes::PROVIDERS) &&
                    $customer->getCustomAttribute(CreateCustomerTwoFactorAuthAttributes::PROVIDERS)->getValue()
                ) {
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('twoFactorAuth/customer/loginProviders');
                    return $resultRedirect;
                }
            } catch (\Exception $e) {
                return $proceed();
            }
        }
        return $proceed();
    }
}
