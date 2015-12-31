<?php

class ExpressHomePage extends Page
{

    public static $icon     = "silverstripe-gdm-express/assets/images/sitetree_images/home.png";
    public $pageIcon = "silverstripe-gdm-express/assets/images/sitetree_images/home.png";
    public static $db       = array(
        'FeatureOneTitle'      => 'Varchar(255)',
        'FeatureOneCategory'   => "Enum('comments, group, news', 'comments')",
        'FeatureOneContent'    => 'HTMLText',
        'FeatureOneButtonText' => 'Varchar(255)',
        'FeatureTwoTitle'      => 'Varchar(255)',
        'FeatureTwoCategory'   => "Enum('comments, group, news', 'group')",
        'FeatureTwoContent'    => 'HTMLText',
        'FeatureTwoButtonText' => 'Varchar(255)',
        'LearnMoreButtonText'  => 'Varchar(255)'
    );
    public static $has_one  = array(
        'LearnMorePage'  => 'SiteTree',
        'FeatureOneLink' => 'SiteTree',
        'FeatureTwoLink' => 'SiteTree'
    );
    public static $has_many = array(
        'CarouselItems' => 'CarouselItem',
        'Quicklinks'    => 'Quicklink'
    );

    public function getCMSFields()
    {
        $fields            = parent::getCMSFields();
        // Main Content tab
        // Carousel tab
        $carouselItemsGrid = null;
        // Manay to many relations can only be established if we have an id. So put a place holder instead of a grid if this is a new object.
        if ($this->ID == 0) {
            $carouselItemsGrid = TextField::create("CarouselItems", "Carousel Items")->setDisabled(true)->setValue("Page must be saved once before adding Carousel Items.");
        } else {
            $carouselItemsGrid                = new GridField(
                    'CarouselItems', 'Carousel', $this->CarouselItems()->sort('Archived'), GridFieldConfig_RelationEditor::create()
            );
            $carouselItemsGridUploadComponent = new GridFieldBulkUpload("Image");
            $carouselItemsGridUploadComponent->setUfSetup("setFolderName", $this->ImageFolder("carousel"));
            $carouselItemsGrid->setModelClass('CarouselItem')->getConfig()->addComponent($carouselItemsGridUploadComponent)->addComponent(new GridFieldOrderableRows("SortID"));
        }
        $fields->addFieldToTab('Root.Carousel', $carouselItemsGrid);
        // Links
        $fields->addFieldToTab('Root.Links', new TreeDropdownField('LearnMorePageID', 'Page to link the "Learn More" button to:', 'SiteTree'));
        $fields->addFieldToTab('Root.Links', new TextField('LearnMoreButtonText', 'Text to display on the "Learn More" button:', 'SiteTree'));

        $quickLinksGrid       = new GridField(
                'Quicklinks', 'Quicklinks', $this->Quicklinks(), GridFieldConfig_RelationEditor::create());
        $quickLinksGrid->setModelClass('Quicklink');
        $quickLinksFieldGroup = FieldGroup::create($quickLinksGrid)->setTitle('Quick Links');
        $quickLinksFieldGroup->setName("QuicklinkGroup");
        $fields->addFieldToTab('Root.Links', $quickLinksFieldGroup);

        $fields->removeByName('Translations');
        $fields->removeByName('Import');

        $fields->addFieldToTab('Root.Features', ToggleCompositeField::create('FeatureOne', _t('SiteTree.FeatureOne', 'Feature One'), array(
                    new TextField('FeatureOneTitle', 'Title'),
                    new DropdownField('FeatureOneCategory', 'Category', singleton('ExpressHomePage')->dbObject('FeatureOneCategory')->enumValues()),
                    new HTMLEditorField('FeatureOneContent', 'Content'),
                    new TreeDropdownField('FeatureOneLinkID', 'Page to link to', 'SiteTree'),
                    new TextField('FeatureOneButtonText', 'Button text')
                        )
                )->setHeadingLevel(3)
        );

        $fields->addFieldToTab('Root.Features', ToggleCompositeField::create('FeatureTwo', _t('SiteTree.FeatureTwo', 'Feature Two'), array(
                    new TextField('FeatureTwoTitle', 'Title'),
                    new DropdownField('FeatureTwoCategory', 'Category', singleton('ExpressHomePage')->dbObject('FeatureTwoCategory')->enumValues()),
                    new HTMLEditorField('FeatureTwoContent', 'Content'),
                    new TreeDropdownField('FeatureTwoLinkID', 'Page to link to', 'SiteTree'),
                    new TextField('FeatureTwoButtonText', 'Button text')
                        )
                )->setHeadingLevel(3)
        );

        return $fields;
    }

    public function GetCategoryIcon($category)
    {
        $result = "";
        if ($category == "comments") {
            $result = "glyphicon glyphicon-comment";
        } elseif ($category == "group") {
            $result = "glyphicon glyphicon-user";
        } elseif ($category == "news") {
            $result = "glyphicon glyphicon-bullhorn";
        }
        return $result;
    }

    public function GetFeatureOneIcon()
    {
        return $this->GetCategoryIcon($this->FeatureOneCategory);
    }

    public function GetFeatureTwoIcon()
    {
        return $this->GetCategoryIcon($this->FeatureTwoCategory);
    }
}

class ExpressHomePage_Controller extends Page_Controller
{

    /**
     * @param int $amount The amount of items to provide.
     */
    public function getNewsItems($amount = 2)
    {
        $newsHolder = NewsHolder::get_one('NewsHolder');
        if ($newsHolder) {
            $controller = new NewsHolder_Controller($newsHolder);
            return $controller->getNewsItems($amount);
        }
    }

    public function getNews()
    {
        return DataObject::get_one("NewsHolder");
    }
}
