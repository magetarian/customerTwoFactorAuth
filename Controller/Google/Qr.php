<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Controller\Google;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magetarian\CustomerTwoFactorAuth\Model\Provider\Engine\Google;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Qr extends Action implements HttpGetActionInterface
{
    private $google;

    private $customerRepository;

    public function __construct(
        Context $context,
        Google $google,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context);
        $this->google = $google;
        $this->customerRepository = $customerRepository;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $customerId = $this->getRequest()->getParam('customerId');
        $customer = $this->customerRepository->getById($customerId);
        //@todo restrict only if not activated
        //@todo add email to a session
        //@todo if email not provided
        $pngData = $this->google->getQrCodeAsPng($customer);
        $resultRaw
            ->setHttpResponseCode(200)
            ->setHeader('Content-Type', 'image/png')
            ->setContents($pngData);

        return $resultRaw;
    }
}
