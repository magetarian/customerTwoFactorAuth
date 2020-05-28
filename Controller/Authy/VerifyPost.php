<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Controller\Authy;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magetarian\CustomerTwoFactorAuth\Model\Provider\Engine\Authy;

class VerifyPost extends Action implements HttpPostActionInterface
{
    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    private $json;

    /**
     * @var FormKey
     */
    private $formKey;

    private $authy;

    public function __construct(
        Context $context,
        AccountManagementInterface $customerAccountManagement,
        FormKey $formKey,
        Json $json,
        Authy $authy
    ) {
        parent::__construct($context);
        $this->customerAccountManagement = $customerAccountManagement;
        $this->json = $json;
        $this->formKey = $formKey;
        $this->authy = $authy;
    }

    public function execute()
    {
        $response = [
            'errors' => false,
            'data' => ['oneTouchToken' => false, 'oneTouchStatus' => false],
            'message' => '',
        ];
        $httpBadRequestCode = 400;
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        $authData = [];
        try {
            $authData = $this->json->unserialize($this->getRequest()->getContent());
        } catch (\InvalidArgumentException $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        if (!isset($authData['form_key']) ||
            !Security::compareStrings($authData['form_key'], $this->formKey->getFormKey()) ||
            !$authData ||
            $this->getRequest()->getMethod() !== 'POST' ||
            !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        try {
            $customer = $this->customerAccountManagement->authenticate(
                $authData['username'],
                $authData['password']
            );

            $result = $this->authy->requestToken(
                $customer,
                $authData['method'],
                (isset($authData['code']) ? $authData['code'] : null)
            );
            $response['data']['oneTouchCode'] = (isset($result['code']) ? $result['code'] : false);
            $response['data']['oneTouchStatus'] = (isset($result['status']) ? $result['status'] : false);

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
