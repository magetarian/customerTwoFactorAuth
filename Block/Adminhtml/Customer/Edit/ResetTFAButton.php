<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Block\Adminhtml\Customer\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;

/**
 * Class ResetTFAButton
 * Button for reset customer tfa configuration
 */
class ResetTFAButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var ProviderPoolInterface
     */
    private $providerPool;

    /**
     * ResetTFAButton constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ProviderPoolInterface $providerPool
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProviderPoolInterface $providerPool
    ) {
        parent::__construct($context, $registry);
        $this->providerPool = $providerPool;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $data = [];
        if ($customerId && $this->isProviderActive()) {
            $data = [
                'label' => __('Reset TFA'),
                'id' => 'customer-reset-tfa-button',
                'on_click' => sprintf("location.href = '%s';", $this->getResetUrl()),
                'class' => 'add',
                'aclResource' => 'Magetarian_CustomerTwoFactorAuth::reset_tfa',
                'sort_order' => 100
            ];
        }
        return $data;
    }

    /**
     * @return bool
     */
    private function isProviderActive(): bool
    {
        return (bool) $this->providerPool->getEnabledProviders();
    }

    /**
     * @return string
     */
    public function getResetUrl()
    {
        return $this->getUrl('customer_tfa/customer/reset', ['customer_id' => $this->getCustomerId()]);
    }
}
