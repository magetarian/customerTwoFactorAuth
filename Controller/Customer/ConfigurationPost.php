<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magetarian\CustomerTwoFactorAuth\Controller\Customer;
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTFAAttributes;

/**
 * Class Configuration
 */
class ConfigurationPost extends Customer implements HttpPostActionInterface
{
    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;


    /**
     * ConfigurationPost constructor.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param Validator $formKeyValidator
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Validator $formKeyValidator,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context, $customerSession);
        $this->formKeyValidator   = $formKeyValidator;
        $this->customerRepository = $customerRepository;
    }


    /**
     * Save 2FA Authentication Configuration
     *
     * @return ResponseInterface|ResultInterface|\Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());
        if ($validFormKey) {
            try {
                $providers = $this->_request->getParam('providers');
                $customer = $this->customerSession->getCustomer()->getDataModel();
                $customer->setCustomAttribute(CreateCustomerTFAAttributes::PROVIDERS, $providers);
                $this->customerRepository->save($customer);
                $this->messageManager->addSuccessMessage(__('You saved the 2FA providers.'));
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t save the 2FA providers.'));
            }
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/configuration');
        return $resultRedirect;
    }
}
