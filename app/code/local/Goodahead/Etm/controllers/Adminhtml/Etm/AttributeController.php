<?php

class Goodahead_Etm_Adminhtml_Etm_AttributeController extends Goodahead_Etm_Controller_Adminhtml
{
    /**
     * Entity Type Manager index page
     */
    public function indexAction()
    {
        try {
            $this->_initEntityType();

            $this->_initAction($this->__('Manage Attributes'));
            $this->renderLayout();
        } catch (Goodahead_Etm_Exception $e) {
            Mage::logException($e);
            $this->_forward('no_route');
        }
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'edit':
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_attributes/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_attributes/delete');
                break;
            case 'index':
            default:
                return Mage::getSingleton('admin/session')->isAllowed('goodahead_etm/manage_attributes');
                break;
        }
    }

    /**
     * Grid ajax action
     */
    public function gridAction()
    {
        try {
            $this->_initEntityType();

            $this->loadLayout();
            $this->renderLayout();
        } catch (Goodahead_Etm_Exception $e) {
            Mage::logException($e);
            $this->_forward('no_route');
        }
    }

    /**
     * Delete attribute action
     *
     * @return void
     */
    public function deleteAction()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        if ($attributeId) {
            try {
                $this->_initEntityType();

                /** @var Goodahead_Etm_Model_Attribute $attributeModel */
                $attributeModel = Mage::getSingleton('goodahead_etm/attribute');
                $attributeModel->load($attributeId);
                if (!$attributeModel->getId()) {
                    Mage::throwException(Mage::helper('goodahead_etm')->__('Unable to find attribute.'));
                }
                $attributeModel->delete();

                $this->_getSession()->addSuccess(
                    Mage::helper('goodahead_etm')->__(
                        "Attribute with code '%s' has been deleted.",
                        $attributeModel->getAttributeCode()
                    )
                );
            } catch (Goodahead_Etm_Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('You are not allowed to delete non-custom attribute')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('An error occurred while deleting attribute.')
                );
            }
        }

        // go to grid
        $arguments    = array();
        $entityTypeId = $this->getRequest()->getParam('entity_type_id');
        if (!empty($entityTypeId)) {
            $arguments = array('entity_type_id' => $entityTypeId);
        }
        $this->_redirect('*/*/', $arguments);
    }

    /**
     * Mass delete attributes action
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $attributeIds = $this->getRequest()
            ->getParam('attribute_ids');

        if (!is_array($attributeIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('Please select attribute(s) to delete.')
            );
        } else {
            try {
                $this->_initEntityType();

                /** @var Goodahead_Etm_Model_Attribute $attributeModel */
                $attributeModel = Mage::getSingleton('goodahead_etm/attribute');
                $attributeModel->deleteAttributes($attributeIds);

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('goodahead_etm')->__(
                        '%d attribute(s) were deleted.', count($attributeIds)
                    )
                );
            } catch (Goodahead_Etm_Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('You are not allowed to delete non-custom attributes')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('An error occurred while deleting attributes.')
                );
            }
        }

        // go to grid
        $arguments = array();
        $entityTypeId = $this->getRequest()->getParam('entity_type_id');
        if (!empty($entityTypeId)) {
            $arguments = array('entity_type_id' => $this->getRequest()->getParam('entity_type_id'));
        }
        $this->_redirect('*/*/', $arguments);
    }

    /**
     * Create new attribute
     *
     * @return void
     */
    public function newAction()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * Edit attribute
     *
     * @return void
     */
    public function editAction()
    {
        try {
            $this->_initEntityType();

            /** @var Goodahead_Etm_Model_Attribute $attributeModel */
            $attributeModel = Mage::getSingleton('goodahead_etm/attribute');

            $attributeId = $this->getRequest()->getParam('attribute_id');
            if ($attributeId) {
                $attributeModel->load($attributeId);

                if (!$attributeModel->getId()) {
                    Mage::throwException(Mage::helper('goodahead_etm')->__('Unable to find attribute.'));
                }

                $this->_initAction($this->__("Edit Attribute with Code '%s'", $attributeModel->getAttributeCode()));
            } else {
                $this->_initAction($this->__('New Attribute'));
            }

            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $attributeModel->addData($data);
            }

            Mage::register('etm_attribute', $attributeModel);

            $this->renderLayout();
            return;
        } catch (Goodahead_Etm_Exception $e) {
            $this->_getSession()->addException($e,
                Mage::helper('goodahead_etm')->__('You are not allowed to edit non-custom attribute')
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e,
                Mage::helper('goodahead_etm')->__('An error occurred while opening attribute.')
            );
        }

        // go to grid
        $arguments    = array();
        $entityTypeId = $this->getRequest()->getParam('entity_type_id');
        if (!empty($entityTypeId)) {
            $arguments = array('entity_type_id' => $entityTypeId);
        }
        $this->_redirect('*/*/', $arguments);
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        $redirectPath   = '*/*';
        $redirectParams = array();

        // check if data sent
        $data         = $this->getRequest()->getPost();
        $entityTypeId = $this->getRequest()->getParam('entity_type_id');
        if (!empty($entityTypeId)) {
            $redirectParams = array('entity_type_id' => $entityTypeId);
            $data['entity_type_id'] = $entityTypeId;
        }
        if ($data) {
           /** @var Goodahead_Etm_Model_Attribute $attributeModel */
            $attributeModel = Mage::getSingleton('goodahead_etm/attribute');

            // if news item exists, try to load it
            $attributeId = $this->getRequest()->getParam('attribute_id');
            if ($attributeId) {
                $attributeModel->load($attributeId);
            }

            $attributeModel->addData($data);

            try {
                $hasError = false;
                $this->_initEntityType();

                $attributeModel->save();
                $this->_getSession()->addSuccess(
                    Mage::helper('goodahead_etm')->__('Attribute has been saved.')
                );

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $redirectPath   = '*/*/edit';
                    $redirectParams['attribute_id'] = $attributeModel->getId();
                }
            } catch (Goodahead_Etm_Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('You are not allowed to edit non-custom attribute')
                );
            } catch (Mage_Core_Exception $e) {
                $hasError = true;
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('goodahead_etm')->__('An error occurred while saving attribute.')
                );
            }

            if ($hasError) {
                $this->_getSession()->setFormData($data);
                $redirectPath = '*/*/edit';
                if ($attributeModel->getId()) {
                    $redirectParams['attribute_id'] = $attributeId;
                }
            }
        }

        $this->_redirect($redirectPath, $redirectParams);
    }
}
