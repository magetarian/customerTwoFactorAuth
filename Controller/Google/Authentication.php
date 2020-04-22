<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Controller\Google;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTwoFactorAuthAttributes;
use MSP\TwoFactorAuth\Api\ProviderPoolInterface;

class Authentication extends Action implements HttpPostActionInterface
{
    private $customerAccountManagement;

    private $formKeyValidator;

    private $providerPool;

    public function __construct(
        Context $context,
        AccountManagementInterface $customerAccountManagement,
        Validator $formKeyValidator,
        ProviderPoolInterface $providerPool
    ) {
        parent::__construct($context);
        $this->customerAccountManagement = $customerAccountManagement;
        $this->formKeyValidator = $formKeyValidator;
        $this->providerPool = $providerPool;
    }


    public function execute()
    {
        $response = [
            'errors' => false,
            'providers' => []
        ];
        $httpBadRequestCode = 400;

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());
        $login = $this->getRequest()->getPost('login');

        if (
            !$validFormKey ||
            !$login ||
            $this->getRequest()->getMethod() !== 'POST' ||
            !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);

        try {
            $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
            //@todo validate if provider is not forced



        } catch (LocalizedException $e) {
//            $response = [
//                'errors' => true,
//            ];
//            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
//            $response = [
//                'errors' => true,
//            ];
//            $this->messageManager->addExceptionMessage($e, __('Invalid login or password.'));
        }

        return $resultLayout;
    }
}
