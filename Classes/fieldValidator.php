<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Bernhard Kraft <kraft@web-consulting.at>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
 * Validation class for array fields
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	webcon_
 */
class tx_webcon_fieldValidator {
		// This will get initialized to a string of all characters allowed in an FTP "string"
	protected $ftpStringChars = '';


	/**
	 * Constructor
	 * Initializes a string of characters which are allowed in FTP strings
	 *
	 * @return	void
	 */
	public function __construct() {
		for ($i = 1; $i < 128; $i++) {
			if (($i == ord("\n")) || ($i == ord("\r"))) {
				continue;
			}
			$this->ftpStringChars .= chr($i);
		}
	}

	/**
	 * Validates the passed value as requested by the "validationConfig" parameter
	 * The "validationConfig" data usually contains a validation type and additional options.
	 *
	 * ===================
	 *
	 * The following validation types are allowed currently:
	 * "hostname"	Validates a hostname
	 * "port"	Validates an IP port
	 * "FTP_string"	Validates a FTP string as used for FTP username/password credentials
	 * "path"	Validates a filesystem path
	 *
	 * ===================
	 *
	 * Additional options can get be set in the array. There are options which are only valid
	 * for some specific validation type or other options which will get applied to every validation
	 *
	 * Global validation options
	 * "allowEmpty"	Allows the value to be empty
	 *
	 * Validation options for some specific types:
	 * type=path
	 *   "OS"		The operating system scheme which should get used for validation the path.
	 *			Can be "WIN" or empty ("UNIX") currently.
	 *   "mustExist"	The validated file/directory must exist
	 *   "mustBeDirectory"	The validated value must be a directory path
	 *   "mustBeFile"	The validated value must be a regular file
	 *   "validateAbsolutePrefix"	If the validated file/directory is a relative path it will get prepended by
	 *				this prefix for validation.
	 *				This prefix is not checked for validity!
	 *				The prefix will only get prepended for validation. Not permanently.
	 * 
	 * ===================
	 *
	 * @param	mixed	$value: The value to validate
	 * @param	array	$validationConfig: The type and configuration of the validation which should get
	 *			performed onto the value (see above description for details)
	 * @return	boolean	Returns "true" if the passed value validated successfully
	 */
	public function validate($value, $validationConfig) {
		$validationMethod = 'validate_'.$validationConfig['type'];
		if (!method_exists($this, $validationMethod)) {
			return false;
		}
		if ($validationConfig['allowEmpty'] && ((is_array($value) && !count($value)) || !strlen($value))) {
			return true;
		}
		return $this->$validationMethod($value, $validationConfig);
	}

	/*************************************************
	 *
	 * VALIDATION METHODS
	 *
	 * Those methods take care of validating the passed values
	 *
	 *************************************************/

	/**
	 * Validates the passed value as FTP "string". FTP "strings" are allowed for username/password of an FTP session
	 * The valid characters for an FTP "string" are defined in RFC 959: Section 5.3.2 FTP COMMAND ARGUMENTS
	 *
	 * @param	mixed	$value: The value to validate as hostname or IP address
	 * @return	boolean	Returns "true" if the passed value is a valid hostname or IP address
	 */
	protected function validate_FTP_string($value) {
		if (strcspn($value, $this->ftpStringChars)) {
			return false;
		}
		return true;
	}

	/**
	 * Validates the passed value as IP hostname or IP address (v4/v6)
	 *
	 * @param	mixed	$value: The value to validate as hostname or IP address
	 * @return	boolean	Returns "true" if the passed value is a valid hostname or IP address
	 */
	protected function validate_hostname($value) {
		if (t3lib_div::validIP($value)) {
			return true;
		} else {
			if (strspn($value, ':/?#')) {
				return false;
			}
		}
		return filter_var('http://'.$value, FILTER_VALIDATE_URL) !== false;
	}

	/**
	 * Validates the passed value as email address
	 *
	 * @param	mixed	$value: The value to validate as email address
	 * @return	boolean	Returns "true" if the passed value is a valid email address
	 */
	protected function validate_email($value) {
		$parts = t3lib_div::trimExplode(',', $value, true);
		foreach ($parts as $email) {
			if (preg_match('/^([^<]+)<([^>]+@[^>]+)>\s*$/', $email, $matches)) {
				if (!t3lib_div::validEmail($matches[2])) {
					return false;
				}
			} else {
				if (!t3lib_div::validEmail($email)) {
					return false;
				}
			}	
		}
		return true;
	}

	/**
	 * Validates the passed value as boolean
	 *
	 * @param	mixed	$value: The value to validate as boolean
	 * @return	boolean	Returns "true" if the passed value is a valid boolean
	 */
	protected function validate_boolean($value) {
//		if (t3lib_utility_Math::canBeInterpretedAsInteger($value)) {
		if (t3lib_div::testInt($value)) {
				// Be strict
			if (($value == 0) || ($value == 1)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Validates the passed value as TYPO3 page UID
	 *
	 * @param	mixed	$value: The value to validate as TYPO3 page UID
	 * @return	boolean	Returns "true" if the passed value is a valid TYPO3 page UID
	 */
	protected function validate_page($value) {
//		if (t3lib_utility_Math::canBeInterpretedAsInteger($value)) {
		if (t3lib_div::testInt($value)) {
			$page = false;
			if (TYPO3_MODE == 'BE') {
				$sysPage = t3lib_div::makeInstance('t3lib_pageSelect');
				$page = $sysPage->getPage($value);
			} elseif (TYPO3_MODE == 'FE') {
				$page = $GLOBALS['TSFE']->sys_page->getPage($value);
			}
			if (is_array($page) && $page['uid']) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Validates the passed value as TYPO3 record UID
	 * The table to which the UID belongs is supplied via the 'table' key of the $params array
	 *
	 * @param	mixed	$value: The value to validate as TYPO3 record UID
	 * @return	boolean	Returns "true" if the passed value is a valid TYPO3 record UID
	 */
	protected function validate_record($value, $params = array()) {
//		if (t3lib_utility_Math::canBeInterpretedAsInteger($value)) {
		if ($table = $params['table']) {
			if (t3lib_div::testInt($value)) {
				$records = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $table, 'uid='.intval($value));
				return (is_array($records) && count($records)) ? true : false;
			}
		}
		return false;
	}

	/**
	 * Validates the passed value as a comma separated list of TYPO3 record UIDs
	 * The table to which the UID belongs is supplied via the 'table' key of the $params array
	 *
	 * @param	mixed	$value: The value to validate as TYPO3 record UID
	 * @return	boolean	Returns "true" if the passed value is a valid TYPO3 record UID
	 */
	protected function validate_recordList($value, $params = array()) {
		$valid = true;
		$parts = t3lib_div::trimExplode(',', $value, 1);
		foreach ($parts as $part) {
			$valid &= $this->validate_record($part, $params);
			if (!$valid) {
				break;
			}
		}
		return $valid;
	}

	/**
	 * Validates the passed value as TCP/UDP port number
	 *
	 * @param	mixed	$value: The value to validate as IP port number
	 * @return	boolean	Returns "true" if the passed value is a valid IP port number
	 */
	protected function validate_port($value) {
//		if (t3lib_utility_Math::canBeInterpretedAsInteger($value)) {
		if (t3lib_div::testInt($value)) {
			$value = intval($value);
			if (($value > 0) && ($value < 65536)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Validates a filesystem path.
	 * The validation of windows path does not take network shares into account.
	 * Neither has the validation of windows path been tested.
	 *
	 * @param	string	$value: The value to validate as filesystem path
	 * @return	boolean	Returns "true" if the passed value is a valid filesystem path
	 */
	protected function validate_path($value, $params = array()) {
		$result = false;
		$isAbsolute = false;
		if ((TYPO3_OS=='WIN') && (!$params['OS'] || ($params['OS'] =='WIN'))) {
			if (preg_match('/^[A-Z]:/i', $value)) {
				$tmp_value = preg_replace('/^[A-Z]:/', '', $value);
				$isAbsolute = true;
			}
			/*
				// TODO: Validate hostname (WINS name, network path) is not supported currently
			if (preg_match('/^\\\\([^\\]+)\\.+/', $value, $matches)) {
				print_r($matches);
			}
			*/
 				// Validate rest of path
			if (preg_match ('@[^'.preg_quote(":*?\"<>|\r\n").']@', $tmp_value)) {
				$result = true;
			}
		} else {
				// UNIX specific
			if (preg_match('|^(/)?([^/\0]+(/)?)+$|', $value)) {
				$result = true;
			}
			if (substr($value, 0, 1) == '/') {
				$isAbsolute = true;
			}
		}
		if ($result && $params['mustExist']) {
			if (!$isAbsolute) {
				$value = $params['validateAbsolutePrefix'].$value;
			}
			if (!file_exists($value)) {
				$result = false;
			}
			if ($params['mustBeDirectory'] && !is_dir($value)) {
				$result = false;
			}
			if ($params['mustBeFile'] && !is_file($value)) {
				$result = false;
			}
		}
		return $result;
	}


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/fieldValidator.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/fieldValidator.php']);
}

?>
