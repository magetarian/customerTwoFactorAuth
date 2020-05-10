<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Controller\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Json\Helper\Data;
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTwoFactorAuthAttributes;
use Magento\Customer\Model\Session;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerProvidersManagerInterface;

/**
 * Class Providers
 */
class Providers extends Action implements HttpPostActionInterface
{
    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    private $customerSession;

    private $jsonHelper;

    private $customerProvidersManager;

    public function __construct(
        Context $context,
        AccountManagementInterface $customerAccountManagement,
        Validator $formKeyValidator,
        Session $customerSession,
        Data $jsonHelper,
        CustomerProvidersManagerInterface $customerProvidersManager
    ) {
        parent::__construct($context);
        $this->customerAccountManagement = $customerAccountManagement;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerSession = $customerSession;
        $this->jsonHelper = $jsonHelper;
        $this->customerProvidersManager = $customerProvidersManager;
    }


    public function execute()
    {
        $response = [
            'errors' => false,
            'providers' => [],
            'message' => '',
        ];
        $httpBadRequestCode = 400;

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        //checkout
        $loginData = [];
        try {
            $params = $this->jsonHelper->jsonDecode($this->getRequest()->getContent());
            if (is_array($params)) {
                if (isset($params['username'])) {
                    $loginData['username'] = $params['username'];
                }
                if (isset($params['password'])) {
                    $loginData['password'] = $params['password'];
                }
                $this->getRequest()->setParams($params);
            }
        } catch (\Zend_Json_Exception $e) {
            $loginData = [];
        }

        //checkout

        $validFormKey = $this->formKeyValidator->validate($this->getRequest());
        $login = $this->getRequest()->getPost('login');
        //checkout
        if (!$login && $loginData) {
            $login = $loginData;
        }
        //checkout
        if (
            !$validFormKey ||
            !$login ||
            $this->getRequest()->getMethod() !== 'POST' ||
            !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        try {
            $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
            /** @var $customerProviders \Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface[] */
            $customerProviders = $this->customerProvidersManager->getCustomerProviders((int) $customer->getId());


            foreach ($customerProviders as $provider) {
                $response['providers'][$provider->getCode()] = [
                    'label'            => $provider->getName(),
                    'code'             => $provider->getCode(),
                    'configured'       => $provider->isConfigured((int)$customer->getId()),
                    'additionalConfig' => $provider->getEngine()->getAdditionalConfig($customer)
                ];
            }
            //@todo double check if it still need or there is better option
            $this->customerSession->setTwoFaCustomerId($customer->getId());

        } catch (LocalizedException $e) {
            $response['errors'] = true;
            $response['message'] = $e->getMessage();
            //@todo remove if not used for Ui Component
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $response['errors'] = true;
            $response['message'] =__('Invalid login or password.');
            //@todo remove if not used for Ui Component
            $this->messageManager->addExceptionMessage($e, __('Invalid login or password.'));
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($response);
    }
}
