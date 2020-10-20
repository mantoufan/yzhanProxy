<?php

namespace marcocesarato\minifier;

/**
 * Minifier Class
 * @author Marco Cesarato <cesarato.developer@gmail.com>
 * @copyright Copyright (c) 2019
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link https://github.com/marcocesarato/PHP-Minifier
 * @version 0.1.5
 */
class Minifier
{

	/**
	 * Compress HTML
	 * @param $buffer
	 * @return null|string|string[]
	 */
	public function minifyHTML($buffer) {
		$this->zlibCompression();
		if ($this->isHTML($buffer)) {
			$pattern = "/<script[^>]*>(.*?)<\/script>/is";
			preg_match_all($pattern, $buffer, $matches, PREG_SET_ORDER, 0);
			foreach ($matches as $match) {
				$pattern = "/(<script[^>]*>)(" . preg_quote($match[1], '/') . ")(<\/script>)/is";
				$compress = self::compressJS($match[1]);
				$buffer = preg_replace($pattern, '$1' . $compress . '$3', $buffer);
			}
			$pattern = "/<style[^>]*>(.*?)<\/style>/is";
			preg_match_all($pattern, $buffer, $matches, PREG_SET_ORDER, 0);
			foreach ($matches as $match) {
				$pattern = "/(<style[^>]*>)(" . preg_quote($match[1], '/') . ")(<\/style>)/is";
				$compress = self::compressCSS($match[1]);
				$buffer = preg_replace($pattern, '$1' . $compress . '$3', $buffer);
			}
			$buffer = preg_replace(array('/<!--[^\[](.*)[^\]]-->/Uuis', "/[[:blank:]]+/u", '/\s+/u'), array('', ' ', ' '), str_replace(array("\n", "\r", "\t"), '', $buffer));
		}
		return $buffer;
	}

	/**
	 * Compress CSS
	 * @param $buffer
	 * @return string
	 */
	public function minifyCSS($buffer) {
		$this->zlibCompression();
		return preg_replace(array('#\/\*[\s\S]*?\*\/#', '/\s+/'), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $buffer));
	}

	/**
	 * Compress Javascript
	 * @param $buffer
	 * @return string
	 */

	public function minifyJS($buffer) {
		$this->zlibCompression();
		return str_replace(array("\n", "\r", "\t"), '', preg_replace(array('#\/\*[\s\S]*?\*\/|([^:]|^)\/\/.*$#m', '/\s+/'), array('', ' '), $buffer));
	}

	/**
	 * Check if string is HTML
	 * @param $string
	 * @return bool
	 */
	private function isHTML($string) {
		return preg_match('/<html.*>/', $string) ? true : false;
	}

	/**
	 * Set zlib compression
	 */
	private function zlibCompression() {
		if (ini_get('zlib.output_compression')) {
			ini_set("zlib.output_compression", 1);
			ini_set("zlib.output_compression_level", "9");
		}
	}
}