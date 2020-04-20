<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Controller\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTwoFactorAuthAttributes;

class Providers extends Action
{
    private $customerAccountManagement;

    public function __construct(
        Context $context,
        AccountManagementInterface $customerAccountManagement,
    ) {
        parent::__construct($context);
        $this->customerAccountManagement = $customerAccountManagement;
    }

    public function execute()
    {
        $response = [
            'errors' => false,
            'providers' => []
        ];

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        try {
            var_dump($this->getRequest()->getContent()); die();
            $credentials = $this->helper->jsonDecode($this->getRequest()->getContent());
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }


//            $login = $subject->getRequest()->getPost('login');
//            try {
//                $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
//                if (
//                    $customer->getCustomAttribute(CreateCustomerTwoFactorAuthAttributes::PROVIDERS) &&
//                    $customer->getCustomAttribute(CreateCustomerTwoFactorAuthAttributes::PROVIDERS)->getValue()
//                ) {
//                    $resultRedirect = $this->resultRedirectFactory->create();
//                    $resultRedirect->setPath('twoFactorAuth/customer/loginProviders');
//                    return $resultRedirect;
//                }
//            } catch (\Exception $e) {
//                return $proceed();
//            }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($response);
    }
}
