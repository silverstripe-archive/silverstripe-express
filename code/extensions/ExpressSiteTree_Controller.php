<?php

class ExpressSiteTree_Controller extends Extension {
	public static $allowed_actions = array();

	function onAfterInit() {
		$themeDir = SSViewer::get_theme_folder();

		// Add the combined scripts.
		if (method_exists($this->owner, 'getScriptOverrides')) {
			$scripts = $this->owner->getScriptOverrides();
		} else {
			$scripts = array(
				"$themeDir/js/lib/jquery.js",
				"$themeDir/js/lib/jquery-ui-1.8.21.custom.js",
				'themes/module_bootstrap/js/bootstrap-transition.js',
				'themes/module_bootstrap/js/bootstrap-scrollspy.js',
				'themes/module_bootstrap/js/bootstrap-collapse.js',
				'themes/module_bootstrap/js/bootstrap-carousel.js',
				"$themeDir/js/general.js",
				"$themeDir/js/express.js",
				"$themeDir/js/forms.js"
			);
			if (method_exists($this->owner, 'getScriptIncludes')) {
				$scripts = array_merge($scripts, $this->owner->getScriptIncludes());
			}
		}
		Requirements::combine_files('scripts.js', $scripts);

		// Add the combined styles.
		if (method_exists($this->owner, 'getStyleOverrides')) {
			$styles = $this->owner->getStyleOverrides();
		} else {
			$styles = array(
				"$themeDir/css/layout.css",
				"$themeDir/css/typography.css"
			);
			if (method_exists($this->owner, 'getStyleIncludes')) {
				$styles = array_merge($styles, $this->owner->getStyleIncludes());
			}
		}
		Requirements::combine_files('styles.css', $styles);

		// Print styles
		if (method_exists($this->owner, 'getPrintStyleOverrides')) {
			$printStyles = $this->owner->getPrintStyleOverrides();
		} else {
			$printStyles = array("$themeDir/css/print.css");
			if (method_exists($this->owner, 'getPrintStyleIncludes')) {
				$printStyles = array_merge($printStyles, $this->owner->getPrintStyleIncludes());
			}
		}
		foreach ($printStyles as $printStyle) {
			Requirements::css($printStyle, 'print');
		}

		// Extra folder to keep the relative paths consistent when combining.
		Requirements::set_combined_files_folder(ASSETS_DIR . '/_compiled/p');
	}

	/* 	Give external links the external class, and affix size and type
		prefixes to files.
	*/
	function Content() {
		$content = $this->owner->Content;

		// Internal links.
		preg_match_all('/<a.*href="\[file_link,id=([0-9]+)\].*".*>.*<\/a>/U', $content, $matches);

		for ($i = 0; $i < count($matches[0]); $i++){
			$file = DataObject::get_by_id('File', $matches[1][$i]);
			if ($file) {
				$size = $file->getSize();
				$ext = strtoupper($file->getExtension());
				$newLink = $matches[0][$i] . "<span class='fileExt'> [$ext, $size]</span>";
				$content = str_replace($matches[0][$i], $newLink, $content);
			}
		}

		// and now external links
		$pattern = '/<a href=\"(h[^\"]*)\">(.*)<\/a>/iU';
		$replacement = '<a href="$1" class="external">$2</a>';
		$content = preg_replace($pattern, $replacement, $content, -1);

		return $content;
	}


    /**
     * Overrides the ContentControllerSearchExtension and adds snippets to results.
     */
    function results($data, $form, $request) {

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
                $result->ContextualContent = $result->obj('Content')->ContextSummary(300, $query);
            }
        }

        $rssLink = HTTP::setGetVar('rss', '1');

        // Render the result.
        $data = array(
            'Results' => $results,
            'Query'   => $query,
            'Title'   => _t('SearchForm.SearchResults', 'Search Results'),
            'RSSLink' => $rssLink
        );

        // Choose the delivery method - rss or html.
        if (!$this->owner->request->getVar('rss')) {
            // Add RSS feed to normal search.
            RSSFeed::linkToFeed($rssLink, "Search results for query \"$query\".");

            return $this->owner->customise($data)->renderWith(array('Page_results', 'Page'));
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
            $rss = new RSSFeed($fullList, $this->owner->request->getURL(), $title, $siteTagline, "Title", "ContextualContent", null);
            $rss->setTemplate('Page_results_rss');
            return $rss->outputToBrowser();
        }
    }
}