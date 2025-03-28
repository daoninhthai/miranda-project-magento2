<?php
/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magetop\Marketplace\Ui\Downloadable\DataProvider\Product\Form\Modifier;

//use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magetop\Marketplace\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Source\TypeUpload;
use Magento\Downloadable\Model\Source\Shareable;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\DynamicRows;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form;

/**
 * Class adds a grid with links
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Links extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var TypeUpload
     */
    protected $typeUpload;

    /**
     * @var Shareable
     */
    protected $shareable;

    /**
     * @var Data\Links
     */
    protected $linksData;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     * @param TypeUpload $typeUpload
     * @param Shareable $shareable
     * @param Data\Links $linksData
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        TypeUpload $typeUpload,
        Shareable $shareable,
        Data\Links $linksData
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
        $this->typeUpload = $typeUpload;
        $this->shareable = $shareable;
        $this->linksData = $linksData;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();

        $data[$model->getId()][self::DATA_SOURCE_DEFAULT]['links_title'] = $this->linksData->getLinksTitle();
        $data[$model->getId()][self::DATA_SOURCE_DEFAULT]['links_purchased_separately']
            = $this->linksData->isProductLinksCanBePurchasedSeparately() ? '1' : '0';
        $data[$model->getId()]['downloadable']['link'] = $this->linksData->getLinksData();

        return $data;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $linksPath = Composite::CHILDREN_PATH . '/' . Composite::CONTAINER_LINKS;
		
        $linksContainer['arguments']['data']['config'] = [
            'componentType' => Form\Fieldset::NAME,
            'additionalClasses' => 'admin__fieldset-section',
            'label' => __('Links'),
            'dataScope' => '',
            'visible' => $this->locator->getProduct()->getTypeId() === Type::TYPE_DOWNLOADABLE,
            'sortOrder' => 30,
        ];
        $linksTitle['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'label' => __('Title'),
            'dataScope' => 'product.links_title',
			'component' => 'Magento_Ui/js/form/element/abstract',
			'template' => 'Magetop_Marketplace/form/field',
			'elementTmpl' => 'Magetop_Marketplace/form/element/input',
            'scopeLabel' => $this->storeManager->isSingleStoreMode() ? '' : '',
        ];
        $linksPurchasedSeparately['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Checkbox::NAME,
            'dataType' => Form\Element\DataType\Number::NAME,
            'description' => __('Links can be purchased separately'),
            'label' => ' ',
            'dataScope' => 'product.links_purchased_separately',
            'scopeLabel' => $this->storeManager->isSingleStoreMode() ? '' : '',
			'component' => 'Magento_Ui/js/form/element/single-checkbox',
			'template' => 'Magetop_Marketplace/form/field',
            'valueMap' => [
                'false' => '0',
                'true' => '1',
            ],
        ];
        // @codingStandardsIgnoreStart
        $informationLinks['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/html',
            'additionalClasses' => 'admin__fieldset-note',
            'content' => __('Alphanumeric, dash and underscore characters are recommended for filenames. Improper characters are replaced with \'_\'.'),
        ];
		
        // @codingStandardsIgnoreEnd

        $linksContainer = $this->arrayManager->set(
            'children',
            $linksContainer,
            [
                'links_title' => $linksTitle,
                'links_purchased_separately' => $linksPurchasedSeparately,
                'link' => $this->getDynamicRows(),
                'information_links' => $informationLinks,
            ]
        );
		
		//echo '<pre>';print_r($linksContainer);die;
        return $this->arrayManager->set($linksPath, $meta, $linksContainer);
    }

    /**
     * @return array
     */
    protected function getDynamicRows()
    {
        $dynamicRows['arguments']['data']['config'] = [
            'addButtonLabel' => __('Add Link'),
            'componentType' => DynamicRows::NAME,
            'itemTemplate' => 'record',
            'renderDefaultRecord' => false,
            'columnsHeader' => true,
            'additionalClasses' => 'admin__field-wide',
            'dataScope' => 'downloadable',
            'deleteProperty' => 'is_delete',
			//'template' => 'Magetop_Marketplace/form/field',
            'deleteValue' => '1',
        ];		
        return $this->arrayManager->set('children/record', $dynamicRows, $this->getRecord());
    }

    /**
     * @return array
     */
    protected function getRecord()
    { 
        $record['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'isTemplate' => true,
            'is_collection' => true,
            'component' => 'Magento_Ui/js/dynamic-rows/record',
			//'template' => 'Magetop_Marketplace/form/field',
            'dataScope' => '',
        ];
        $recordPosition['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Number::NAME,
            'dataScope' => 'sort_order',
			'component' => 'Magento_Ui/js/form/element/abstract',
			'template' => 'Magetop_Marketplace/form/field',
			'elementTmpl' => 'Magetop_Marketplace/form/element/input',
            'visible' => false,
        ];
        $recordActionDelete['arguments']['data']['config'] = [
            'label' => null,
            'componentType' => 'actionDelete',
            'fit' => true,
        ];

        return $this->arrayManager->set(
            'children',
            $record,
            [
                'container_link_title' => $this->getTitleColumn(),
                'container_link_price' => $this->getPriceColumn(),
                'container_file' => $this->getFileColumn(),
                'container_sample' => $this->getSampleColumn(),
                'is_shareable' => $this->getShareableColumn(),
                'max_downloads' => $this->getMaxDownloadsColumn(),
                'position' => $recordPosition,
                'action_delete' => $recordActionDelete,
            ]
        );
    }

    /**
     * @return array
     */
    protected function getTitleColumn()
    {
        $titleContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
			'template' => 'Magetop_Marketplace/group/group',
			'fieldTemplate' => 'Magetop_Marketplace/form/field',
            'label' => __('Title'),
            'dataScope' => '',
        ];
        $titleField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'title',
			'component' => 'Magento_Ui/js/form/element/abstract',
			'template' => 'Magetop_Marketplace/form/field',
			'elementTmpl' => 'Magetop_Marketplace/form/element/input',
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set('children/link_title', $titleContainer, $titleField);
    }

    /**
     * @return array
     */
    protected function getPriceColumn()
    {
        $priceContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
			'template' => 'Magetop_Marketplace/group/group',
			'fieldTemplate' => 'Magetop_Marketplace/form/field',
            'label' => __('Price'),
            'dataScope' => '',
        ];
        $priceField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Number::NAME,
            'component' => 'Magetop_Marketplace/js/components/price-handler',
            'dataScope' => 'price',
            'addbefore' => $this->locator->getStore()->getBaseCurrency()
                ->getCurrencySymbol(),
            'validation' => [
                'validate-zero-or-greater' => true,
            ],
			'template' => 'Magetop_Marketplace/form/field',
			'elementTmpl' => 'Magetop_Marketplace/form/element/input',
            'imports' => [
                'linksPurchasedSeparately' => '${$.provider}:data.product'
                    . '.links_purchased_separately',
                'useDefaultPrice' => '${$.parentName}.use_default_price:checked'
            ],
        ];

        return $this->arrayManager->set('children/link_price', $priceContainer, $priceField);
    }

    /**
     * @return array
     */
    protected function getFileColumn()
    {
        $fileContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
			'template' => 'Magetop_Marketplace/group/group',
			'fieldTemplate' => 'Magetop_Marketplace/form/field',			
			//'template' => 'Magetop_Marketplace/form/field',
            'label' => __('File'),
            'dataScope' => '',
        ];
        $fileTypeField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Select::NAME,
            'componentType' => Form\Field::NAME,
            'component' => 'Magetop_Marketplace/js/components/upload-type-handler',
			'elementTmpl' => 'Magetop_Marketplace/form/element/select',
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'type',
            'options' => $this->typeUpload->toOptionArray(),
			//'template' => 'Magetop_Marketplace/form/field',
            'typeFile' => 'links_file',
            'typeUrl' => 'link_url',
        ];
        $fileLinkUrl['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'link_url',
            'placeholder' => 'URL',
			'component' => 'Magento_Ui/js/form/element/abstract',
			'template' => 'Magetop_Marketplace/form/field',
			'elementTmpl' => 'Magetop_Marketplace/form/element/input',
            'validation' => [
                'required-entry' => true,
                'validate-url' => true,
            ],
        ];
        $fileUploader['arguments']['data']['config'] = [
            'formElement' => 'fileUploader',
            'componentType' => 'fileUploader',
            'component' => 'Magetop_Marketplace/js/components/file-uploader',
            'elementTmpl' => 'Magetop_Marketplace/components/file-uploader',
            'fileInputName' => 'links',
            'uploaderConfig' => [
                'url' => $this->urlBuilder->addSessionParam()->getUrl(
                    'marketplace/product_downloadable_file/upload',
                    ['type' => 'links', '_secure' => true]
                ),
            ],
			//'template' => 'Magetop_Marketplace/form/field',
            'dataScope' => 'file',
            'validation' => [
                'required-entry' => true,
            ],
        ];
		//echo '<pre>';print_r($fileLinkUrl);die;
        return $this->arrayManager->set(
            'children',
            $fileContainer,
            [
                'type' => $fileTypeField,
                'link_url' => $fileLinkUrl,
                'links_file' => $fileUploader
            ]
        );
    }

    /**
     * @return array
     */
    protected function getSampleColumn()
    {
        $sampleContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
			'template' => 'Magetop_Marketplace/group/group',
			'fieldTemplate' => 'Magetop_Marketplace/form/field',			
            'label' => __('Sample'),
            'dataScope' => '',
        ];
        $sampleTypeField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Select::NAME,
            'componentType' => Form\Field::NAME,
            'component' => 'Magetop_Marketplace/js/components/upload-type-handler',
			'elementTmpl' => 'Magetop_Marketplace/form/element/select',
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'sample.type',
            'options' => $this->typeUpload->toOptionArray(),
			//'template' => 'Magetop_Marketplace/form/field',
            'typeFile' => 'sample_file',
            'typeUrl' => 'sample_url',
        ];
        $sampleLinkUrl['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
			'component' => 'Magento_Ui/js/form/element/abstract',
			'template' => 'Magetop_Marketplace/form/field',
			'elementTmpl' => 'Magetop_Marketplace/form/element/input',
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'sample.url',
            'placeholder' => 'URL',
            'validation' => [
                'validate-url' => true,
            ],
        ];
        $sampleUploader['arguments']['data']['config'] = [
            'formElement' => 'fileUploader',
            'componentType' => 'fileUploader',
            'component' => 'Magetop_Marketplace/js/components/file-uploader',
			//'template' => 'Magetop_Marketplace/form/field',
            'elementTmpl' => 'Magetop_Marketplace/components/file-uploader',
            'fileInputName' => 'link_samples',
            'uploaderConfig' => [
                'url' => $this->urlBuilder->addSessionParam()->getUrl(
                    'marketplace/product_downloadable_file/upload',
                    ['type' => 'link_samples', '_secure' => true]
                ),
            ],
            'dataScope' => 'sample.file',
        ];

        return $this->arrayManager->set(
            'children',
            $sampleContainer,
            [
                'sample_type' => $sampleTypeField,
                'sample_url' => $sampleLinkUrl,
                'sample_file' => $sampleUploader,
            ]
        );
    }

    /**
     * @return array
     */
    protected function getShareableColumn()
    {
        $shareableField['arguments']['data']['config'] = [
            'label' => __('Shareable'),
            'formElement' => Form\Element\Select::NAME,
			'component' => 'Magento_Ui/js/form/element/select',
			'template' => 'Magetop_Marketplace/form/field',
			'elementTmpl' => 'Magetop_Marketplace/form/element/select',
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Number::NAME,
            'dataScope' => 'is_shareable',
            'options' => $this->shareable->toOptionArray(),
        ];
		//echo '<pre>';print_r($shareableField);die;
        return $shareableField;
    }

    /**
     * @return array
     */
    protected function getMaxDownloadsColumn()
    {
        $maxDownloadsContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
			'template' => 'Magetop_Marketplace/group/group',
			'fieldTemplate' => 'Magetop_Marketplace/form/field',			
			//'template' => 'Magetop_Marketplace/form/field',
            'label' => __('Max. Downloads'),
            'dataScope' => '',
        ];
        $numberOfDownloadsField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
			'component' => 'Magento_Ui/js/form/element/abstract',
			'template' => 'Magetop_Marketplace/form/field',
			'elementTmpl' => 'Magetop_Marketplace/form/element/input',
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Number::NAME,
            'dataScope' => 'number_of_downloads',
            'value' => 0,
            'validation' => [
                'validate-zero-or-greater' => true,
                'validate-number' => true,
            ],
        ];
        $isUnlimitedField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Checkbox::NAME,
            'componentType' => Form\Field::NAME,
			'component' => 'Magento_Ui/js/form/element/single-checkbox',
			'template' => 'Magetop_Marketplace/form/field',
            'dataType' => Form\Element\DataType\Number::NAME,
            'dataScope' => 'is_unlimited',
            'description' => __('Unlimited'),
            'valueMap' => [
                'false' => '0',
                'true' => '1',
            ],
            'exports' => [
                'checked' => '${$.parentName}.number_of_downloads:disabled',
            ],
        ];

        return $this->arrayManager->set(
            'children',
            $maxDownloadsContainer,
            [
                'number_of_downloads' => $numberOfDownloadsField,
                'is_unlimited' => $isUnlimitedField,
            ]
        );
    }
}
