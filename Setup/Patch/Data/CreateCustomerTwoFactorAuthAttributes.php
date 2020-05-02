<?php
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magetarian\CustomerTwoFactorAuth\Model\Attribute\Backend\TwoFaEncodedConfig;
use Magetarian\CustomerTwoFactorAuth\Model\Config\Source\EnabledProviders;

/**
 * Class CreateCustomerTwoFactorAuthAttributes
 * Customer attributes for 2FA
 */
class CreateCustomerTwoFactorAuthAttributes implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * Constants
     */
    const PROVIDERS        = 'two_fa_providers';
    const CONFIG           = 'two_fa_encoded_config';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetup
     */
    private $customerSetupFactory;

    /**
     * @var SetFactory
     */
    private $attributeSetFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param SetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        SetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->createProviderAttribute();
        $this->createProviderConfigAttribute();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->removeAttribute(
            Customer::ENTITY,
            self::PROVIDERS
        );
        $customerSetup->removeAttribute(
            Customer::ENTITY,
            self::CONFIG
        );
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
        return [];
    }

    private function createProviderAttribute()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(
            ['setup' => $this->moduleDataSetup]
        );
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(
            Customer::ENTITY
        );
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet Set */
        $attributeSet     = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::PROVIDERS,
            [
                'label'    => 'Two Factor Auth Providers',
                'input'    => 'multiselect',
                'type'     => 'varchar',
                'source'   => EnabledProviders::class,
                'required' => false,
                'position' => 100,
                'visible'  => true,
                'system'   => false,
                'backend'  => ArrayBackend::class
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            self::PROVIDERS
        );

        $attribute->addData([
            'attribute_set_id'   => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms'      => [
                'adminhtml_customer'
            ]
        ]);

        $attribute->save();
    }

    private function createProviderConfigAttribute()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(
            ['setup' => $this->moduleDataSetup]
        );
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(
            Customer::ENTITY
        );
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet Set */
        $attributeSet     = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::CONFIG,
            [
                'label'         => 'Two Factor Auth Encoded Config',
                'input'         => 'textarea',
                'type'          => 'text',
                'required'      => false,
                'position'      => 101,
                'visible'       => false,
                'system'        => false,
                'backend_model' => TwoFaEncodedConfig::class
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            self::CONFIG
        );

        $attribute->addData([
            'attribute_set_id'   => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
        ]);

        $attribute->save();
    }
}
