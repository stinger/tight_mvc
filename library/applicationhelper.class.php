<?php
class ApplicationHelper
{

	function pluralize($count, $term)
	{
		global $inflect;
		return $inflect->pluralize_if($count, $term);
	}

	function sanitize($data)
	{
		return stripslashes(strip_tags($data));
	}

	function link($text, $path, $class = null)
	{
		$base = BASE_URL;
		$path = str_replace(' ','-',$path);
		if (CURRENT_MODULE)
		{
			$path = CURRENT_MODULE . "/{$path}";
		}
		return "<a href=\"{$base}/{$path}\" class=\"{$class}\">{$text}</a>";
	}

	function doctype($doctype = NULL)
	{
		switch($doctype)
		{
			case 'HTML5':
				$type = '<!doctype html>';
			break;
			case 'XHTML11':
				$type = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
			break;
			case 'XHTML1_STRICT':
				$type='<?xml version="1.0" encoding="UTF-8"?>'."\n".'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			break;
			case 'XHTML1_TRANSITIONAL':
				$type = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			break;
			case 'XHTML1_FRAMESET':
				$type='<?xml version="1.0" encoding="UTF-8"?>'."\n".'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
			break;
			case 'XHTML1_BASIC':
				$type= '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">';
			break;
			case 'HTML4_STRICT':
				$type = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
			break;
			case 'HTML4_TRANSITIONAL':
				$type = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
			break;
			case 'HTML4_FRAMESET':
				$type = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
			break;
			case 'HTML32':
				$type = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">';
			break;
			case 'HTML20':
				$type = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">';
			break;
			default:
				$type = '<!doctype html>';
			break;
		}
		return $type."\n";
	}

	function include_js($fileName)
	{
		$base = BASE_URL;
		return "<script src=\"{$base}/js/{$fileName}.js\"></script>\n";
	}

	function include_css($fileName,$media='screen')
	{
		$base = BASE_URL;
		return "<link rel=\"stylesheet\" href="{$base}/css/{$fileName}.css\" media=\"{$media}\"/>\n";
	}

	function remove_accent($str)
	{
		$a = array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','Ā','ā','Ă','ă','Ą','ą','Ć','ć','Ĉ','ĉ','Ċ','ċ','Č','č','Ď','ď','Đ','đ','Ē','ē','Ĕ','ĕ','Ė','ė','Ę','ę','Ě','ě','Ĝ','ĝ','Ğ','ğ','Ġ','ġ','Ģ','ģ','Ĥ','ĥ','Ħ','ħ','Ĩ','ĩ','Ī','ī','Ĭ','ĭ','Į','į','İ','ı','Ĳ','ĳ','Ĵ','ĵ','Ķ','ķ','Ĺ','ĺ','Ļ','ļ','Ľ','ľ','Ŀ','ŀ','Ł','ł','Ń','ń','Ņ','ņ','Ň','ň','ŉ','Ō','ō','Ŏ','ŏ','Ő','ő','Œ','œ','Ŕ','ŕ','Ŗ','ŗ','Ř','ř','Ś','ś','Ŝ','ŝ','Ş','ş','Š','š','Ţ','ţ','Ť','ť','Ŧ','ŧ','Ũ','ũ','Ū','ū','Ŭ','ŭ','Ů','ů','Ű','ű','Ų','ų','Ŵ','ŵ','Ŷ','ŷ','Ÿ','Ź','ź','Ż','ż','Ž','ž','ſ','ƒ','Ơ','ơ','Ư','ư','Ǎ','ǎ','Ǐ','ǐ','Ǒ','ǒ','Ǔ','ǔ','Ǖ','ǖ','Ǘ','ǘ','Ǚ','ǚ','Ǜ','ǜ','Ǻ','ǻ','Ǽ','ǽ','Ǿ','ǿ');
		$b = array('A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L','l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O','o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','A','a','AE','ae','O','o');
		return str_replace($a, $b, $str);
	}

	function slug($str)
	{
		return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', '-', ''), $this->remove_accent($str)));
	}
}
