<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Controller\Adminhtml\Customer;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;

/**
 * Class Reset
 * Reset TFA configuration for a customer
 */
class Reset extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magetarian_CustomerTwoFactorAuth::reset_tfa';

    /**
     * @var ProviderPoolInterface
     */
    private $providerPool;

    /**
     * Reset constructor.
     *
     * @param Context $context
     * @param ProviderPoolInterface $providerPool
     */
    public function __construct(
        Context $context,
        ProviderPoolInterface $providerPool
    ) {
        $this->providerPool = $providerPool;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $customerId = (int)$this->getRequest()->getParam('customer_id', 0);
        if (!$customerId) {
            $resultRedirect->setPath('customer/index');
            return $resultRedirect;
        }

        try {
            foreach ($this->providerPool->getProviders() as $provider) {
                $provider->resetConfiguration($customerId);
            }

            $this->messageManager->addSuccessMessage(
                __('The customer tfa configration has been reset.')
            );
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while resetting customer tfa configuration.')
            );
        }
        $resultRedirect->setPath(
            'customer/index/edit',
            ['id' => $customerId, '_current' => true]
        );
        return $resultRedirect;
    }
}
