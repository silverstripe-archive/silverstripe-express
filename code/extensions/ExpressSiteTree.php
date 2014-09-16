<?php

class ExpressSiteTree extends SiteTreeExtension {
	static $icon = 'gdm-ss-express/assests/images/sitetree_images/page.png';
	
	public function MenuChildren() {
		return $this->owner->Children()->filter('ShowInMenus', true);
	}
}