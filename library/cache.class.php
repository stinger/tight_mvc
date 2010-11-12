<?php
class Cache {

	function get($fileName) {
		$fileName = APPLICATION_BASE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $fileName;
		if (file_exists($fileName)) {
			$handle = fopen($fileName, 'rb');
			$variable = fread($handle, filesize($fileName));
			fclose($handle);
			return unserialize($variable);
		} else {
			return null;
		}
	}
	
	function set($fileName,$variable) {
		$fileName = APPLICATION_BASE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $fileName;
		$handle = fopen($fileName, 'a');
		fwrite($handle, serialize($variable));
		fclose($handle);
	}

}
