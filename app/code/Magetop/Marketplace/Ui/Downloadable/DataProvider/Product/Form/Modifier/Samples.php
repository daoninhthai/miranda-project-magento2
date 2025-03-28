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
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\DynamicRows;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form;

/**
 * Class adds a grid with samples
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Samples extends AbstractModifier
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
     * @var Data\Samples
     */
    protected $samplesData;

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
     * @param Data\Samples $samplesData
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        TypeUpload $typeUpload,
        Data\Samples $samplesData
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
        $this->typeUpload = $typeUpload;
        $this->samplesData = $samplesData;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();

        $data[$model->getId()][self::DATA_SOURCE_DEFAULT]['samples_title'] = $this->samplesData->getSamplesTitle();
        $data[$model->getId()]['downloadable']['sample'] = $this->samplesData->getSamplesData();

        return $data;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $samplesPath = Composite::CHILDREN_PATH . '/' . Composite::CONTAINER_SAMPLES;
        $samplesContainer['arguments']['data']['config'] = [
            'additionalClasses' => 'admin__fieldset-section',
            'componentType' => Form\Fieldset::NAME,
            'label' => __('Samples'),
            'dataScope' => '',
            'visible' => $this->locator->getProduct()->getTypeId() === Type::TYPE_DOWNLOADABLE,
            'sortOrder' => 40,
        ];
        $samplesTitle['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
			'component' => 'Magento_Ui/js/form/element/abstract',
			'template' => 'Magetop_Marketplace/form/field',
			'elementTmpl' => 'Magetop_Marketplace/form/element/input',
            'dataType' => Form\Element\DataType\Text::NAME,
            'label' => __('Title'),
            'dataScope' => 'product.samples_title',
            'scopeLabel' => $this->storeManager->isSingleStoreMode() ? '' : '',
        ];
        // @codingStandardsIgnoreStart
        $informationSamples['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/html',
            'additionalClasses' => 'admin__fieldset-note',
            'content' => __('Alphanumeric, dash and underscore characters are recommended for filenames. Improper characters are replaced with \'_\'.'),
        ];
        // @codingStandardsIgnoreEnd

        $samplesContainer = $this->arrayManager->set(
            'children',
            $samplesContainer,
            [
                'samples_title' => $samplesTitle,
                'sample' => $this->getDynamicRows(),
                'information_samples' => $informationSamples,
            ]
        );

        return $this->arrayManager->set($samplesPath, $meta, $samplesContainer);
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
            'deleteProperty'=> 'is_delete',
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
            'dataScope' => '',
        ];
        $recordPosition['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
			'component' => 'Magento_Ui/js/form/element/abstract',
			'template' => 'Magetop_Marketplace/form/field',
			'elementTmpl' => 'Magetop_Marketplace/form/element/input',
            'dataType' => Form\Element\DataType\Number::NAME,
            'dataScope' => 'sort_order',
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
                'container_sample_title' => $this->getTitleColumn(),
                'container_sample' => $this->getSampleColumn(),
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
			'component' => 'Magento_Ui/js/form/element/abstract',
			'template' => 'Magetop_Marketplace/form/field',			
			'elementTmpl' => 'Magetop_Marketplace/form/element/input',
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'title',
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set('children/sample_title', $titleContainer, $titleField);
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
            'label' => __('File'),
            'dataScope' => '',
        ];
        $sampleType['arguments']['data']['config'] = [
            'formElement' => Form\Element\Select::NAME,
            'componentType' => Form\Field::NAME,
            'component' => 'Magetop_Marketplace/js/components/upload-type-handler',
			'elementTmpl' => 'Magetop_Marketplace/form/element/select',
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'type',
            'options' => $this->typeUpload->toOptionArray(),
            'typeFile' => 'sample_file',
            'typeUrl' => 'sample_url',
        ];
        $sampleUrl['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
			'component' => 'Magento_Ui/js/form/element/abstract',
			'template' => 'Magetop_Marketplace/form/field',				
			'elementTmpl' => 'Magetop_Marketplace/form/element/input',
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'sample_url',
            'placeholder' => 'URL',
            'validation' => [
                'required-entry' => true,
                'validate-url' => true,
            ],
        ];
        $sampleUploader['arguments']['data']['config'] = [
            'formElement' => 'fileUploader',
            'componentType' => 'fileUploader',
            'component' => 'Magetop_Marketplace/js/components/file-uploader',
            'elementTmpl' => 'Magetop_Marketplace/components/file-uploader',
            'fileInputName' => 'samples',
            'uploaderConfig' => [
                'url' => $this->urlBuilder->addSessionParam()->getUrl(
                    'marketplace/product_downloadable_file/upload',
                    ['type' => 'samples', '_secure' => true]
                ),
            ],
            'dataScope' => 'file',
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set(
            'children',
            $sampleContainer,
            [
                'sample_type' => $sampleType,
                'sample_url' => $sampleUrl,
                'sample_file' => $sampleUploader,
            ]
        );
    }
}
