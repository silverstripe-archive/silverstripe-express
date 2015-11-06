<?php

class SSGDMExpressPageUtilities extends DataExtension {

    private $forceLeft  = false;
    private $forceRight = false;

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

    public function setHasLeft($value) {
        $this->forceLeft = $value;
    }

    public function setHasRight($value) {
        $this->forceRight = $value;
    }

    public function GetHasSide($side) {
        $widgetArea = null;
        $result     = false;
        if ($this->forceLeft && (strtolower($side) == "left")) {
            $result = true;
        } else if ($this->forceRight && (strtolower($side) == "right")) {
            $result = true;
        } else if ($this->owner->hasExtension("WidgetPage")) {
            if (strtolower($side) == "right") {
                $widgetArea = $this->owner->WidgetArea("RightSideBar");
            } else if (strtolower($side) == "left") {
                $widgetArea = $this->owner->WidgetArea("LeftSideBar");
            }
            $result = $widgetArea && $widgetArea->exists();
        }
        return $result;
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
        return $this->CleanStringForFolder($this->owner->MenuTitle) . ($subfolder ? "/" . $this->CleanStringForFolder($subfolder) : ""); 
    }

    private function CleanStringForFolder($string) {

        return
                // Make folder always lower case
                strtolower(
                    // Remove - and / from ends
                    trim(
                            // Remove duplicate -
                            preg_replace('/' . preg_quote("-") . '[' . preg_quote("-") . ']*/', "-",
                                // Replace any non alphanumeric characters with a -
                                preg_replace('/[^a-z0-9]/i', "-", $string)
                            ),
                        // 2nd arg to first trim
                    "-/")
                );
    }

    function FindChildrenOfType($objectType, $all = false, $limit = null) {
        $result   = new ArrayList();
        $children = $all ? $this->owner->AllChildren() : $this->owner->Children();
        foreach ($children as $child) {
            if (!is_null($limit) && $result->count() >= $limit) {
                break;
            }
            if ($child->ClassName == $objectType) {
                $result->add($child);
            }
            if ($child->hasMethod('FindChildrenOfType')) {
                $result->merge($child->FindChildrenOfType($objectType, $all, is_null($limit) ? null : $limit - $result->count()));
            }
        }
        return $result;
    }

    function GetAllChildrenOfType($objectType, $limit = null) {
        return $this->FindChildrenOfType($objectType, true, $limit);
    }

    function GetChildrenOfType($objectType, $limit = null) {
        return $this->FindChildrenOfType($objectType, false, $limit);
    }

    function GetFirstParentOfType($objectType) {
        $parent = $this->owner->Parent();
        while ($parent->ClassName !== $objectType && $parent->ParentID !== 0 && $parent->exists()) {
            $parent = $parent->Parent();
        }
        return $parent->ClassName === $objectType ? $parent : false;
    }

}
