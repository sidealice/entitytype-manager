<?php

class Goodahead_Etm_Block_Adminhtml_Entity_Types_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare layout.
     * Add files to use dialog windows
     *
     * @return Mage_Adminhtml_Block_System_Email_Template_Edit_Form
     */
    protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->addItem('js', 'prototype/window.js')
                ->addItem('js_css', 'prototype/windows/themes/default.css')
                ->addCss('lib/prototype/windows/themes/magento.css')
                ->addItem('js', 'mage/adminhtml/variables.js');
        }
        return parent::_prepareLayout();
    }



    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'            => 'edit_form',
            'action'        => $this->getUrl('*/*/save'),
            'method'        => 'post',
            'enctype'       => 'multipart/form-data',
            'use_container' => true
        ));

        $entityType = Mage::registry('etm_entity_type');

        $fieldSet = $form->addFieldset('entity_type_data', array(
            'class' => 'fieldset-wide',
        ));
        $validateClass = sprintf('required-entry validate-code validate-length maximum-length-%d',
            Mage_Eav_Model_Entity_Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );

        $fieldSet->addField('entity_type_code', 'text', array(
            'label'     => Mage::helper('goodahead_etm')->__("Entity Type Code"),
            'name'      => 'entity_type_code',
            'class'     => $validateClass,
            'required'  => true,
        ));

        $fieldSet->addField('entity_type_name', 'text', array(
            'label'     => Mage::helper('goodahead_etm')->__("Entity Type Name"),
            'name'      => 'entity_type_name',
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldSet->addField('entity_type_root_template', 'select', array(
            'label'     => Mage::helper('goodahead_etm')->__("Entity Type Root Template"),
            'name'      => 'entity_type_root_template',
            'values'    => Mage::getSingleton('page/source_layout')->toOptionArray(),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldSet->addField('entity_type_layout_xml', 'textarea', array(
            'label'     => Mage::helper('goodahead_etm')->__("Layout XML"),
            'name'      => 'entity_type_layout_xml',
            'style'     => 'height:7em',
            'required'  => false,
        ));



        $fieldSet->addField('variables', 'hidden', array(
            'name' => 'variables',
        ));

        $insertVariableButton = $this->getLayout()
            ->createBlock('adminhtml/widget_button', '', array(
                'type' => 'button',
                'label' => Mage::helper('adminhtml')->__('Insert Variable...'),
                'onclick' => 'openVariablesWindow();return false;'
            ));

        $fieldSet->addField('insert_variable', 'note', array(
            'text' => $insertVariableButton->toHtml()
        ));

        $fieldSet->addField('entity_type_content', 'textarea', array(
            'label'     => Mage::helper('goodahead_etm')->__("Content"),
            'name'      => 'entity_type_content',
            'style'     => 'height:24em',
            'required'  => false,
        ));

        if ($entityType->getId()) {
            $form->addField('entity_type_id', 'hidden', array(
                'name' => 'entity_type_id',
            ));

            $entityTypeAttributes = Mage::getModel('goodahead_etm/source_attribute')->toOptionsArray($entityType, true);

            $fieldSet->addField('default_attribute_id', 'select', array(
                'label'     => Mage::helper('goodahead_etm')->__("Default Attribute"),
                'name'      => 'default_attribute_id',
                'required'  => false,
                'values'    => $entityTypeAttributes,
                'note'      => Mage::helper('goodahead_etm')->__("This attribute is used to display entity label"),
            ), 'entity_type_name');

            $form->getElement('entity_type_code')->setReadonly('readonly');
            $form->getElement('entity_type_code')->setDisabled(1);
            $form->setValues($entityType->getData());
            $form->getElement('variables')->setValue(Zend_Json::encode($this->getVariables()));
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Retrieve variables to insert into content
     *
     * @return array
     */
    public function getVariables()
    {
        $entityType = Mage::registry('etm_entity_type');

        $visibleAttribute = Mage::helper('goodahead_etm')->getVisibleAttributes($entityType->getId());
        $variables = array();

        foreach($visibleAttribute as $attributeCode => $attribute) {
            $variables[] = array(
                'value' => $attributeCode,
                'label' => $attribute->getAttributeName()
            );
        }



        $optionArray = array();
        foreach ($variables as $variable) {
            $optionArray[] = array(
                'value' => '{{var ' . $variable['value'] . '}}',
                'label' => Mage::helper('goodahead_etm')->__('%s', $variable['label'])
            );
        }
        if ($optionArray) {
            $optionArray = array(
                'label' => Mage::helper('core')->__('Entity Attributes'),
                'value' => $optionArray
            );
        }


        return $optionArray;
    }





}
