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
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTwoFactorAuthAttributes;
use MSP\TwoFactorAuth\Api\ProviderPoolInterface;

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

    /**
     * @var ProviderPoolInterface
     */
    private $providerPool;

    /**
     * Providers constructor.
     *
     * @param Context $context
     * @param AccountManagementInterface $customerAccountManagement
     * @param Validator $formKeyValidator
     * @param ProviderPoolInterface $providerPool
     */
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

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\ResultInterface
     */
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

        try {
            $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);

            if (
                $customer->getCustomAttribute(CreateCustomerTwoFactorAuthAttributes::PROVIDERS) &&
                $customer->getCustomAttribute(CreateCustomerTwoFactorAuthAttributes::PROVIDERS)->getValue()
            ) {
                //@todo only forced options should be shown in case admin updated list
                $providersArray = explode(
                    ',',
                    $customer->getCustomAttribute(CreateCustomerTwoFactorAuthAttributes::PROVIDERS)->getValue()
                );
                foreach ($providersArray as $providerCode) {
                    $provider = $this->providerPool->getProviderByCode($providerCode);
                    $response['providers'][$providerCode] = $provider->getName();
                }
            }
        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
            ];
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
            ];
            $this->messageManager->addExceptionMessage($e, __('Invalid login or password.'));
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($response);
    }
}
