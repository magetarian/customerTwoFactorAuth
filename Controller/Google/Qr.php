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
use Magento\Customer\Model\Session;

/**
 * Class Qr
 * Generation of QR code for Google Authentication
 */
class Qr extends Action implements HttpGetActionInterface
{
    /**
     * @var Google
     */
    private $google;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Qr constructor.
     *
     * @param Context $context
     * @param Google $google
     * @param CustomerRepositoryInterface $customerRepository
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        Google $google,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->google = $google;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\ResultInterface
     * @throws \Endroid\QrCode\Exception\ValidationException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            $customerId = $this->customerSession->getTwoFaCustomerId();
        }
        $customer = $this->customerRepository->getById($customerId);
        $pngData = $this->google->getQrCodeAsPng($customer);
        $resultRaw
            ->setHttpResponseCode(200)
            ->setHeader('Content-Type', 'image/png')
            ->setContents($pngData);

        return $resultRaw;
    }
}
