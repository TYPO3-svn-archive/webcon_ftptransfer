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
require_once(t3lib_extMgm::extPath('webcon_ftptransfer').'Classes/File/Abstract.php');

/**
 * Abstract class defining a FTP file handler
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	webcon_ftptransfer
 */
class tx_webconFtptransfer_FileFtp extends tx_webconFtptransfer_FileAbstract implements tx_webconFtptransfer_File {
		// Parameters passed to the constructor
	protected $rootObject = null;
	protected $locationObject = null;
	protected $path = '';
	protected $basename = '';

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
		parent::init($rootObject, $locationObject, $path, $basename);
	}
	
	/*
	 * Returns a chunk of the file for 
	 *
	 * @return	boolean	When this is set to true the internal pointer will be reset to the start of the file
	 * @return	string	A chunk of the file. The size is determined by the non-abstract implementation of this class
	 */
	public function getChunk($fromStart = false) {
		$this->error('Can\'t read a chunk from a FTP file', t3lib_FlashMessage::ERROR);
		return false;
	}

	/*
	 * Writes a chunk to the current file
	 *
	 * @return	boolean	True when the chunk could get successfully written to the file
	 */
	public function putChunk($chunk) {
		$this->error('Can\'t write a chunk to a FTP file', t3lib_FlashMessage::ERROR);
		return false;
	}

	/*
	 * Returns true if an instance of this file object can read it's data from a resource
	 *
	 * @return	boolean	When this is true the "readFromResource" method can get used
	 */
	public function canReadFromResource() {
		return true;
	}

	/*
	 * Returns true if an instance of this file object can write it's data to a resource
	 *
	 * @return	boolean	When this is true the "writeToResource" method can get used
	 */
	public function canWriteToResource() {
		return true;
	}

	/*
	 * Returns true if an instance of this file object can read it's data in chunk and return raw data
	 *
	 * @return	boolean	When this is true the "getChunk" method can get used
	 */
	public function canReadChunks() {
		return false;
	}

	/*
	 * Returns true if an instance of this file object can write chunks of data to its resource
	 *
	 * @return	boolean	When this is true the "putChunk" method can get used
	 */
	public function canWriteChunks() {
		return false;
	}

	/*
	 * Writes the content of this file by reading its content from the passed resource
	 *
	 * @param	resource The resource from which to read data to be written to this file
	 * @return	boolean	True if success
	 */
	public function readFromResource($resource) {
		return ftp_fput($this->handle, $this->getAbsoluteName(), $resource, FTP_BINARY);
	}

	/*
	 * Reads the content of this file by writing its content to the passed resource
	 *
	 * @param	resource The resource from which to read data to be written to this file
	 * @return	boolean	True if success
	 */
	public function writeToResource($resource) {
		return ftp_fget($this->handle, $resource, $this->getBaseName(), FTP_BINARY);
	}

	/*
	 * Returns true if an instance of this file object can get accessed by a PHP resource
	 *
	 * @return	boolean	When true is returned an instance of this object can get supply a PHP resource by calling the method getResource()
	 */
	public function hasResource() {
		return false;
	}

	/*
	 * Opens this file for reading
	 *
	 * @return	boolean	True if file could get successfully opened or if this isn't necessary for the current type of location
	 */
	public function openForReading() {
		$this->handle = $this->locationObject->getConnectionHandle();
		return true;
	}

	/*
	 * Opens a file for writing
	 *
	 * @return	boolean	$append: When this is set the file pointer will be placed to the end of the file (not supported for all location types)
	 * @return	boolean	True if file could get successfully opened or if this isn't necessary for the current type of location
	 */
	public function openForWriting($append = false) {
		$this->handle = $this->locationObject->getConnectionHandle();
		return true;
	}

	/*
	 * Closes this file
	 *
	 * @return	void
	 */
	public function close() {
			// The FTP handle is maintained by the locationObject. Simply set it to "null"
		$this->handle = null;
	}

	/*
	 * Deletes this file
	 *
	 * @return	boolean	True when successfully deleted
	 */
	public function delete() {
		ftp_delete($this->getBaseName());
	}

	/*
	 * Returns the size of the current file or false if it doesn't exist currently
	 *
	 * @return	mixed The size of the current file in bytes or false if it doesn't exist
	 */
	public function getSize() {
		if (!$this->handle) {
			$this->handle = $this->locationObject->getConnectionHandle();
		}
		return ftp_size($this->handle, $this->getBaseName());
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/File/Ftp.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/File/Ftp.php']);
}

?>
