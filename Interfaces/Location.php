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


require_once(t3lib_extMgm::extPath('webcon_ftptransfer').'Classes/File/Abstract.php');

/**
 * Interface defining an location handler
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	webcon_ftptransfer
 */
interface tx_webconFtptransfer_location{

	/*
	 * Initializes this location
	 *
	 * @param	tx_webconFtptransfer_transferFiles	$parentObject: A reference to the parent object instance
	 * @param	string	$path: The path which should get handled by an instance of this object
	 * @param	string	$type: The type of location this instance is used as (source/target/failed)
	 * @return	boolean	Returns true if initalizing this location succeeded (path is fine, etc.)
	 */
	public function init(tx_webconFtptransfer_transferFiles &$parentObject, $path, $type);

	/*
	 * Returns all files which can get transfered FROM this location
	 *
	 * @return array	An array of "tx_webconFtptransfer_file" objects
	 */
	public function getFiles();

	/*
	 * Puts the passed file to this location
	 *
	 * @param	tx_webconFtptransfer_file	$file: The file which should get put to this location
	 * @return	boolean	True if the file has been put/written successfully
	 */
	public function putFile(tx_webconFtptransfer_File $file);

	/*
	 * Returns a string label appropriat for logging about this location
	 *
	 * @return	string	A textual representation for this location
	 */
	public function getLogLabel();


}


?>
