<?php

/*
 * File Type Detection for PHP
 * Copyright 2020 Daniel Marschall, ViaThinkSoft
 *
 *    Revision 2020-05-17
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class VtsFileTypeDetect {

	public static function getMimeType($filename) {
		include __DIR__ . '/mimetype_lookup.inc.php';

		foreach ($mime_types as $ext => $mime) {
			if (strtoupper(substr($filename, -strlen($ext)-1)) == strtoupper('.'.$ext)) {
				return $mime;
			}
		}

		return false;
	}

	public static function getDescription($file, $filename1=__DIR__.'/filetypes.conf', $filename2=__DIR__.'/filetypes.local') {
		// TODO: Make it multi-lang

		$ini = !file_exists($filename1) ? array() : parse_ini_file($filename1, true, INI_SCANNER_RAW);
		if (!isset($ini['OidHeader']))     $ini['OidHeader']     = array();
		if (!isset($ini['GuidHeader']))    $ini['GuidHeader']    = array();
		if (!isset($ini['FileExtension'])) $ini['FileExtension'] = array();
		if (!isset($ini['MimeType']))      $ini['MimeType']      = array();

		$ini2 = !file_exists($filename2) ? array() : parse_ini_file($filename2, true, INI_SCANNER_RAW);
		if (!isset($ini2['OidHeader']))     $ini2['OidHeader']     = array();
		if (!isset($ini2['GuidHeader']))    $ini2['GuidHeader']    = array();
		if (!isset($ini2['FileExtension'])) $ini2['FileExtension'] = array();
		if (!isset($ini2['MimeType']))      $ini2['MimeType']      = array();

		if (is_readable($file)) {
			$h = fopen($file, 'r');
			$line = trim(fgets($h, 128));
			if (($line[0] == '[') && ($line[strlen($line)-1] == ']')) {
				$line = substr($line, 1, strlen($line)-2);
				if (isset($ini2['OidHeader'][$line]))  return $ini2['OidHeader'][$line];
				if (isset($ini['OidHeader'][$line]))   return $ini['OidHeader'][$line];
				if (isset($ini2['GuidHeader'][$line])) return $ini2['GuidHeader'][$line];
				if (isset($ini['GuidHeader'][$line]))  return $ini['GuidHeader'][$line];
			}
			fclose($h);
		}

		foreach ($ini2['FileExtension'] as $ext => $name) {
			if (strtoupper(substr($file, -strlen($ext)-1)) == strtoupper('.'.$ext)) {
				return $name;
			}
		}

		foreach ($ini['FileExtension'] as $ext => $name) {
			if (strtoupper(substr($file, -strlen($ext)-1)) == strtoupper('.'.$ext)) {
				return $name;
			}
		}

		$mime = false;
		if (function_exists('mime_content_type')) {
			$mime = @mime_content_type($file);
		}
		if (!$mime) {
			$mime = self::getMimeType($file);
		}
		if ($mime) {
			if (isset($ini2['MimeType'][$mime])) return $ini2['MimeType'][$mime];
			if (isset($ini['MimeType'][$mime]))  return $ini['MimeType'][$mime];
		}

		return $ini['Static']['LngUnknown'];
	}

}
