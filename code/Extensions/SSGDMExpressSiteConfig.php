<?php

/**
 * Adds new global settings.
 */
class SSGDMExpressSiteConfig extends DataExtension
{

    public static $db        = array(
        'GACode'                 => 'Varchar(16)',
        'FooterLogoLink'         => 'Varchar(255)',
        'FooterLogoDescription'  => 'Varchar(255)',
        'Copyright'              => 'Varchar(255)',
        'HeaderLogoOffsetY'      => 'Int',
        'HeaderLogoOffsetX'      => 'Int',
        'HeaderLogoOffsetSmallY' => 'Int',
        'HeaderLogoOffsetSmallX' => 'Int',
        'ShowTitleInHeader'      => 'Boolean',
    );
    public static $has_one   = array(
        'Logo'       => 'Image',
        'LogoMobile' => 'Image',
        'FooterLogo' => 'Image'
    );
    public static $many_many = array(
        'FooterLinks' => 'SiteTree',
    );

    public function updateCMSFields(FieldList $fields)
    {
        // Main
        $fields->addFieldToTab('Root.Main', $gaCode          = new TextField('GACode', 'Google Analytics account'));
        $gaCode->setRightTitle('Account number to be used all across the site (in the format <strong>UA-XXXXX-X</strong>)');
        $fields->addFieldToTab('Root.Main', $showTitle       = new CheckboxField('ShowTitleInHeader', 'Show title in header'), 'Tagline');
        /* @var $logoField UploadField */
        $logoField       = new UploadField('Logo', 'Large logo, to appear in the header.');
        $logoField->setAllowedFileCategories('image');
        $logoField->setConfig('allowedMaxFileNumber', 1);
        $mobileLogoField = new UploadField('LogoMobile', 'Mobile logo, to appear in the header.');
        $mobileLogoField->setAllowedFileCategories('image');
        $mobileLogoField->setConfig('allowedMaxFileNumber', 1);
        $fields->addFieldToTab('Root.Main', $logoField);
        $fields->addFieldToTab('Root.Main', $this->getLogoOffSetField());
        $fields->addFieldToTab('Root.Main', $mobileLogoField);
        $fields->addFieldToTab('Root.Main', $this->getMobileLogoOffSetField());


        //Footer
        $fields->addFieldToTab('Root.Footer', $footerLogoField = new UploadField('FooterLogo', 'Footer logo, to appear in the bottom right.'));
        $footerLogoField->setAllowedFileCategories('image');
        $footerLogoField->setConfig('allowedMaxFileNumber', 1);
        $fields->addFieldToTab('Root.Footer', $footerLink      = new TextField('FooterLogoLink', 'Footer Logo link'));
        $footerLink->setRightTitle('Please include the protocol (ie, http:// or https://) unless it is an internal link.');
        $fields->addFieldToTab('Root.Footer', new TextField('FooterLogoDescription', 'Footer Logo description'));
        $fields->addFieldToTab('Root.Footer', new TreeMultiselectField('FooterLinks', 'Footer Links', 'SiteTree'));
        $fields->addFieldToTab('Root.Footer', new TextField('Copyright', 'Copyright'));
    }

    private function getLogoOffSetField()
    {
        $headerLogoOffsetYField = new NumericField('HeaderLogoOffsetY', 'Top Offset');
        $headerLogoOffsetXField = new NumericField('HeaderLogoOffsetX', 'Left Offset');
        return $this->createTwoColumnField('logoOffSetField', "Logo offset", $headerLogoOffsetYField, $headerLogoOffsetXField);
    }

    private function getMobileLogoOffSetField()
    {
        $headerLogoOffsetSmallYField = new NumericField('HeaderLogoOffsetSmallY', 'Top Offset');
        $headerLogoOffsetSmallXField = new NumericField('HeaderLogoOffsetSmallX', 'Left Offset');
        return $this->createTwoColumnField('mobileLogoOffSetField', "Mobile logo offset", $headerLogoOffsetSmallYField, $headerLogoOffsetSmallXField);
    }

    public function createTwoColumnField($id, $label, $field1, $field2)
    {
        $twoColumnField = new CompositeField(
                new LiteralField($id . '-wrap', '
<div id="' . $id . '-wrap" class="field fieldgroup">
    <label class="left" for="HeaderLogoOffsetLarge">' . $label . '</label>
    <div class="middleColumn">
        <div id="' . $id . '[X]" class="fieldgroup-field nolabel" style="padding-top: 0; margin-top: -7px;">
            <div class="nolabel">
                '), $field1, new LiteralField('MobileLogoOffSetField2', '
  </div>
        </div>
        <div id="' . $id . '[Y]" class="fieldgroup-field nolabel" style="padding-top: 0; margin-top: -7px;">
            <div class="nolabel">
                '), $field2, new LiteralField('MobileLogoOffSetField3', '
   </div>
        </div>
    </div>
</div>
')
        );
        $twoColumnField->addExtraClass('field');
        $twoColumnField->setName($id);
        return $twoColumnField;
    }
}
