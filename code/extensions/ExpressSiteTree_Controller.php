<?php

class ExpressSiteTree_Controller extends Extension {

    public static $allowed_actions = array();

    function onAfterInit() {
        $themeDir    = SSViewer::get_theme_folder();
        $scripts     = array();
        $styles      = array();
        $printStyles = array();

        // Add the combined scripts.
        if (method_exists($this->owner, 'getScriptOverrides')) {
            $scripts = $this->owner->getScriptOverrides();
        } else if (method_exists($this->owner, 'getScriptIncludes')) {
            $scripts = array_unique(array_merge($scripts, $this->owner->getScriptIncludes()));
        }

        // Add the combined styles.
        if (method_exists($this->owner, 'getStyleOverrides')) {
            $styles = $this->owner->getStyleOverrides();
        } else if (method_exists($this->owner, 'getStyleIncludes')) {
            $styles = array_unique(array_merge($styles, $this->owner->getStyleIncludes()));
        }

        // Print styles
        if (method_exists($this->owner, 'getPrintStyleOverrides')) {
            $printStyles = $this->owner->getPrintStyleOverrides();
        } else if (method_exists($this->owner, 'getPrintStyleIncludes')) {
            $printStyles = array_unique(array_merge($printStyles, $this->owner->getPrintStyleIncludes()));
        }

        if (Director::isDev()) {
            foreach ($scripts as $script) {
                Requirements::javascript($script);
            }
            foreach ($styles as $style) {
                Requirements::css($style);
            }
            foreach ($styles as $printStyle) {
                Requirements::css($printStyle, 'print');
            }
        } else {
            Requirements::combine_files('scripts.js', $scripts);
            Requirements::combine_files('styles.css', $styles);
            Requirements::combine_files('print.css', $printStyles, 'print');
        }

        // Extra folder to keep the relative paths consistent when combining.
        Requirements::set_combined_files_folder($themeDir . '/combinedfiles');
    }

    /**
     * Give external links the external class, and affix size and type prefixes to files.
     *
     * @return String
     */
    function Content() {
        $content = $this->owner->Content;

        // Internal links.
        $matches = array();
        preg_match_all('/<a.*href="\[file_link,id=([0-9]+)\].*".*>.*<\/a>/U', $content, $matches);

        for ($i = 0; $i < count($matches[0]); $i++) {
            $file = DataObject::get_by_id('File', $matches[1][$i]);
            if ($file) {
                $size    = $file->getSize();
                $ext     = strtoupper($file->getExtension());
                $newLink = $matches[0][$i] . "<span class='fileExt'> [$ext, $size]</span>";
                $content = str_replace($matches[0][$i], $newLink, $content);
            }
        }

        // and now external links
        $pattern     = '/<a href=\"(h[^\"]*)\">(.*)<\/a>/iU';
        $replacement = '<a href="$1" class="external">$2</a>';
        $result      = preg_replace($pattern, $replacement, $content, -1);
        return $result;
    }

    /**
     * Overrides the ContentControllerSearchExtension and adds snippets to results.
     *
     * @param array $data
     * @param SearchForm $form
     * @param SS_HTTPRequest $request
     * @return HTMLText
     */
    function results($data, $form, $request) {
        $result  = null;
        $results = $form->getResults();
        $query   = $form->getSearchQuery();

        // Add context summaries based on the queries.
        foreach ($results as $result) {
            $contextualTitle         = new Text();
            $contextualTitle->setValue($result->MenuTitle ? $result->MenuTitle : $result->Title);
            $result->ContextualTitle = $contextualTitle->ContextSummary(300, $query);

            if (!$result->Content && $result->ClassName == 'File') {
                // Fake some content for the files.
                $result->ContextualContent = "A file named \"$result->Name\" ($result->Size).";
            } else {
                // /(.?)\[embed(.*?)\](.+?)\[\/\s*embed\s*\](.?)/gi
                $valueField                = $result->obj('Content');
                $valueField->setValue(ShortcodeParser::get_active()->parse($valueField->getValue()));
                $result->ContextualContent = $valueField->ContextSummary(300, $query);
            }
        }

        $rssLink = HTTP::setGetVar('rss', '1');

        // Render the result.
        $context = array(
            'Results' => $results,
            'Query'   => $query,
            'Title'   => _t('SearchForm.SearchResults', 'Search Results'),
            'RSSLink' => $rssLink
        );

        // Choose the delivery method - rss or html.
        if (!$this->owner->request->getVar('rss')) {
            // Add RSS feed to normal search.
            RSSFeed::linkToFeed($rssLink, "Search results for query \"$query\".");

            $result = $this->owner->customise($context)->renderWith(array('Page_results', 'Page'));
        } else {
            // De-paginate and reorder. Sort-by-relevancy doesn't make sense in RSS context.
            $fullList = $results->getList()->sort('LastEdited', 'DESC');

            // Get some descriptive strings
            $siteName    = SiteConfig::current_site_config()->Title;
            $siteTagline = SiteConfig::current_site_config()->Tagline;
            if ($siteName) {
                $title = "$siteName search results for query \"$query\".";
            } else {
                $title = "Search results for query \"$query\".";
            }

            // Generate the feed content.
            $rss    = new RSSFeed($fullList, $this->owner->request->getURL(), $title, $siteTagline, "Title", "ContextualContent", null);
            $rss->setTemplate('Page_results_rss');
            $result = $rss->outputToBrowser();
        }
        return $result;
    }

    public function themedJavascript($name, $module = null) {
        $theme      = SSViewer::get_theme_folder();
        $project    = project();
        $absbase    = BASE_PATH . DIRECTORY_SEPARATOR;
        $abstheme   = $absbase . $theme;
        $absproject = $absbase . $project;
        $scripts    = array("/javascript/$name.js", "/js/$name.js");
        if (!Director::isDev()) {
            $scripts = array_merge(array("/javascript/$name.min.js", "/js/$name.min.js"), $scripts);
        }
        foreach ($scripts as $script) {
            if (file_exists($absproject . $script)) {
                Requirements::javascript($project . $script);
                break;
            } elseif ($module && file_exists($abstheme . '_' . $module . $script)) {
                Requirements::javascript($theme . '_' . $module . $script);
                break;
            } elseif (file_exists($abstheme . $script)) {
                Requirements::javascript($theme . $script);
                break;
            } elseif ($module) {
                Requirements::javascript($module . $script);
                break;
            }
        }
    }

}
