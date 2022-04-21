<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Controller\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Customer\Model\Session;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerProvidersManagerInterface;
use Magento\Framework\Data\Form\FormKey;

/**
 * Class Providers
 * The class return enabled providers for a customer login action
 */
class Providers extends Action implements HttpPostActionInterface
{
    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var CustomerProvidersManagerInterface
     */
    private $customerProvidersManager;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * Providers constructor.
     *
     * @param Context $context
     * @param AccountManagementInterface $customerAccountManagement
     * @param FormKey $formKey
     * @param Session $customerSession
     * @param Json $json
     * @param CustomerProvidersManagerInterface $customerProvidersManager
     */
    public function __construct(
        Context $context,
        AccountManagementInterface $customerAccountManagement,
        FormKey $formKey,
        Session $customerSession,
        Json $json,
        CustomerProvidersManagerInterface $customerProvidersManager
    ) {
        parent::__construct($context);
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerSession = $customerSession;
        $this->json = $json;
        $this->customerProvidersManager = $customerProvidersManager;
        $this->formKey = $formKey;
    }

    /**
     * Get customer providers
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\App\ResponseInterface
     * @throws LocalizedException
     */
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

        $loginData = [];
        try {
            $loginData = $this->json->unserialize($this->getRequest()->getContent());
        } catch (\InvalidArgumentException $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        if (!isset($loginData['form_key']) ||
            !Security::compareStrings($loginData['form_key'], $this->formKey->getFormKey()) ||
            !$loginData ||
            $this->getRequest()->getMethod() !== 'POST' ||
            !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        try {
            $customer = $this->customerAccountManagement->authenticate($loginData['username'], $loginData['password']);
            /** @var $customerProviders \Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface[] */
            $customerProviders = $this->customerProvidersManager->getCustomerProviders((int) $customer->getId());

            foreach ($customerProviders as $provider) {
                $response['providers'][$provider->getCode()] = [
                    'label'            => $provider->getName(),
                    'code'             => $provider->getCode(),
                    'configured'       => $provider->isConfigured((int) $customer->getId()),
                    'additionalConfig' => $provider->getEngine()->getAdditionalConfig($customer)
                ];
            }
            $this->customerSession->setTwoFaCustomerId((int) $customer->getId());
        } catch (LocalizedException $e) {
            $response['errors'] = true;
            $response['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $response['errors'] = true;
            $response['message'] =__('Invalid login or password.');
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($response);
    }
}
