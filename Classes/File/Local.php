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
 * Class defining a local file handler (extending abstract file handler)
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	webcon_ftptransfer
 */
class tx_webconFtptransfer_FileLocal extends tx_webconFtptransfer_FileAbstract implements tx_webconFtptransfer_File {
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
		if (!$this->handle) {
			$this->openForReading();
		} elseif ($fromStart) {
			fseek($this->handle, 0, SEEK_SET);
		}
		return fread($this->handle, $this->CHUNK_SIZE);
	}

	/*
	 * Writes a chunk to the current file
	 *
	 * @return	boolean	True when the chunk could get successfully written to the file
	 */
	public function putChunk($chunk) {
		if (!$this->handle) {
			$this->openForWriting();
		}
		if (!$this->handle) {
			$this->error('No valid handle available', t3lib_FlashMessage::WARNING);
			return false;
		} else {
			$bytesWritten = fwrite($this->handle, $chunk);
			if (($bytesWritten === false) || ($bytesWritten != strlen($chunk))) {
				return false;
			}
			return true;
		}
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
		return true;
	}

	/*
	 * Returns true if an instance of this file object can write chunks of data to its resource
	 *
	 * @return	boolean	When this is true the "putChunk" method can get used
	 */
	public function canWriteChunks() {
		return true;
	}

	/*
	 * Writes the this file by reading its content from the passed resource
	 *
	 * @param	resource The resource from which to read data to be written to this file
	 * @return	boolean	True if success
	 */
	public function readFromResource($resource) {
			// Close the file while using "file_put_contents()"
		$this->close();
		return file_put_contents($this->getAbsoluteName(), $resource);
	}

	/*
	 * Reads this file by writing its content to the passed resource
	 *
	 * @return	boolean	True if success
	 */
	public function writeToResource($resource) {
		if (!$this->handle) {
			$this->openForReading();
		}
		if (!$this->handle) {
			$this->error('No valid handle available', t3lib_FlashMessage::WARNING);
			return false;
		}
		$bytesCopied = stream_copy_to_stream($this->handle, $resource);
		$sourceSize = filesize($this->getAbsoluteName());
		return ($bytesCopied == $sourceSize) ? true : false;
	}

	/*
	 * Returns true if an instance of this file object can get accessed by a PHP resource
	 *
	 * @return	boolean	When true is returned an instance of this object can get supply a PHP resource by calling the method getResource() which by default returns $this->handle
	 */
	public function hasResource() {
		return true;
	}

	/*
	 * Opens this file for reading
	 *
	 * @return	boolean	True if file could get successfully opened or if this isn't necessary for the current type of location
	 */
	public function openForReading() {
		$this->handle = fopen($this->getAbsoluteName(), 'rb');
		return is_resource($this->handle);
	}

	/*
	 * Opens a file for writing
	 *
	 * @return	boolean	$append: When this is set the file pointer will be placed to the end of the file (not supported for all location types)
	 * @return	boolean	True if file could get successfully opened or if this isn't necessary for the current type of location
	 */
	public function openForWriting($append = false) {
		$this->handle = fopen($this->getAbsoluteName(), ($append?'a':'w').'b');
		return is_resource($this->handle);
	}

	/*
	 * Closes this file
	 *
	 * @return	void
	 */
	public function close() {
		if ($this->handle) {
			fclose($this->handle);
		}
		$this->handle = null;
	}


	/*
	 * Deletes this file
	 *
	 * @return	boolean	True when successfully deleted
	 */
	public function delete() {
		return unlink($this->getAbsoluteName());
	}

	/*
	 * Returns the size of the current file or false if it doesn't exist currently
	 *
	 * @return	mixed The size of the current file in bytes or false if it doesn't exist
	 */
	public function getSize() {
		if (file_exists($this->getAbsoluteName())) {
			clearstatcache();
			return filesize($this->getAbsoluteName());
		}
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/File/Local.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/File/Local.php']);
}

?>
