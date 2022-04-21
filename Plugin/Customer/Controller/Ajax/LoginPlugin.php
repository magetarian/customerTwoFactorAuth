<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Plugin\Customer\Controller\Ajax;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\Ajax\Login;
use Magento\Framework\Exception\LocalizedException;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Json\DecoderInterface;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerProvidersManagerInterface;

/**
 * Class LoginPlugin
 * Around plugin for ajax login
 */
class LoginPlugin
{
    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ProviderPoolInterface
     */
    private $providerPool;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * @var CustomerProvidersManagerInterface
     */
    private $customerProvidersManager;

    /**
     * LoginPlugin constructor.
     *
     * @param AccountManagementInterface $customerAccountManagement
     * @param ResultFactory $resultFactory
     * @param ProviderPoolInterface $providerPool
     * @param DataObjectFactory $dataObjectFactory
     * @param Data $jsonHelper
     * @param CustomerProvidersManagerInterface $customerProvidersManager
     */
    public function __construct(
        AccountManagementInterface $customerAccountManagement,
        ResultFactory $resultFactory,
        ProviderPoolInterface $providerPool,
        DataObjectFactory $dataObjectFactory,
        Data $jsonHelper,
        CustomerProvidersManagerInterface $customerProvidersManager
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultFactory = $resultFactory;
        $this->providerPool = $providerPool;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->jsonHelper = $jsonHelper;
        $this->customerProvidersManager = $customerProvidersManager;
    }

    /**
     * Plugin for ajax login
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param Login $subject
     * @param callable $proceed
     *
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Raw
     */
    public function aroundExecute(Login $subject, callable $proceed)
    {
        $httpBadRequestCode = 400;

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        try {
            $credentials = $this->jsonHelper->jsonDecode($subject->getRequest()->getContent());
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        if (!$credentials ||
            $subject->getRequest()->getMethod() !== 'POST' ||
            !$subject->getRequest()->isXmlHttpRequest()
        ) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        $response = [
            'errors' => true,
            'message' => __('Login successful.')
        ];
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $twoFactorAuthCode = (isset($credentials['tfa_code']) ?$credentials['tfa_code'] : null);
            $providerCode = (isset($credentials['provider_code']) ?$credentials['provider_code'] : null);

            $customer = $this->customerAccountManagement->authenticate(
                $credentials['username'],
                $credentials['password']
            );

            /** @var $customerProviders \Magetarian\CustomerTwoFactorAuth\Api\ProviderInterface[] */
            $customerProviders = $this->customerProvidersManager->getCustomerProviders((int) $customer->getId());

            if (count($customerProviders) && (!$twoFactorAuthCode || !$providerCode)) {
                $response = [
                    'errors' => true,
                    'message' => __('Login using two factor authentication, please.')
                ];
                return $resultJson->setData($response);
            }

            $providerEngine = $this->providerPool->getProviderByCode($providerCode)->getEngine();
            $verification = $this->dataObjectFactory->create([ 'data' => $credentials]);

            if (!$providerEngine->verify($customer, $verification)) {
                $response = [
                    'errors' => true,
                    'message' => __('The two factor authentication failed. Please try again.')
                ];
                return $resultJson->setData($response);
            }

        } catch (\Exception $e) {
            return $proceed();
        }

        return $proceed();
    }
}
