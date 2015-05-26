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
//        Main menuclass="debug"> "$widgetArea ' . $side . ' exists "' . PHP_EOL . print_r($widgetArea && $widgetArea->exists() ? "1" : "0", true) . PHP_EOL . '</pre>';
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
        $name = preg_replace('/[^a-z0-9]/i', "-", $this->owner->MenuTitle);
        $name = preg_replace('/' . preg_quote("-") . '[' . preg_quote("-") . ']*/', "-", $name);
        $name = trim($name, "-");
        return strtolower($name) . ($subfolder ? "/" . rtrim($subfolder, "/\\") : "");
    }

}
