<?php
namespace Kos\Design\Block\Adminhtml\Widget;
Class TextField extends \Magento\Backend\Block\Template{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
 
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_factoryElement;

    /**
     * @param Context           $context
     * @param Factory           $factoryElement
     * @param Config            $wysiwygConfig
     * @param array             $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        $data = []
    ) {
        $this->_factoryElement = $factoryElement;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $editor = $this->_factoryElement->create('editor', ['data' => $element->getData()])
            ->setLabel('')
            ->setWysiwyg(true)
            ->setConfig($this->getWysiwygConfig($element))
            ->setForceLoad(true)
            ->setForm($element->getForm());

        if($element->getRequired()){
            $editor->addClass('required-entry');
        }

        $element->setData('after_element_html', $editor->getElementHtml());

        return $element;
    }

    protected function getWysiwygConfig(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $config = $this->_wysiwygConfig->getConfig();
        $config->setData('add_variables', false);
        $config->setData('add_widgets', false);
        $config->addData(
            [
                'settings' => [
                    'mode' => 'exact',
                    'elements' => $element->getHtmlId(),
                    'theme_advanced_buttons1' => 'bold,underline,italic,|,justifyleft,justifycenter,justifyright,|,styleselect,fontselect,fontsizeselect,|,forecolor,backcolor,|,link,unlink,image,|,bullist,numlist,|,code',
                    'theme_advanced_buttons2' => null,
                    'theme_advanced_buttons3' => null,
                    'theme_advanced_buttons4' => null,
                ]
            ]
        );
        return $config;
    }
} 