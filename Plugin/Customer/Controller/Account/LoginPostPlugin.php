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
use Magento\Framework\Controller\Result\RedirectFactory;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\DataObjectFactory;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerProvidersManagerInterface;

class LoginPostPlugin
{
    private $customerAccountManagement;

    private $resultRedirectFactory;

    private $providerPool;

    private $messageManager;

    private $dataObjectFactory;

    private $customerProvidersManager;

    public function __construct(
        AccountManagementInterface $customerAccountManagement,
        RedirectFactory $resultRedirectFactory,
        ProviderPoolInterface $providerPool,
        ManagerInterface $messageManager,
        DataObjectFactory $dataObjectFactory,
        CustomerProvidersManagerInterface $customerProvidersManager
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->providerPool = $providerPool;
        $this->messageManager = $messageManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customerProvidersManager = $customerProvidersManager;
    }

    public function aroundExecute(LoginPost $subject, callable $proceed)
    {
        if ($subject->getRequest()->isPost()) {
            $login = $subject->getRequest()->getPost('login');
            $twoFactorAuthCode = $subject->getRequest()->getPost('tfa_code');
            $providerCode = $subject->getRequest()->getPost('provider_code');
            $resultRedirect = $this->resultRedirectFactory->create();
            try {
                $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                /** @var $customerProviders \Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface[] */
                $customerProviders = $this->customerProvidersManager->getCustomerProviders((int) $customer->getId());

                if (count($customerProviders) && (!$twoFactorAuthCode || !$providerCode) ) {
                    $this->messageManager->addWarningMessage(
                        __('Login using two factor authentication, please.')
                    );
                    $resultRedirect->setPath('*/*/');
                    return $resultRedirect;
                }
                $providerEngine = $this->providerPool->getProviderByCode($providerCode)->getEngine();
                $verification = $this->dataObjectFactory->create([ 'data' => $subject->getRequest()->getParams()]);

                if (!$providerEngine->verify($customer, $verification)) {
                    $resultRedirect->setPath('*/*/');
                    $this->messageManager->addErrorMessage(
                        __('The two factor authentication failed. Please try again.')
                    );
                    return $resultRedirect;
                }

            } catch (\Exception $e) {
                return $proceed();
            }
        }
        return $proceed();
    }
}
