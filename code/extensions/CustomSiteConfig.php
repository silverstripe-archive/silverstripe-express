<?php

/**
 * Adds new global settings.
 */
class CustomSiteConfig extends DataExtension {

    static $db        = array(
        'GACode'                => 'Varchar(16)',
//        'FacebookURL'           => 'Varchar(256)', // multitude of ways to link to Facebook accounts, best to leave it open.
//        'TwitterUsername'       => 'Varchar(16)', // max length of Twitter username 15
//        'AddThisProfileID'      => 'Varchar(32)',
        'FooterLogoLink'        => 'Varchar(255)',
        'FooterLogoDescription' => 'Varchar(255)',
        'Copyright'             => 'Varchar(255)',
    );
    static $has_one   = array(
        'Logo'       => 'Image',
        'FooterLogo' => 'Image'
    );
    static $many_many = array(
        'FooterLinks' => 'SiteTree',
    );

    function updateCMSFields(FieldList $fields) {
        // Main
        $fields->addFieldToTab('Root.Main', $gaCode    = new TextField('GACode', 'Google Analytics account'));
        $gaCode->setRightTitle('Account number to be used all across the site (in the format <strong>UA-XXXXX-X</strong>)');
        $fields->addFieldToTab('Root.Main', $logoField = new UploadField('Logo', 'Logo, to appear in the top left.'));
        /* @var $logoField UploadField */
        $logoField->setAllowedFileCategories('image');
        $logoField->setConfig('allowedMaxFileNumber', 1);

        // Social Media
        $fields->addFieldToTab('Root.SocialMedia', new CheckboxSetField("SharePagesOn", "Share pages with", SocialNetworkLink::create()->dbObject('Network')->enumValues()));

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

}
