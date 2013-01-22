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

require_once(t3lib_extMgm::extPath('webcon_ftptransfer').'Interfaces/Location.php');
require_once(t3lib_extMgm::extPath('webcon_ftptransfer').'Classes/Location/Abstract.php');

/**
 * Location handler for: Chained calls
 * This can only act as source
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	webcon_ftptransfer
 */
class tx_webconFtptransfer_LocationChained implements tx_webconFtptransfer_Location {
		// TODO: This is only true for UNIX like filesystems. Should be "\" on Windows
	protected $pathSeparator = '/';
	protected $absolutePath = '';

		// Parameters passed to the constructor
	protected $rootObject = null;
	protected $path = '';
	protected $type = '';

	/*
	 * Initializes this location
	 *
	 * @param	tx_webconFtptransfer_transferFiles	$rootObject: A reference to the root object instance (scheduler task)
	 * @param	string	$path: The path which should get handled by an instance of this object
	 * @param	string	$type: The type of location this instance is used as (source/target/failed)
	 * @return	boolean	Returns true if initalizing this location succeeded (path is fine, etc.)
	 */
	public function init(tx_webconFtptransfer_transferFiles &$rootObject, $path, $type) {
		$this->rootObject = &$rootObject;
		$this->path = $path;
		if (($path != 'success') && ($path != 'failed') && ($path != 'fatal')) {
				// These are the only "paths" which the LocationChaind class can handle.
			$this->error('Only paths allowed for chained locations are "success", "failed" and "fatal"', t3lib_FlashMessage::ERROR);
			return false;
		}
		$this->type = $type;
		if ($type !== 'source') {
				// This class can only get used as source
			$this->error('Chained locations are only valid as source', t3lib_FlashMessage::ERROR);
			return false;
		}
		return true;
	}

	/*
	 * Returns all files which can get transfered FROM this location
	 *
	 * @return array	An array of "tx_webconFtptransfer_file" objects
	 */
	public function getFiles() {
		$files = $this->rootObject->get_chainDataElement();
		return $files[$this->path];
	}

	/*
	 * Puts the passed file to this location
	 *
	 * @param	tx_webconFtptransfer_File	$file: The file which should get put to this location
	 * @return	mixed	False if an error occured else the name of the file at the target location.
	 */
	public function putFile(tx_webconFtptransfer_File $file) {
			// This class can only get used as source
		return false;
	}

	/*
	 * Returns a string label appropriat for logging about this location
	 *
	 * @return	string	A textual representation for this location
	 */
	public function getLogLabel() {
		return get_class($this).': '.$this->type.': '.$this->path;
	}

	/*
	 * Sets an error message in the application logic error consumer
	 *
	 * @param	string	$message: An error message
	 * @param	integer	$severity: The error severity (Values taken from t3lib_FlashMessage)
	 * @return	void
	 */
	protected function error($message, $severity = t3lib_FlashMessage::NOTICE) {
		$this->rootObject->set_Error($this->getLogLabel().':'.chr(10).$message.chr(10).chr(10), $severity);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/Location/Chained.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/Location/Chained.php']);
}

?>
