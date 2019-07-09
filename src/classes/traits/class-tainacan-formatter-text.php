<?php

namespace Tainacan\Traits;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class for formatter texts
 * @author Vinicius Nunus - L3P/Medialab
 *
 */
trait Formatter_Text {

	/**
	 * 
	 * @return string Texts with url's transformed in html tag <a>
	 */
	public function make_clickable_links($text) {
		$url = '~((www\.|http:\/\/www\.|http:\/\/|https:\/\/www\.|https:\/\/|ftp:\/\/www\.|ftp:\/\/|ftps:\/\/www\.|ftps:\/\/)[^"<\s]+)(?![^<>]*>|[^"]*?<\/a)~i';
		$text = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $text);
		$text = str_replace('href="www.', 'href="http://www.', $text);

		return $text;
	}
}