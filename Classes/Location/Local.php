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
require_once(t3lib_extMgm::extPath('webcon_ftptransfer').'Classes/File/Local.php');

/**
 * Location handler for: FTP
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	webcon_ftptransfer
 */
class tx_webconFtptransfer_LocationLocal extends tx_webconFtptransfer_LocationAbstract implements tx_webconFtptransfer_Location {
	protected $handle = null;
	protected $files = array();
	protected $directories = array();
	protected $fileFuncs = null;
	protected $absolutePath = '';

	/*
	 * Initializes this location
	 *
	 * @param	tx_webconFtptransfer_transferFiles	$rootObject: A reference to the root object instance (scheduler task)
	 * @param	string	$path: The path which should get handled by an instance of this object
	 * @param	string	$type: The type of location this instance is used as (source/target/failed)
	 * @return	boolean	Returns true if initalizing this location succeeded (path is fine, etc.)
	 */
	public function init(tx_webconFtptransfer_transferFiles &$rootObject, $path, $type) {
		if (!parent::init($rootObject, $path, $type)) {
			$this->error('Initializing parent object failed', t3lib_FlashMessage::ERROR);
			return false;
		}
		$this->fileFuncs = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		$this->fileFuncs->init(array(), array());
		if ((strlen($this->path) > 1) && (substr($this->path, -1) == '/')) {
			$this->path = substr($this->path, 0, -1);
		}
		
			// Switch to webroot (This is also the location which is assumed for relative paths by the scheduler field validator)
		chdir(PATH_site);
		if (!file_exists($this->path)) {
			$this->error('Supplied path does not exist', t3lib_FlashMessage::WARNING);
			return false;
		}
		if (!is_dir($this->path)) {
			$this->error('Supplied path is not a directory', t3lib_FlashMessage::WARNING);
			return false;
		}
		if (!chdir($this->path)) {
			$this->error('Can not change to passed directory', t3lib_FlashMessage::WARNING);
			return false;
		}
		$this->absolutePath = getcwd();

		if (($type == 'source') && !is_readable($this->path)) {
			$this->error('Supplied path should get used as source but is not readable', t3lib_FlashMessage::WARNING);
			return false;
		}
		if (($type == 'target') && !is_writeable($this->path)) {
			$this->error('Supplied path should get used as target but is not writable', t3lib_FlashMessage::WARNING);
			return false;
		}
		if (($type == 'failed') && !is_writeable($this->path)) {
			$this->error('Supplied path should get used as location for failed transfers but is not writable', t3lib_FlashMessage::WARNING);
			return false;
		}
		$this->handle = dir($this->path);
		return $this->handle ? true : false;
		
	}

	/*
	 * Returns all files which can get transfered FROM this location
	 *
	 * @return array	An array of "tx_webconFtptransfer_file" objects
	 */
	public function getFiles() {
		$this->readDirectory();	
		return $this->files;
	}

	/*
	 * Reads the current directory and stores all entries in $this->files (or $this->directories if there are directory entries)
	 *
	 * @return void
	 */
	protected function readDirectory() {
		while (($entry = $this->handle->read()) !== false) {
			if (is_file($this->path.'/'.$entry)) {
				$file = t3lib_div::makeInstance('tx_webconFtptransfer_FileLocal');
				$file->init($this->rootObject, $this, $this->path, $entry);
				$this->files[] = $file;
			} elseif (is_dir($this->path.'/'.$entry)) {
				if (($entry != '.') && ($entry != '..')) {
//						// Sub-Directories are currently not handled (recursion)
//					$directory = t3lib_div::makeInstance(__CLASS__, $this, $this->path, $entry);
//					$directory->init($this->rootObject, $this, $this->path, $tmpFile);
//						// then
//					$this->directories[] = $directory;
				}
			}
		}
	}

	/*
	 * Returns true when a file which has a name as the passed file object already exists at this location
	 *
	 * @param	tx_webconFtptransfer_file	$file: The file whose basename should be checked for local existence
	 * @return	boolean	When the basename of the passed file object instance already exists at the location this location object instance points to true will get returned
	 */
	protected function fileExists(tx_webconFtptransfer_File $file) {
		return file_exists($this->getAbsolutePrefix() . $file->getBaseName());
	}

	/*
	 * Generate an alternate filename for a filename which does already exist at this location
	 *
	 * @param	tx_webconFtptransfer_file	$file: The file for which a new target name should get generated
	 * @return	string	An alternate name file name
	 */
	protected function getAlternateTargetName(tx_webconFtptransfer_File $file) {
		return $this->fileFuncs->getUniqueName($file->getBaseName(), $this->path);
	}

	/*
	 * Returns true when already existing files should get overwritten
	 *
	 * @return	boolean	If already existing files should get overwritten this method returns true
	 */
	protected function overwriteExisting() {
		return false;
	}

	/*
	 * Moves the file from one location to another on the same location type (local, FTP, etc.)
	 * There is an extraneous method for this because this can usually be coded more efficient than a simple byte copy
	 *
	 * @param	tx_webconFtptransfer_file	$file: The file which should get moved to the passed target filename
	 * @param	string	$target: A filename where the file $file should get put to
	 * @return	boolean	True if the move procedure succeeded
	 */
	protected function move(tx_webconFtptransfer_File $file, $target) {
		$success = false;
		if ($this->sameLocationType($file->getLocation())) {
			$success = rename($file->getAbsoluteName(), $this->getAbsolutePrefix() . $target);
		} else {
			$success = $this->copy($file, $target);
			if ($success) {
				$success &= $file->delete();
			} else {
				$this->error('Couldn\'t delete source location file "'.$file->getBaseName.'" after moving to current location !');
			}
		}
		return $success;
	}

	/*
	 * Makes a copy of the passed file to the target location
	 * Makes a simple byte-stream copy of the passed file to the target location
	 *
	 * @param	tx_webconFtptransfer_file	$file: The file which should get stored to the passed target filename
	 * @param	string	$target: A filename where the contents of $file should get put to
	 * @return	boolean	True if the copy procedure succeeded
	 */
	protected function copy(tx_webconFtptransfer_File $file, $target) {
		$success = false;
		if ($this->sameLocationType($file->getLocation())) {
			$success = copy($file->getAbsoluteName(), $this->getAbsolutePrefix() . $target);
		} else {
			$success = $this->heterogenCopy($file, $target);
		}
		if ($success) {
			$sourceSize = $file->getSize();
			$targetSize = filesize($target);
			$success &= ($sourceSize == $targetSize) ? true : false;
		}
		return $success;
	}

	/*
	 * Returns true when the passed location object points to a location of the same location type as $this location
	 *
	 * @param	tx_webconFtptransfer_location	$location: The location object instance to check
	 * @return	boolean	True when the passed location is of the same type than the current location
	 */
	protected function sameLocationType(tx_webconFtptransfer_location $location) {
		if ($location instanceof tx_webconFtptransfer_LocationLocal) {
			return true;
		}
	}

	/*
	 * Makes a copy of the passed file to the target location
	 * Makes a copy between heterogen source and target systems (by using a bytestream or similar method)
	 *
	 * @param	tx_webconFtptransfer_file	$file: The file which should get stored to the passed target filename
	 * @param	string	$target: A filename where the contents of $file should get put to
	 * @return	boolean	True if the copy procedure succeeded
	 */
	protected function heterogenCopy(tx_webconFtptransfer_File $sourceFile, $targetName) {
		$writer = t3lib_div::makeInstance('tx_webconFtptransfer_FileLocal');
		$writer->init($this->rootObject, $this, $this->path, $targetName);
		if (!$reader->openForReading()) {
			$this->error('Couldn\'t open source file "'.$reader->getBaseName().'" for reading!');
			return false;
		}
		if (!$writer->openForWriting()) {
			$this->error('Couldn\'t open target file "'.$writer->getBaseName().'" for writing!');
			return false;
		}
		if ($writer->canReadFromResource() && $reader->hasResource()) {
			$writer->readFromResource($reader->getResource());
		} elseif ($reader->canWriteToResource() && $writer->hasResource()) {
			$reader->writeToResource($writer->getResource());
		} elseif ($reader->canReadChunks() && $writer->canWriteChunks()) {
			while (($chunk = $reader->getChunk()) !== false) {
				if (!$writer->putChunk($chunk)) {
					$this->error('Couldn\'t write to target file "'.$writer->getBaseName().'"! (Disk full?)');
					return false;
				}
			}
		}
		$writer->close();
		$reader->close();
		return true;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/Location/Local.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/Location/Local.php']);
}

?>
