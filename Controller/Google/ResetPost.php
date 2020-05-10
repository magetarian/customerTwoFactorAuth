<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Controller\Google;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\Google as MspGoogle;
use Magetarian\CustomerTwoFactorAuth\Controller\Customer;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;

/**
 * Class ResetPost
 */
class ResetPost extends Customer implements HttpPostActionInterface
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
     * @var
     */
    private $customerConfigManager;

    /**
     * ResetPost constructor.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param Validator $formKeyValidator
     * @param ProviderPoolInterface $providerPool
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Validator $formKeyValidator,
        ProviderPoolInterface $providerPool
    ) {
        parent::__construct($context, $customerSession);
        $this->formKeyValidator   = $formKeyValidator;
        $this->providerPool = $providerPool;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());
        if ($validFormKey) {
            try {
                $provider = $this->providerPool->getProviderByCode(MspGoogle::CODE);
                $provider->resetConfiguration((int) $this->customerSession->getCustomerId());
                $this->messageManager->addSuccessMessage(__('The configuration has been reset.'));
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t reset the configuration.'));
            }
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/customer/configuration');
        return $resultRedirect;
    }
}
