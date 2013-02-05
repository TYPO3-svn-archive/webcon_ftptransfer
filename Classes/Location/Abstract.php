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
 * Location handler for: FTP
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	webcon_ftptransfer
 */
abstract class tx_webconFtptransfer_LocationAbstract {
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
	public function prepare(tx_webconFtptransfer_transferFiles &$rootObject, $path, $type) {
		$this->rootObject = &$rootObject;
		$this->path = $path;
		$this->type = $type;
		return true;
	}

	/*
	 * Returns all files which can get transfered FROM this location
	 *
	 * @return array	An array of "tx_webconFtptransfer_file" objects
	 */
	public function getFiles() {
		return array();
	}

	/*
	 * Puts the passed file to this location
	 *
	 * @param	tx_webconFtptransfer_File	$file: The file which should get put to this location
	 * @return	mixed	False if an error occured else the name of the file at the target location.
	 */
	public function putFile(tx_webconFtptransfer_File $file) {
			// Get a filename for this location (either the same as the original or an alternate if there is already such a file - and overwrite is disabled)
		if ($this->overwriteExisting() || !$this->fileExists($file)) {
			$targetName = $file->getBaseName();
		} else {
			$targetName = $this->getAlternateTargetName($file);
		}

		if ($this->rootObject->moveInsteadOfCopy($this->type)) {
			if ($this->move($file, $targetName)) {
				return $targetName;
			}
		} else {
			if ($this->copy($file, $targetName)) {
				return $targetName;
			}
		}
		return false;
	}

	/*
	 * Generate an alternate filename for a filename which does already exist at this location
	 *
	 * @param	tx_webconFtptransfer_File	$file: The file for which a new target name should get generated
	 * @return	string	An alternate name file name
	 */
	abstract protected function getAlternateTargetName(tx_webconFtptransfer_File $file);
	
	/*
	 * Returns true when a file which has a name as the passed file object already exists at this location
	 *
	 * @param	tx_webconFtptransfer_File	$file: The file whose basename should be checked for local existence
	 * @return	boolean	When the basename of the passed file object instance already exists at the location this location object instance points to true will get returned
	 */
	abstract protected function fileExists(tx_webconFtptransfer_File $file);

	/*
	 * Makes a copy of the passed file to the target location
	 * Depending on the locationObject type of the passed file different copy alogrythms can be choosen.
	 *
	 * @param	tx_webconFtptransfer_File	$file: The file which should get stored to the passed target filename
	 * @param	string	$target: A filename where the contents of $file should get put to
	 * @return	boolean	True if the copy procedure succeeded
	 */
	abstract protected function copy(tx_webconFtptransfer_File $file, $target);

	/*
	 * Moves the file from one location to another on the same location type (local, FTP, etc.)
	 * There is an extraneous method for this because different moving algorythms can be implemented depending
	 * on the type of the locationObject of the passed file.
	 *
	 * @param	tx_webconFtptransfer_File	$file: The file which should get moved to the passed target filename
	 * @param	string	$target: A filename where the file $file should get put to
	 * @return	boolean	True if the move procedure succeeded
	 */
	abstract protected function move(tx_webconFtptransfer_File $file, $target);

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

	/*
	 * Returns the prefix for a file name to make it an absolute file path
	 *
	 * @return	string	A prefix which can be put in front of a filename in this location to make it absolute
	 */
	public function getAbsolutePrefix() {
		return $this->absolutePath . $this->pathSeparator;
	}

	/*
	 * Returns true when already existing files should get overwritten
	 *
	 * @return	boolean	If already existing files should get overwritten this method returns true
	 */
	abstract protected function overwriteExisting();

	/*
	 * Returns true when the passed location object points to a location of the same location type as $this location
	 *
	 * @param	tx_webconFtptransfer_location	$location: The location object instance to check
	 * @return	boolean	True when the passed location is of the same type than the current location
	 */
	abstract protected function sameLocationType(tx_webconFtptransfer_location $location);

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/Location/None.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/Location/None.php']);
}

?>
