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


require_once(t3lib_extMgm::extPath('webcon_ftptransfer').'Interfaces/File.php');

/**
 * Abstract class defining a file handler
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	webcon_ftptransfer
 */
abstract class tx_webconFtptransfer_FileAbstract {
		// Parameters passed to the constructor
	protected $rootObject = null;
	protected $locationObject = null;
	protected $path = '';
	protected $basename = '';

	protected $handle = null;

	/*
	 * Initializes this file
	 *
	 * @param	tx_webconFtptransfer_transferFiles	$rootObject: A reference to the root object instance (scheduler task)
	 * @param	tx_webconFtptransfer_location		$locationObject: A reference to the location object where this file can be found at
	 * @param	string	$path: The path where this file is found at
	 * @param	string	$basename: The basename of this file
	 * @return	boolean	Returns true if initalizing this file succeeded
	 */
	public function init(tx_webconFtptransfer_transferFiles &$rootObject, tx_webconFtptransfer_location &$locationObject, $path, $basename) {
		$this->rootObject = &$rootObject;
		$this->locationObject = &$locationObject;
		$this->path = $path;
		$this->basename = $basename;
	}


	/*
	 * When this method is called it will return a PHP resource if possible
	 *
	 * @return	resource	A PHP resource representing the contents of this file object
	 */
	public function getResource() {
		return $this->handle;
	}

	/*
	 * Returns a string label appropriat for logging about this file
	 *
	 * @return	string	A textual representation for this file 
	 */
	public function getLogLabel() {
		$logLine = get_class($this) . ': ' . $this->getBaseName();
		return $logLine . ' @ ' . $this->locationObject->getLogLabel();
	}

	/*
	 * Returns the basename of this file
	 *
	 * @return string	The name of this file
	 */
	public function getBaseName() {
		return $this->basename;
	}

	/*
	 * Returns the absolute name of this file
	 *
	 * @return string	The absolute name of this file
	 */
	public function getAbsoluteName() {
		return $this->locationObject->getAbsolutePrefix() . $this->getBaseName();
	}

	/*
	 * Returns the the location object of this file
	 *
	 * @return tx_webconFtptransfer_location	A location object where this file is located (the instance has to implement the "tx_webconFtptransfer_location" (location) interface)
	 */
	public function getLocation() {
		return $this->locationObject;
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


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/File/Abstract.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/File/Abstract.php']);
}

?>
