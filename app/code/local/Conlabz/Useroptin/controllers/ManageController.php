<?php
include "Mage/Newsletter/controllers/ManageController.php";
class Conlabz_Useroptin_ManageController extends Mage_Newsletter_ManageController {
	
	public function saveAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('customer/account/');
        }
        try {
        
        	if (Mage::getStoreConfig("newsletter/subscription/confirm_logged_email_template") == 1){
             
             	$status = Mage::getModel("newsletter/subscriber")->subscribe(Mage::getSingleton('customer/session')->getCustomer()->getEmail());
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    Mage::getSingleton('customer/session')->addSuccess($this->__('Confirmation request has been sent.'));
                }
                else {
                    Mage::getSingleton('customer/session')->addSuccess($this->__('Thank you for your subscription.'));
                }
            	
            }else{       
            	Mage::getSingleton('customer/session')->getCustomer()
            		->setStoreId(Mage::app()->getStore()->getId())
            		->setIsSubscribed((boolean)$this->getRequest()->getParam('is_subscribed', false))
            		->save();
            	if ((boolean)$this->getRequest()->getParam('is_subscribed', false)) {
                	Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been saved.'));
            	} else {
                	Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been removed.'));
            	}
            }
            
        }
        catch (Exception $e) {
	        Mage::getSingleton('customer/session')->addError($e->getMessage());
            Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
        }
        $this->_redirect('customer/account/');
    }

   
}
