<?php

class ContactUsPage extends UserDefinedForm {

    static $icon       = "silverstripe-gdm-express/assests/images/sitetree_images/contact.png";
    public $pageIcon   = "silverstripe-gdm-express/assests/images/sitetree_images/contact.png";
    private static $db = array(
        'MapEmbedHTML' => 'HTMLText'
    );

    public function getCMSFields() {
        $fields            = parent::getCMSFields();
        $MapEmbedHTMLField = new TextareaField('MapEmbedHTML');
        $fields->addFieldToTab('Root.Main', $MapEmbedHTMLField, 'Content');
        return $fields;
    }

    function onBeforeWrite() {
        if ($this->MapEmbedHTML) {
            $this->MapEmbedHTML = preg_replace('/\s(width|style|height|frameborder)=".*?"/i', '', $this->MapEmbedHTML);
        }

        return parent::onBeforeWrite();
    }

}

class ContactUsPage_Controller extends UserDefinedForm_Controller {

}
