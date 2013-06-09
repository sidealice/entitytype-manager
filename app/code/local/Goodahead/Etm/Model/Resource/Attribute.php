<?php

class Goodahead_Etm_Model_Resource_Attribute extends Mage_Eav_Model_Resource_Entity_Attribute
{
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->joinInner(
            array('etm_entity_type' => $this->getTable('goodahead_etm/eav_entity_type')),
            $this->getMainTable() . '.entity_type_id = etm_entity_type.entity_type_id'
        );
        $select->joinInner(
            array('etm_attribute' => $this->getTable('goodahead_etm/eav_attribute')),
            $this->getMainTable() . '.attribute_id = etm_attribute.attribute_id'
        );

        return $select;
    }

    /**
     * @param Goodahead_Etm_Model_Attribute|Mage_Core_Model_Abstract $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->isObjectNew()) {
            /* @var $helper Mage_Catalog_Helper_Product */
            $helper = Mage::helper('catalog/product');

            $object->setIsUserDefined(1);
            $object->setSourceModel($helper->getAttributeSourceModelByInputType($object->getFrontendInput()));
            $object->setBackendModel($helper->getAttributeBackendModelByInputType($object->getFrontendInput()));
            $object->setBackendType($object->getBackendTypeByInput($object->getFrontendInput()));
        }

        return parent::_beforeSave($object);
    }

    /**
     * @param Goodahead_Etm_Model_Attribute|Mage_Core_Model_Abstract $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute|void
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);

        $tableName = $this->getTable('goodahead_etm/eav_attribute');
        $this->_getWriteAdapter()->insertOnDuplicate($tableName, $this->_prepareDataForTable($object, $tableName));
    }

    /**
     * Delete attributes with given ids
     *
     * @param array $attributeIds
     * @return $this
     */
    public function deleteAttributes(array $attributeIds)
    {
        /** @var Goodahead_Etm_Model_Resource_Attribute_Collection $collection */
        $collection = Mage::getResourceModel('goodahead_etm/attribute_collection');
        $collection->addFieldToFilter('main_table.attribute_id', $attributeIds);

        // we should delete only attributes which belong to our custom entity types
        $attributeIdsToDelete = $collection->getAllIds();

        $this->_getWriteAdapter()->delete($this->getMainTable(),
            array('attribute_id IN(?)' => $attributeIdsToDelete)
        );

        return $this;
    }
}
