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
	 * @return string Texts with url's and emails transformed in html tag <a>. It uses WordPress function make_clickable() to do this.
	 */
	public static function make_clickable_links($text) {

		return make_clickable($text);
	}
}