<?php
class Conlabz_Useroptin_Model_Newsletter_Subscriber extends Mage_Newsletter_Model_Subscriber
{

    const XML_PATH_LOGGED_CONFIRM_EMAIL_TEMPLATE = 'newsletter/subscription/confirm_logged_email_template';

    /**
     * Subscribes by email
     *
     * @param string $email
     * @throws Exception
     * @return int
     */
    public function subscribe($email)
    {
        $this->loadByEmail($email);
        $customerSession = Mage::getSingleton('customer/session');

        if(!$this->getId()) {
            Mage::log(get_class($this)."---subscribe", null, "newsletter.log", true);
            Mage::log($this->randomSequence(), null, "newsletter.log", true);
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        $isConfirmNeed   = (Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG) == 1) ? true : false;
        $isOwnSubscribes = false;
        $ownerId = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email)
            ->getId();
        $isSubscribeOwnEmail = $customerSession->isLoggedIn() && $ownerId == $customerSession->getId();
        if ($isSubscribeOwnEmail){
            $isConfirmNeed   = (Mage::getStoreConfig(self::XML_PATH_LOGGED_CONFIRM_EMAIL_TEMPLATE) == 1) ? true : false;
        }
        $isConfirmNeed = true;
        $isSubscribeOwnEmail = true;

        if (!$this->getId() || $this->getStatus() == self::STATUS_UNSUBSCRIBED
            || $this->getStatus() == self::STATUS_NOT_ACTIVE
        ) {
            if ($isConfirmNeed === true) {
                // if user subscribes own login email - confirmation is not needed
                $isOwnSubscribes = $isSubscribeOwnEmail;
                if ($isOwnSubscribes == true){
                    if (Mage::getStoreConfig(self::XML_PATH_LOGGED_CONFIRM_EMAIL_TEMPLATE) == 1){
                    	$this->setStatus(self::STATUS_NOT_ACTIVE);
                    }else{
                    	$this->setStatus(self::STATUS_SUBSCRIBED);
                    }
                } else {
                    $this->setStatus(self::STATUS_NOT_ACTIVE);
                }
            } else {
                $this->setStatus(self::STATUS_SUBSCRIBED);
            }
            $this->setSubscriberEmail($email);
        }

        if ($isSubscribeOwnEmail) {
            $this->setStoreId($customerSession->getCustomer()->getStoreId());
            $this->setCustomerId($customerSession->getCustomerId());
        } else {
            $this->setStoreId(Mage::app()->getStore()->getId());
            $this->setCustomerId(0);
        }

        $this->setIsStatusChanged(true);

        try {
            $this->save();
            if ($isConfirmNeed === true
                && $isOwnSubscribes === false
            ) {
                $this->sendConfirmationRequestEmail();
            } else {
            	if (Mage::getStoreConfig(self::XML_PATH_LOGGED_CONFIRM_EMAIL_TEMPLATE) == 1){
                	$this->sendConfirmationRequestEmail();
            	}else{
            		$this->sendConfirmationSuccessEmail();
            	}
            }

            return $this->getStatus();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}
