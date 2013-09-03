<?php

class VP_Util_Color {


	/**
	 * Return 2 digit formatted Dechex
	 * @param  string $dec Decimal value
	 * @return string	   Formatted Hex Value
	 */
	private function _dechex($dec) {
		return str_pad(dechex($dec), 2, "0", STR_PAD_LEFT);
	}

	/**
	 * Validate Decimal value
	 * @param  integer $hex            Decimal value
	 * @param  boolean $allow_negative Allow Negative range
	 * @return integer                 Validated Decimal value
	 */
	private function _validate_dec_value($dec, $allow_negative = false) {
		if ($allow_negative) {
			return max(min(array($dec, 255)), -255);
		} else {
			return max(min(array($dec, 255)), 0);
		}
	}

	/**
	 * Validate Percent Decimal value
	 * @param  float   $hex Percent    Decimal value
	 * @param  boolean $allow_negative Allow Negative range
	 * @return float                   Validated Percent Decimal value
	 */
	private function _validate_percent_value($percent, $allow_negative = false) {
		if ($allow_negative) {
			return max(min(array($percent, 1)), -1);
		} else {
			return max(min(array($percent, 1)), 0);
		}
	}

	/**
	 * Format HEX
	 * @param  string $hex HEX color string
	 * @return string      Formatted HEX color
	 */
	private function _format_hex($hex) {

		// strip #
		$hex = str_replace('#', '', $hex);
		
		if (strlen($hex) === 3) {
			// short format to full format
			return $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
		} else if (strlen($hex) != 6) {
			// it's not hex format, return original
			return $hex;
		}
		// already perfect!
		return $hex;
	}

	/**
	 * Fill HSL
	 * @param  array $c Channel array
	 * @return array    Channel array
	 */
	private function _fill_hsl($c) {

		// default value
		$c['hue']        = 0;
		$c['saturation'] = 0;
		$c['lightness']  = 0;

		$min = min($c['red'] / 255, $c['green'] / 255, $c['blue'] / 255);
		$max = max($c['red'] / 255, $c['green'] / 255, $c['blue'] / 255);
		$chroma = $max - $min;

		// lightness
		$c['lightness'] = ($max + $min) / 2;
		
		// grayscale
		if ($chroma == 0) return $c;

		// saturation
		if ($c['lightness'] < 0.5) {
			$c['saturation'] = $chroma / ($max + $min);
		}
		else {
			$c['saturation'] = $chroma / (2 - $max - $min);
		}

		// hue
		$_R = ((($max - $c['red']) / 6) + ($chroma / 2)) / $chroma;
		$_G = ((($max - $c['green']) / 6) + ($chroma / 2)) / $chroma;
		$_B = ((($max - $c['blue']) / 6) + ($chroma / 2)) / $chroma;

		if ($c['red'] == $max) {
			$c['hue'] = $_B - $_G;
		}
		else if ($c['green'] == $max) {
			$c['hue'] = (1 / 3) + $_R - $_B;
		}
		else if ($c['blue'] == $max) {
			$c['hue'] = (2 / 3) + $_G - $_R;
		}
		$c['hue'] = self::_validate_percent_value($c['hue']) * 360;

		// return
		return $c;
	}

	/**
	 * Extract from HEX color
	 * @param  string $hex HEX color
	 */
	private function _extract_hex($hex) {
		$hex = self::_format_hex($hex);
		$c = array();

		$c['format']     = 'hex';
		$c['red']        = self::_validate_dec_value(hexdec($hex[0].$hex[1]));
		$c['green']      = self::_validate_dec_value(hexdec($hex[2].$hex[3]));
		$c['blue']       = self::_validate_dec_value(hexdec($hex[4].$hex[5]));
		$c['alpha']      = 1;
		$c = self::_fill_hsl($c);

		return $c;
	}

	/**
	 * Extract from RGBA color
	 * @param  string $rgba RGBA color
	 */
	private function _extract_rgba($rgba) {
		$arr_rgba = explode(',', str_replace(array('rgba(', ')', ' '), '', $rgba));

		$c['format']     = 'rgba';
		$c['red']        = isset($arr_rgba[0]) ? self::_validate_dec_value($arr_rgba[0]) : 0;
		$c['green']      = isset($arr_rgba[1]) ? self::_validate_dec_value($arr_rgba[1]) : 0;
		$c['blue']       = isset($arr_rgba[2]) ? self::_validate_dec_value($arr_rgba[2]) : 0;
		$c['alpha']      = isset($arr_rgba[3]) ? self::_validate_percent_value($arr_rgba[3]) : 0;
		$c = self::_fill_hsl($c);

		return $c;
	}

	/**
	 * Extract from RGB color
	 * @param  string $rgb RGB color
	 */
	private function _extract_rgb($rgb) {
		$arr_rgb = explode(',', str_replace(array('rgb(', ')', ' '), '', $rgb));

		$c['format']     = 'rgb';
		$c['red']        = isset($arr_rgb[0]) ? self::_validate_dec_value($arr_rgb[0]) : 0;
		$c['green']      = isset($arr_rgb[1]) ? self::_validate_dec_value($arr_rgb[1]) : 0;
		$c['blue']       = isset($arr_rgb[2]) ? self::_validate_dec_value($arr_rgb[2]) : 0;
		$c['alpha']      = isset($arr_rgb[3]) ? self::_validate_percent_value($arr_rgb[3]) : 0;
		$c = self::_fill_hsl($c);

		return $c;
	}

	/**
	 * Parse to RGBA string format
	 * @param  array  $c Channel array
	 * @return string    RGBA color string
	 */
	private function _parse_rgba($c) {
		return "rgba($c[red],$c[green],$c[blue],$c[alpha])";
	}

	/**
	 * Parse to RGB string format
	 * @param  array  $c Channel array
	 * @return string    RGB color string
	 */
	private function _parse_rgb($c) {
		return "rgb($c[red],$c[green],$c[blue])";
	}

	/**
	 * Parse to HEX string format
	 * @param  array  $c Channel array
	 * @return string    HEX color string
	 */
	private function _parse_hex($c) {
		return '#' . self::_dechex($c['red']) . self::_dechex($c['blue']) . self::_dechex($c['green']);
	}

	/**
	 * Check if color string is in HEX format
	 * @param  string  $string Color string
	 * @return boolean         Boolean
	 */
	public static function is_hex_color($string) {
		if (preg_match("/^#?(?:[0-9a-fA-F]{3}){1,2}$/", $string)) {
			return true;
		}
		return false;
	}

	/**
	 * Check if color string is in RGBA format
	 * @param  string  $string Color string
	 * @return boolean         Boolean
	 */
	public static function is_rgba_color($string) {
		if (strpos($string, 'rgba(') === 0) {
			return true;
		}
		return false;
	}

	/**
	 * Check if color string is in RGB format
	 * @param  string  $string Color string
	 * @return boolean         Boolean
	 */
	public static function is_rgb_color($string) {
		if (strpos($string, 'rgb(') === 0) {
			return true;
		}
		return false;
	}

	/**
	 * Extract a Color String into Channel Array
	 * @param  string $color Color string
	 * @return array         Channel array
	 */
	public static function extract($color) {
		if (self::is_hex_color($color)) {
			return self::_extract_hex($color);
		}
		else if (self::is_rgba_color($color)) {
			return self::_extract_rgba($color);
		}
		else if (self::is_rgb_color($color)) {
			return self::_extract_rgb($color);
		}
		return false;
	}

	/**
	 * Change format
	 * @param  string $color  Color string
	 * @param  string $format Return format
	 * @return string         Return Color string
	 */
	public static function parse($color, $format = 'hex') {
		$c = self::extract($color);
		if ($c === false) return $color;
		return call_user_func(array('VP_Util_Color', "_parse_$format"), $c);
	}

	/**
	 * Adjust Lightness (lighten / darken)
	 * @param  string $color  Color String
	 * @param  float  $amount Ammount of adjustment
	 * @return string         Return Color String
	 */
	public static function adjust_lightness($color, $amount = 0) {
		$c = self::extract($color);
		$amount = self::_validate_percent_value($amoun, true);

		$c['lightness'] = $c['lightness'] + $amount;
		$c['lightness'] = self::_validate_percent_value($c['lightness']);
		
		return call_user_func(array('VP_Util_Color', "_parse_{$c["format"]}"), $c);
	}

	/**
	 * Adjust Alpha
	 * @param  string $color  Color String
	 * @param  float  $amount Ammount of adjustment
	 * @return string         Return Color String
	 */
	public static function adjust_alpha($color, $amount = 0) {
		$c = self::extract($color);
		$amount = self::_validate_percent_value($amount, true);

		$c['alpha'] = $c['alpha'] + $amount;
		$c['alpha'] = self::_validate_percent_value($c['alpha']);

		return call_user_func(array('VP_Util_Color', "_parse_{$c["format"]}"), $c);
	}

	/**
	 * Percentage Alpha
	 * @param  string $color  Color String
	 * @param  float  $amount Ammount of adjustment
	 * @return string         Return Color String
	 */
	public static function percentage_alpha($color, $amount = 0) {
		$c = self::extract($color);
		$amount = self::_validate_percent_value($amount, false);

		$c['alpha'] = $c['alpha'] * $amount;
		$c['alpha'] = self::_validate_percent_value($c['alpha']);

		return call_user_func(array('VP_Util_Color', "_parse_{$c["format"]}"), $c);
	}
}