<?php

class ExpressSiteTree extends SiteTreeExtension {

	static $icon = 'silverstripe-gdm-express/assets/images/sitetree_images/page.png';
	private $menuChildren;

	public function MenuChildren() {
		if (!$this->menuChildren) {
			$this->menuChildren = $this->owner->Children()->filter('ShowInMenus', true);
		}
		return $this->menuChildren;
	}

}
