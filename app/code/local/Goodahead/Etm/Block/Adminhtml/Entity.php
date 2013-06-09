<?php
class Goodahead_Etm_Block_Adminhtml_Entity extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'goodahead_etm';
        $this->_controller = 'adminhtml_entity';

        $entityType = Mage::registry('etm_entity_type');

        $this->_headerText = Mage::helper('goodahead_etm')->__(
            'Manage %s entities', $entityType->getEntityTypeName()
        );

        $this->_backButtonLabel = Mage::helper('goodahead_etm')->__('Back to Entity Types List');
        $this->_addBackButton();

        parent::__construct();

        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/etm_entityType') . '\')');

        $this->_updateButton('add', 'label', Mage::helper('goodahead_etm')->__('Add New Entity'));
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new', array('_current' => true));
    }
}
