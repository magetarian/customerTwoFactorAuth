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
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magetarian\CustomerTwoFactorAuth\Model\Config\Source\EnabledProviders;

/**
 * Class CreateCustomerTFAAttributes
 * New Attributes for TFA
 */
class CreateCustomerTFAAttributes implements DataPatchInterface, PatchRevertableInterface
{
    /**
     *  Providers Attribute Code
     */
    const PROVIDERS        = 'tfa_providers';

    /**
     * Configuration Attribute Code
     */
    const CONFIG           = 'tfa_encoded_config';

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
     * @return CreateCustomerTFAAttributes|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->createCustomerAttributes();
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

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function createCustomerAttributes()
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
                'label'    => 'TFA Providers',
                'input'    => 'multiselect',
                'type'     => 'varchar',
                'source'   => EnabledProviders::class,
                'required' => false,
                'position' => 100,
                'visible'  => false,
                'system'   => false,
                'backend'  => ArrayBackend::class
            ]
        );

        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::CONFIG,
            [
                'label'         => 'TFA Encoded Config',
                'input'         => 'textarea',
                'type'          => 'text',
                'required'      => false,
                'position'      => 101,
                'visible'       => false,
                'system'        => false
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            self::PROVIDERS
        );
        $attribute->addData([
            'attribute_set_id'   => $attributeSetId,
            'attribute_group_id' => $attributeGroupId
        ]);
        $attribute->save();

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
