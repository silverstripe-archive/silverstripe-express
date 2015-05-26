<?php

class ExpressSiteTree extends SiteTreeExtension {

    static $icon = 'silverstripe-gdm-express/assets/images/sitetree_images/page.png';

    public function MenuChildren() {
        return $this->owner->Children()->filter('ShowInMenus', true);
    }

}
