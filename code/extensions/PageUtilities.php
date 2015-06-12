<?php

class PageUtilities extends DataExtension {

    // Get name - will be wrapped in anchor HTML if $linkl isset
    public function GetNamedLink($name, $link, $target = null) {
        $result = '';
        if (strlen($name) != 0) {
            $result = $name;
            if (strlen($link) != 0) {
                $result = "<a href=\"" . $link . "\"" . (is_null($target) ? "" : " target=\"" . $target . "\"") . ">" . $result . "</a>";
            }
        }
        return $result;
    }

    public function GetHasSide($side) {
        $widgetArea = null;
        if ($this->owner->hasExtension("WidgetPage")) {
            if (strtolower($side) == "right") {
                $widgetArea = $this->owner->WidgetArea("RightSideBar");
            } else if (strtolower($side) == "left") {
                $widgetArea = $this->owner->WidgetArea("LeftSideBar");
            }
        }
        return $widgetArea && $widgetArea->exists();
    }

    public function GetLeftCssClass() {
        $cssClass = "side left";
        if ($this->GetHasSide("right")) {
            $cssClass .= " has-right";
        }
        return $cssClass;
    }

    public function GetCenterCssClass() {
        $cssClass = "center";
        if ($this->GetHasSide("right")) {
            $cssClass .= " has-right";
        }
        if ($this->GetHasSide("left")) {
            $cssClass .= " has-left";
        }
        return $cssClass;
    }

    public function GetRightCssClass() {
        $cssClass = "side right";
        if ($this->GetHasSide("left")) {
            $cssClass .= " has-left";
        }
        return $cssClass;
    }

    public function ImageFolder($subfolder = null) {
        return $this->CleanStringForFolder($this->owner->MenuTitle) . ($subfolder ? "/" . $this->CleanStringForFolder($subfolder) : ""); //strtolower($name) . ($subfolder ? "/" . rtrim($subfolder, "/\\") : "");
    }

    private function CleanStringForFolder($string) {

        return
                // Make folder always lower case
                strtolower(
                // Remove - and / from ends
                trim(
                        // Remove duplicate -
                        preg_replace('/' . preg_quote("-") . '[' . preg_quote("-") . ']*/', "-",
                                // Replace and non alphanumeric characters with a -
                                preg_replace('/[^a-z0-9]/i', "-", $string)),
                        // 2nd arg to firt trim
                        "-/"));
    }

    function GetAllChildrenOfType($objectType) {
        $result = new ArrayList();

        foreach ($this->owner->Children() as $child) {
            if ($child->ClassName == $objectType) {
                $result->add($child);
            }
            if ($child->hasMethod('GetAllChildrenOfType')) {
                $result->merge($child->GetAllChildrenOfType($objectType));
            }
        }
        return $result;
    }

    function GetFirstParentOfType($objectType) {
        $parent = $this->owner->Parent();
        while ($parent->ClassName !== $objectType && $parent->ParentID !== 0) {
            $parent = $parent->Parent();
        }
        return $parent->ClassName === $objectType ? $parent : false;
    }

}
