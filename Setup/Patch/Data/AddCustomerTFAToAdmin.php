<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTFAAttributes;

/**
 * Class AddCustomerTFAToAdmin
 * Add customer providers to customer edit page
 */
class AddCustomerTFAToAdmin implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetup
     */
    private $customerSetupFactory;

    /**
     * AddCustomerTFAToAdmin constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @return $this|AddCustomerTFAToAdmin
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->addToAdmin();
        $this->moduleDataSetup->getConnection()->endSetup();
        return $this;
    }

    /**
     *
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->updateAttribute(
            Customer::ENTITY,
            CreateCustomerTFAAttributes::PROVIDERS,
            'is_visible',
            false
        );
        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            CreateCustomerTFAAttributes::PROVIDERS
        );
        $attribute->setData(
            'used_in_forms',
            []
        );
        $attribute->save();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [CreateCustomerTFAAttributes::class];
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addToAdmin()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(
            ['setup' => $this->moduleDataSetup]
        );
        $customerSetup->updateAttribute(
            Customer::ENTITY,
            CreateCustomerTFAAttributes::PROVIDERS,
            'is_visible',
            true
        );
        $customerSetup->updateAttribute(
            Customer::ENTITY,
            CreateCustomerTFAAttributes::CONFIG,
            'backend_model'
        );
        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            CreateCustomerTFAAttributes::PROVIDERS
        );
        $attribute->addData(
            [
                'used_in_forms' => ['adminhtml_customer']
            ]
        );

        $attribute->save();
    }
}
