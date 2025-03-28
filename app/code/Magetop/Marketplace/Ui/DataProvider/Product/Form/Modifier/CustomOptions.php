<?php
/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magetop\Marketplace\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Modal;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Framework\Locale\CurrencyInterface;

/**
 * Data provider for "Customizable Options" panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomOptions extends AbstractModifier
{
    /**#@+
     * Group values
     */
    const GROUP_CUSTOM_OPTIONS_NAME = 'custom_options';
    const GROUP_CUSTOM_OPTIONS_SCOPE = 'data.product';
    const GROUP_CUSTOM_OPTIONS_PREVIOUS_NAME = 'search-engine-optimization';
    const GROUP_CUSTOM_OPTIONS_DEFAULT_SORT_ORDER = 31;
    /**#@-*/

    /**#@+
     * Button values
     */
    const BUTTON_ADD = 'button_add';
    const BUTTON_IMPORT = 'button_import';
    /**#@-*/

    /**#@+
     * Container values
     */
    const CONTAINER_HEADER_NAME = 'container_header';
    const CONTAINER_OPTION = 'container_option';
    const CONTAINER_COMMON_NAME = 'container_common';
    const CONTAINER_TYPE_STATIC_NAME = 'container_type_static';
    /**#@-*/

    /**#@+
     * Grid values
     */
    const GRID_OPTIONS_NAME = 'options';
    const GRID_TYPE_SELECT_NAME = 'values';
    /**#@-*/

    /**#@+
     * Field values
     */
    const FIELD_ENABLE = 'affect_product_custom_options';
    const FIELD_OPTION_ID = 'option_id';
    const FIELD_TITLE_NAME = 'title';
    const FIELD_TYPE_NAME = 'type';
    const FIELD_IS_REQUIRE_NAME = 'is_require';
    const FIELD_SORT_ORDER_NAME = 'sort_order';
    const FIELD_PRICE_NAME = 'price';
    const FIELD_PRICE_TYPE_NAME = 'price_type';
    const FIELD_SKU_NAME = 'sku';
    const FIELD_MAX_CHARACTERS_NAME = 'max_characters';
    const FIELD_FILE_EXTENSION_NAME = 'file_extension';
    const FIELD_IMAGE_SIZE_X_NAME = 'image_size_x';
    const FIELD_IMAGE_SIZE_Y_NAME = 'image_size_y';
    const FIELD_IS_DELETE = 'is_delete';
    /**#@-*/

    /**#@+
     * Import options values
     */
    const IMPORT_OPTIONS_MODAL = 'import_options_modal';
    const CUSTOM_OPTIONS_LISTING = 'product_custom_options_listing';
    /**#@-*/

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     */
    protected $productOptionsConfig;

    /**
     * @var \Magento\Catalog\Model\Config\Source\Product\Options\Price
     */
    protected $productOptionsPrice;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    
    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $productOptionsConfig
     * @param ProductOptionsPrice $productOptionsPrice
     * @param UrlInterface $urlBuilder
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        ConfigInterface $productOptionsConfig,
        ProductOptionsPrice $productOptionsPrice,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->productOptionsConfig = $productOptionsConfig;
        $this->productOptionsPrice = $productOptionsPrice;
        $this->urlBuilder = $urlBuilder;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $options = [];
        $productOptions = $this->locator->getProduct()->getOptions() ?: [];

        /** @var \Magento\Catalog\Model\Product\Option $option */
        foreach ($productOptions as $index => $option) {
            $options[$index] = $this->formatPriceByPath(static::FIELD_PRICE_NAME, $option->getData());
            $values = $option->getValues() ?: [];

            /** @var \Magento\Catalog\Model\Product\Option $value */
            foreach ($values as $value) {
                $options[$index][static::GRID_TYPE_SELECT_NAME][] = $this->formatPriceByPath(
                    static::FIELD_PRICE_NAME,
                    $value->getData()
                );
            }
        }

        return array_replace_recursive(
            $data,
            [
                $this->locator->getProduct()->getId() => [
                    static::DATA_SOURCE_DEFAULT => [
                        static::FIELD_ENABLE => 1,
                        static::GRID_OPTIONS_NAME => $options
                    ]
                ]
            ]
        );
    }

    /**
     * Format float number to have two digits after delimiter
     *
     * @param string $path
     * @param array $data
     * @return array
     */
    protected function formatPriceByPath($path, array $data)
    {
        $value = $this->arrayManager->get($path, $data);

        if (is_numeric($value)) {
            $data = $this->arrayManager->replace($path, $data, $this->formatPrice($value));
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->createCustomOptionsPanel();

        return $this->meta;
    }

    /**
     * Create "Customizable Options" panel
     *
     * @return $this
     */
    protected function createCustomOptionsPanel()
    {
        $this->meta = array_replace_recursive(
            $this->meta,
            [
                static::GROUP_CUSTOM_OPTIONS_NAME => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Customizable Options'),
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::GROUP_CUSTOM_OPTIONS_SCOPE,
                                'collapsible' => true,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $this->meta,
                                    static::GROUP_CUSTOM_OPTIONS_PREVIOUS_NAME,
                                    static::GROUP_CUSTOM_OPTIONS_DEFAULT_SORT_ORDER
                                ),
                            ],
                        ],
                    ],
                    'children' => [
                        static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                        static::FIELD_ENABLE => $this->getEnableFieldConfig(20),
                        static::GRID_OPTIONS_NAME => $this->getOptionsGridConfig(30)
                    ]
                ]
            ]
        );

        $this->meta = array_merge_recursive(
            $this->meta,
            [
                static::IMPORT_OPTIONS_MODAL => $this->getImportOptionsModalConfig()
            ]
        );

        return $this;
    }

    /**
     * Get config for header container
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getHeaderContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $sortOrder,
                        'content' => __('Custom options let customers choose the product variations they want.'),
                    ],
                ],
            ],
            'children' => [
                /*static::BUTTON_IMPORT => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title' => __('Import Options'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => 'ns=' . static::FORM_NAME . ', index=options',
                                        'actionName' => 'clearDataProvider'
                                    ],
                                    [
                                        'targetName' => 'ns=' . static::FORM_NAME . ', index='
                                            . static::IMPORT_OPTIONS_MODAL,
                                        'actionName' => 'openModal',
                                    ],
                                    [
                                        'targetName' => 'ns=' . static::CUSTOM_OPTIONS_LISTING
                                            . ', index=' . static::CUSTOM_OPTIONS_LISTING,
                                        'actionName' => 'render',
                                    ],
                                ],
                                'displayAsLink' => true,
                                'sortOrder' => 10,
                            ],
                        ],
                    ],
                ],*/
                static::BUTTON_ADD => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title' => __('Add Option'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/button',
                                'sortOrder' => 20,
                                'actions' => [
                                    [
                                        'targetName' => 'ns = ${ $.ns }, index = ' . static::GRID_OPTIONS_NAME,
                                        'actionName' => 'processingAddChild',
                                    ]
                                ]
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for the whole grid
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getOptionsGridConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Option'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magetop_Marketplace/js/components/dynamic-rows-import-custom-options',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'addButton' => false,
                        'renderDefaultRecord' => false,
                        'columnsHeader' => false,
                        'collapsibleHeader' => true,
                        'sortOrder' => $sortOrder,
                        'dataProvider' => static::CUSTOM_OPTIONS_LISTING,
                        'links' => ['insertData' => '${ $.provider }:${ $.dataProvider }'],
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'headerLabel' => __('New Option'),
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::CONTAINER_OPTION . '.' . static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::CONTAINER_OPTION => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Fieldset::NAME,
                                        'label' => null,
                                        'sortOrder' => 10,
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(40),
                                static::CONTAINER_COMMON_NAME => $this->getCommonContainerConfig(10),
                                static::CONTAINER_TYPE_STATIC_NAME => $this->getStaticTypeContainerConfig(20),
                                static::GRID_TYPE_SELECT_NAME => $this->getSelectTypeGridConfig(30)
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Get config for hidden field responsible for enabling custom options processing
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getEnableFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Field::NAME,
						'component' => 'Magento_Ui/js/form/element/abstract',
						'template' => 'Magetop_Marketplace/form/field',
						'elementTmpl' => 'Magetop_Marketplace/form/element/input',
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_ENABLE,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for modal window "Import Options"
     *
     * @return array
     */
    protected function getImportOptionsModalConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'dataScope' => '',
                        'provider' => static::FORM_NAME . '.magetopmarketproduct_form_data_source',
                        'options' => [
                            'title' => __('Select Product'),
                            'buttons' => [
                                [
                                    'text' => __('Import'),
                                    'class' => 'action-primary',
                                    'actions' => [
                                        [
                                            'targetName' => 'index = ' . static::CUSTOM_OPTIONS_LISTING,
                                            'actionName' => 'save'
                                        ],
                                        'closeModal'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'children' => [
                static::CUSTOM_OPTIONS_LISTING => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => false,
                                'componentType' => 'insertListing',
                                'dataScope' => static::CUSTOM_OPTIONS_LISTING,
                                'externalProvider' => static::CUSTOM_OPTIONS_LISTING . '.'
                                    . static::CUSTOM_OPTIONS_LISTING . '_data_source',
                                'selectionsProvider' => static::CUSTOM_OPTIONS_LISTING . '.'
                                    . static::CUSTOM_OPTIONS_LISTING . '.product_columns.ids',
                                'ns' => static::CUSTOM_OPTIONS_LISTING,
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => true,
                                'behaviourType' => 'edit',
                                'externalFilterMode' => true,
                                'currentProductId' => $this->locator->getProduct()->getId(),
                                'dataLinks' => [
                                    'imports' => false,
                                    'exports' => true
                                ],
                                'exports' => [
                                    'currentProductId' => '${ $.externalProvider }:params.current_product_id'
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for container with common fields for any type
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getCommonContainerConfig($sortOrder)
    {
        $commonContainer = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'formElement' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
						'template' => 'Magetop_Marketplace/group/group',
						'fieldTemplate' => 'Magetop_Marketplace/form/field',						
                        'breakLine' => false,
                        'showLabel' => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                static::FIELD_OPTION_ID => $this->getOptionIdFieldConfig(10),
                static::FIELD_TITLE_NAME => $this->getTitleFieldConfig(
                    20,
                    [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Option Title'),
                                    'component' => 'Magetop_Marketplace/component/static-type-input',
                                    'valueUpdate' => 'input',
                                    'imports' => [
                                        'optionId' => '${ $.provider }:${ $.parentScope }.option_id'
                                    ]
                                ],
                            ],
                        ],
                    ]
                ),
                static::FIELD_TYPE_NAME => $this->getTypeFieldConfig(30),
                static::FIELD_IS_REQUIRE_NAME => $this->getIsRequireFieldConfig(40)
            ]
        ];

        if ($this->locator->getProduct()->getStoreId()) {
            $useDefaultConfig = [
                'service' => [
                    'template' => 'Magento_Catalog/form/element/helper/custom-option-service',
                ]
            ];
            $titlePath = $this->arrayManager->findPath(static::FIELD_TITLE_NAME, $commonContainer, null)
                . static::META_CONFIG_PATH;
            $commonContainer = $this->arrayManager->merge($titlePath, $commonContainer, $useDefaultConfig);
        }

        return $commonContainer;
    }

    /**
     * Get config for container with fields for all types except "select"
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getStaticTypeContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'formElement' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
						'template' => 'Magetop_Marketplace/group/group',
						'fieldTemplate' => 'Magetop_Marketplace/form/field',						
                        'breakLine' => false,
                        'showLabel' => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                static::FIELD_PRICE_NAME => $this->getPriceFieldConfig(10),
                static::FIELD_PRICE_TYPE_NAME => $this->getPriceTypeFieldConfig(20),
                static::FIELD_SKU_NAME => $this->getSkuFieldConfig(30),
                static::FIELD_MAX_CHARACTERS_NAME => $this->getMaxCharactersFieldConfig(40),
                static::FIELD_FILE_EXTENSION_NAME => $this->getFileExtensionFieldConfig(50),
                static::FIELD_IMAGE_SIZE_X_NAME => $this->getImageSizeXFieldConfig(60),
                static::FIELD_IMAGE_SIZE_Y_NAME => $this->getImageSizeYFieldConfig(70)
            ]
        ];
    }

    /**
     * Get config for grid for "select" types
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getSelectTypeGridConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Value'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'renderDefaultRecord' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::FIELD_TITLE_NAME => $this->getTitleFieldConfig(10),
                        static::FIELD_PRICE_NAME => $this->getPriceFieldConfig(20),
                        static::FIELD_PRICE_TYPE_NAME => $this->getPriceTypeFieldConfig(30, ['fit' => true]),
                        static::FIELD_SKU_NAME => $this->getSkuFieldConfig(40),
                        static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(50),
                        static::FIELD_IS_DELETE => $this->getIsDeleteFieldConfig(60)
                    ]
                ]
            ]
        ];
    }

    /**
     * Get config for hidden id field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getOptionIdFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Input::NAME,
                        'componentType' => Field::NAME,
                        'dataScope' => static::FIELD_OPTION_ID,
                        'sortOrder' => $sortOrder,
                        'visible' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Title" fields
     *
     * @param int $sortOrder
     * @param array $options
     * @return array
     */
    protected function getTitleFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Title'),
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
							'component' => 'Magento_Ui/js/form/element/abstract',
							'template' => 'Magetop_Marketplace/form/field',	
							'elementTmpl' => 'Magetop_Marketplace/form/element/input',							
                            'dataScope' => static::FIELD_TITLE_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'validation' => [
                                'required-entry' => true
                            ],
                        ],
                    ],
                ],
            ],
            $options
        );
    }

    /**
     * Get config for "Option Type" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getTypeFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Option Type'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'component' => 'Magetop_Marketplace/js/custom-options-type',
						'template' => 'Magetop_Marketplace/form/field',
                        'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                        'selectType' => 'optgroup',
                        'dataScope' => static::FIELD_TYPE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'options' => $this->getProductOptionTypes(),
                        'disableLabel' => true,
                        'multiple' => false,
                        'selectedPlaceholders' => [
                            'defaultPlaceholder' => __('-- Please select --'),
                        ],
                        'validation' => [
                            'required-entry' => true
                        ],
                        'groupsConfig' => [
                            'text' => [
                                'values' => ['field', 'area'],
                                'indexes' => [
                                    static::CONTAINER_TYPE_STATIC_NAME,
                                    static::FIELD_PRICE_NAME,
                                    static::FIELD_PRICE_TYPE_NAME,
                                    static::FIELD_SKU_NAME,
                                    static::FIELD_MAX_CHARACTERS_NAME
                                ]
                            ],
                            'file' => [
                                'values' => ['file'],
                                'indexes' => [
                                    static::CONTAINER_TYPE_STATIC_NAME,
                                    static::FIELD_PRICE_NAME,
                                    static::FIELD_PRICE_TYPE_NAME,
                                    static::FIELD_SKU_NAME,
                                    static::FIELD_FILE_EXTENSION_NAME,
                                    static::FIELD_IMAGE_SIZE_X_NAME,
                                    static::FIELD_IMAGE_SIZE_Y_NAME
                                ]
                            ],
                            'select' => [
                                'values' => ['drop_down', 'radio', 'checkbox', 'multiple'],
                                'indexes' => [
                                    static::GRID_TYPE_SELECT_NAME
                                ]
                            ],
                            'data' => [
                                'values' => ['date', 'date_time', 'time'],
                                'indexes' => [
                                    static::CONTAINER_TYPE_STATIC_NAME,
                                    static::FIELD_PRICE_NAME,
                                    static::FIELD_PRICE_TYPE_NAME,
                                    static::FIELD_SKU_NAME
                                ]
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Required" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getIsRequireFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Required'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => static::FIELD_IS_REQUIRE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'value' => '1',
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0'
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field used for sorting
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getPositionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
						'component' => 'Magento_Ui/js/form/element/abstract',
						'template' => 'Magetop_Marketplace/form/field',			
						'elementTmpl' => 'Magetop_Marketplace/form/element/input',	
                        'dataScope' => static::FIELD_SORT_ORDER_NAME,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field used for removing rows
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getIsDeleteFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => ActionDelete::NAME,
                        'fit' => true,
                        'sortOrder' => $sortOrder
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Price" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getPriceFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Price'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_NAME,
						'component' => 'Magento_Ui/js/form/element/abstract',
						'template' => 'Magetop_Marketplace/form/field',		
						'elementTmpl' => 'Magetop_Marketplace/form/element/input',
                        'dataType' => Number::NAME,
                        'addbefore' => $this->getCurrencySymbol(),
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'validate-zero-or-greater' => true
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Price Type" field
     *
     * @param int $sortOrder
     * @param array $config
     * @return array
     */
    protected function getPriceTypeFieldConfig($sortOrder, array $config = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Price Type'),
                            'componentType' => Field::NAME,
                            'formElement' => Select::NAME,
							'component' => 'Magento_Ui/js/form/element/select',
							'template' => 'Magetop_Marketplace/form/field',							
							'elementTmpl' => 'Magetop_Marketplace/form/element/select',
                            'dataScope' => static::FIELD_PRICE_TYPE_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'options' => $this->productOptionsPrice->toOptionArray(),
                        ],
                    ],
                ],
            ],
            $config
        );
    }

    /**
     * Get config for "SKU" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getSkuFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('SKU'),
                        'componentType' => Field::NAME,
						'component' => 'Magento_Ui/js/form/element/abstract',
						'template' => 'Magetop_Marketplace/form/field',						
						'elementTmpl' => 'Magetop_Marketplace/form/element/input',	
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_SKU_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Max Characters" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getMaxCharactersFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Max Characters'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
						'component' => 'Magento_Ui/js/form/element/abstract',
						'template' => 'Magetop_Marketplace/form/field',	
						'elementTmpl' => 'Magetop_Marketplace/form/element/input',						
                        'dataScope' => static::FIELD_MAX_CHARACTERS_NAME,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'validate-zero-or-greater' => true
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "File Extension" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getFileExtensionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Compatible File Extensions'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
						'component' => 'Magento_Ui/js/form/element/abstract',
						'template' => 'Magetop_Marketplace/form/field',
						'elementTmpl' => 'Magetop_Marketplace/form/element/input',	
                        'dataScope' => static::FIELD_FILE_EXTENSION_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Maximum Image Width" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getImageSizeXFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Maximum Image Size'),
                        'notice' => __('Please leave blank if it is not an image.'),
                        'addafter' => __('px.'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
						'component' => 'Magento_Ui/js/form/element/abstract',
						'template' => 'Magetop_Marketplace/form/field',
						'elementTmpl' => 'Magetop_Marketplace/form/element/input',	
                        'dataScope' => static::FIELD_IMAGE_SIZE_X_NAME,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'validate-zero-or-greater' => true
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Maximum Image Height" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getImageSizeYFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => ' ',
                        'addafter' => __('px.'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
						'component' => 'Magento_Ui/js/form/element/abstract',
						'template' => 'Magetop_Marketplace/form/field',	
						'elementTmpl' => 'Magetop_Marketplace/form/element/input',	
                        'dataScope' => static::FIELD_IMAGE_SIZE_Y_NAME,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'validate-zero-or-greater' => true
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get options for drop-down control with product option types
     *
     * @return array
     */
    protected function getProductOptionTypes()
    {
        $options = [];
        $groupIndex = 0;

        foreach ($this->productOptionsConfig->getAll() as $option) {
            $group = [
                'value' => $groupIndex,
                //TODO: Wrap label with __() or remove this TODO after MAGETWO-49771 is closed
                'label' => $option['label'],
                'optgroup' => []
            ];

            foreach ($option['types'] as $type) {
                if ($type['disabled']) {
                    continue;
                }

                //TODO: Wrap label with __() or remove this TODO after MAGETWO-49771 is closed
                $group['optgroup'][] = ['label' => $type['label'], 'value' => $type['name']];
            }

            if (count($group['optgroup'])) {
                $options[] = $group;
                $groupIndex += 1;
            }
        }

        return $options;
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    protected function getCurrencySymbol()
    {
        return $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }

    /**
     * The getter function to get the locale currency for real application code
     *
     * @return \Magento\Framework\Locale\CurrencyInterface
     *
     * @deprecated
     */
    private function getLocaleCurrency()
    {
        if ($this->localeCurrency === null) {
            $this->localeCurrency = \Magento\Framework\App\ObjectManager::getInstance()->get(CurrencyInterface::class);
        }
        return $this->localeCurrency;
    }
    
    /**
     * Format price according to the locale of the currency
     *
     * @param mixed $value
     * @return string
     */
    protected function formatPrice($value)
    {
        if (!is_numeric($value)) {
            return null;
        }

        $store = $this->storeManager->getStore();
        $currency = $this->getLocaleCurrency()->getCurrency($store->getBaseCurrencyCode());
        $value = $currency->toCurrency($value, ['display' => \Magento\Framework\Currency::NO_SYMBOL]);

        return $value;
    }
}
