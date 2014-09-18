<?php

class PageUtilities extends DataExtension {

    // Get name - will be wrapped in anchor HTML if $linkl isset
    public function GetNamedLink($name, $link) {
        $result = '';
        if (strlen($name) != 0) {
            $result = $name;
            if (strlen($link) != 0) {
                $result = "<a href=\"" . $link . "\">" . $result . "</a>";
            }
        }
        return $result;
    }

}
