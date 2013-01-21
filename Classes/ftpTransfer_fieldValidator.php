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


require_once(t3lib_extMgm::extPath('webcon_ftptransfer').'Classes/fieldValidator.php');

/**
 * Validation class for array fields
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	webcon_
 */
class tx_webconFtpTransfer_fieldValidator extends tx_webcon_fieldValidator {
		// This is the prefix which can get used before a path to denote that the path points to the remote FTP site 
	static $ftpPathPrefix = 'ftp:';

		// This is the prefix which can get used before a path to denote that the path should handle results of previous chained tasks
	static $chainPathPrefix = 'chain:';
	static $allowedChainPaths = array('target', 'failed', 'fatal');


	/**
	 * Validates a filesystem path.
	 * This class extends the filesystem path validation to allow a "ftp:" prefix which denotes the
	 * path is not local but on a remote FTP server.
	 *
	 * If the path to be validates has the "ftp:" prefix the options "mustExist", "mustBeDirectory" and "mustBeFile"
	 * wont work anymore and will be unset.
	 *
	 * @param	string	$value: The value to validate as filesystem path
	 * @return	boolean	Returns "true" if the passed value is a valid filesystem path
	 */
	protected function validate_path($value, $params = array()) {
		if (substr($value, 0, strlen(self::$ftpPathPrefix)) === self::$ftpPathPrefix) {
				// If the path has a prefix "ftp:" remove this for validation and set path type to "UNIX"
			$value = substr($value, strlen(self::$ftpPathPrefix));
				// FTP uses UNIX style pathnames
			$params['OS']= 'UNIX';
				// Only local files can get checked for existence or type
			unset($params['mustExist']);
			unset($params['mustBeDirectory']);
			unset($params['mustBeFile']);
		}
		if (substr($value, 0, strlen(self::$chainPathPrefix)) === self::$chainPathPrefix) {
			if (!$params['allowChain']) {
				return false;
			}
				// If the path has a prefix "chain:" remove this for validation.
				// The rest of the path must be one of the values "target/failed/fatal"
			$value = substr($value, strlen(self::$chainPathPrefix));
				// Only paths in "$allowedChainPaths" are valid
			if (in_array($value, self::$allowedChainPaths)) {
				return true;
			} else {
				return false;
			}
			
		}
		return parent::validate_path($value, $params);
	}


	/**
	 * Validates a "label". This is just a dummy method as labels wont get validated.
	 *
	 * @param	string	$value: The value to validate as label
	 * @return	boolean	Returns always "true"
	 */
	protected function validate_label($value, $params = array()) {
		return true;
	}


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/ftpTransfer_fieldValidator.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/ftpTransfer_fieldValidator.php']);
}

?>

