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
require_once(t3lib_extMgm::extPath('webcon_ftptransfer').'Classes/File/Ftp.php');

/**
 * Location handler for: FTP
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	webcon_ftptransfer
 */
class tx_webconFtptransfer_LocationFtp extends tx_webconFtptransfer_LocationAbstract implements tx_webconFtptransfer_Location {
		// Member variables for storing files/directories at this location
	protected $files = array();
	protected $directories = array();

		// A timeout for FTP operations
	protected $timeout = 20;

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
		if (substr($this->path, -1) == '/') {
			$this->path = substr($this->path, 0, -1);
		}
		$hostname = $rootObject->get_FtpOption('hostname');
		$port = $rootObject->get_FtpOption('port');
		$username = $rootObject->get_FtpOption('username');
		$password = $rootObject->get_FtpOption('password');

		$this->handle = ftp_connect($hostname, $port, $this->timeout);
		if (!$this->handle) {
			$this->error('Couldn\'t connect to FTP server', t3lib_FlashMessage::WARNING);
			return false;
		}

		if (!@ftp_login($this->handle, $username, $password)) {
			$this->error('Error during login on FTP server', t3lib_FlashMessage::WARNING);
			return false;
		}

		if (!@ftp_chdir($this->handle, $this->path)) {
			$this->error('Error changing FTP directory', t3lib_FlashMessage::WARNING);
			return false;
		}
		$this->absolutePath = @ftp_pwd($this->handle);
			
		return true;
		
	}

	/*
	 * Returns all files which can get transfered FROM this location
	 *
	 * @return array	An array of "tx_webconFtptransfer_File" objects
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
		$tmpFiles = ftp_nlist($this->handle, '-P .');
		foreach ($tmpFiles as $tmpFile)	{
			if (substr($tmpFile, -1) != '/') {
				$file = t3lib_div::makeInstance('tx_webconFtptransfer_FileFtp');
				$file->init($this->rootObject, $this, $this->path, $tmpFile);
				$this->files[] = $file;
			} else {
				$tmpFile = substr($tmpFile, 0, -1);
				if (($tmpFile != '.') && ($tmpFile != '..')) {
//						// Sub-Directories are currently not handled (recursion)
//					$directory = t3lib_div::makeInstance(__CLASS__);
//					$directory->init($this->rootObject, $this, $this->path, $tmpFile);
//					$this->directories[] = $directory;
				}
			}
		}
	}

	/*
	 * Returns true when a file which has a name as the passed file object already exists at this location
	 *
	 * @param	tx_webconFtptransfer_File	$file: The file whose basename should be checked for local existence
	 * @return	boolean	When the basename of the passed file object instance already exists at the location this location object instance points to true will get returned
	 */
	protected function fileExists(tx_webconFtptransfer_File $file) {
		if (!($file instanceof tx_webconFtptransfer_File)) {
			$this->error('Passed argument is does not implement the "tx_webconFtptransfer_File" interface!', t3lib_FlashMessage::ERROR);
			return false;
		}
		return (ftp_mdtm($this->handle, $file->getBaseName())) >= 0 ? true : false;
	}

	/*
	 * Returns a random string for generating random filename parts
	 *
	 * @param	integer	$length: The length of the random string to generate
	 * @return	string	A random string of $length characters
	 */
	protected function getRandomString($length) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$lastPos = strlen($chars)-1;
		$result = '';
		for ($i = 0; $i < 0; $i++) {
			$r = rand(0, $lastPos);
			$result .= substr($chars, $r, 1);
		}
		return $result;
	}

	/*
	 * Generate an alternate filename for a filename which does already exist at this location
	 *
	 * @param	tx_webconFtptransfer_File	$file: The file for which a new target name should get generated
	 * @return	string	An alternate name file name
	 */
	protected function getAlternateTargetName(tx_webconFtptransfer_File $file) {
		$name = $file->getBaseName();
		if (ftp_mdtm($this->handle, $name) >= 0) {
			list($ext, $body) = explode('.', strrev($name), 2);
			$body = strrev($body);
			$ext = strrev($ext);
			$cnt = 1;
			do {
				$name = $body . '_' . sprintf("%02d", $cnt) . '.' . $ext;
			} while ( (($nameOK = ftp_mdtm($this->handle, $name)) >= 0) && ($cnt++ < 10) );
			if ($nameOK >= 0) {
				$cnt = 0;
				do {
					$random = $this->getRandomString(5);
					$name = $body . '_' . $random . '.' . $ext;
				} while ( (($nameOK = ftp_mdtm($this->handle, $name)) >= 0) && ($cnt++ < 10) );
				if ($nameOK >= 0) {
					die('Whattheheck?');
				}
			}
		}
		return $name;
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
	 * @param	tx_webconFtptransfer_File	$file: The file which should get moved to the passed target filename
	 * @param	string	$target: A filename where the file $file should get put to
	 * @return	boolean	True if the move procedure succeeded
	 */
	protected function move(tx_webconFtptransfer_File $file, $target) {
		$success = false;
		if ($this->sameLocationType($file->getLocation())) {
				// TODO: Implement FTP 2 FTP transfer (move)
				// Moving a file could even get more simplified if both FTP locations have the same host/port/user/password
				// as it is very unlikely that two connections to the same host/port/user/password get presented a different (virtual) filesystem
//			$success = rename($file->getAbsoluteName(), $targetName);
		} else {
			$success = $this->copy($file, $target);
			if ($success) {
				$success &= $file->delete();
			} else {
				$this->error('Couldn\'t delete source location file "'.$file->getBaseName.'" after moving to current location !', t3lib_FlashMessage::WARNING);
			}
		}
		return $success;
	}

	/*
	 * Makes a copy of the passed file to the target location
	 * Depending on the locationObject type of the passed file different copy alogrythms can be choosen.
	 *
	 * @param	tx_webconFtptransfer_File	$file: The file which should get stored to the passed target filename
	 * @param	string	$target: A filename where the contents of $file should get put to
	 * @return	boolean	True if the copy procedure succeeded
	 */
	protected function copy(tx_webconFtptransfer_File $file, $target) {
		$success = false;
		if ($this->sameLocationType($file->getLocation())) {
				// TODO: Implement FTP 2 FTP transfer (copy)
				// With FTP it is possible to move a file from one server to another without having it transfered to the local machine.
				// To implement such a feature here a control connection must be open to both servers (using an instance of this object)
				// and one of the servers (try both variants?) has to be put into "PASV" mode. The other server has to get notified of
				// the IP and port of the other passive server using the PORT(?) command.
//			$success = copy($file->getAbsoluteName(), $target);
		} else {
			$success = $this->heterogenCopy($file, $target);
		}
		if ($success) {
			$sourceSize = $file->getSize();
			$targetSize = ftp_size($this->handle, $target);
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
		return false;
			// When this returns true (both locations are FTP) it should be possible to
			// move/copy the files around using clever FTP commands without having to
			// stream all data over the machine where this application runs on.
/*
		if ($location instanceof __CLASS__) {
			return true;
		}
*/
	}

	/*
	 * Makes a copy of the passed file to the target location
	 * Makes a copy between heterogen source and target systems (by using a bytestream or similar method)
	 *
	 * @param	tx_webconFtptransfer_File	$file: The file which should get stored to the passed target filename
	 * @param	string	$target: A filename where the contents of $file should get put to
	 * @return	boolean	True if the copy procedure succeeded
	 */
	protected function heterogenCopy(tx_webconFtptransfer_File $reader, $targetName) {
		$writer = t3lib_div::makeInstance('tx_webconFtptransfer_FileFtp');
		$writer->init($this->rootObject, $this, $this->path, $targetName);
		if (!$reader->openForReading()) {
			$this->error('Couldn\'t open source file "'.$reader->getBaseName().'" for reading!');
			return false;
		}
		if (!$writer->openForWriting()) {
			$this->error('Couldn\'t open target file "'.$writer->getBaseName().'" for writing!');
			return false;
		}
		$ok = true;
		if ($writer->canReadFromResource() && $reader->hasResource()) {
			$ok = $writer->readFromResource($reader->getResource());
		} elseif ($reader->canWriteToResource() && $writer->hasResource()) {
			$ok = $reader->writeToResource($writer->getResource());
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
		return $ok;
	}

	/*
	 * This method can get used to retrieve the handle of the FTP connection
	 *
	 * @return	resource	A FTP connection handle
	 */
	public function getConnectionHandle() {
		return $this->handle;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/Location/Ftp.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/Location/Ftp.php']);
}

?>
