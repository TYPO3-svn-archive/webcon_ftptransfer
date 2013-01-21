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
interface tx_webconFtptransfer_File {



	/********************************************
	 * INITIALIZATION
	 *
	 * These methods are used for initializing the object instance
	 ********************************************/

	/*
	 * Initializes this file
	 *
	 * @param	tx_webconFtptransfer_transferFiles	$rootObject: A reference to the root object instance (scheduler task)
	 * @param	tx_webconFtptransfer_location		$locationObject: A reference to the location object where this file can be found at
	 * @param	string	$path: The path where this file is found at
	 * @param	string	$basename: The basename of this file
	 * @return	boolean	Returns true if initalizing this file succeeded
	 */
	public function init(tx_webconFtptransfer_transferFiles &$rootObject, tx_webconFtptransfer_location &$locationObject, $path, $basename);



	/********************************************
	 * OPEN / CLOSE
	 *
	 * Using this methods the file can get opened/closed for successive operations
	 ********************************************/

	/*
	 * Opens this file for reading
	 *
	 * @return	boolean	True if file could get successfully opened or if this isn't necessary for the current type of location
	 */
	public function openForReading();

	/*
	 * Opens a file for writing
	 *
	 * @return	boolean	$append: When this is set the file pointer will be placed to the end of the file (not supported for all location types)
	 * @return	boolean	True if file could get successfully opened or if this isn't necessary for the current type of location
	 */
	public function openForWriting($append);

	/*
	 * Closes the file. After a call to this method no further read/write operations should take place or the outcome will be undefined.
	 *
	 * @return	
	 */
	public function close();



	/********************************************
	 * FILENAME
	 *
	 * Methods which are used for handling the filename
	 ********************************************/

	/*
	 * Returns the basename of this file
	 *
	 * @return string	The name of this file
	 */
	public function getBaseName();

	/*
	 * Returns the absolute name of this file
	 *
	 * @return string	The absolute name of this file
	 */
	public function getAbsoluteName();

	/*
	 * Returns the the location object of this file
	 *
	 * @return tx_webconFtptransfer_Location	A location object where this file is located (the instance has to implement the "tx_webconFtptransfer_location" (location) interface)
	 */
	public function getLocation();



	/********************************************
	 * CHUNK HANDLING
	 *
	 * These methods are required when operating chunkwise on a file
	 ********************************************/

	/*
	 * Returns true if an instance of this file object can read it's data in chunk and return raw data
	 *
	 * @return	boolean	When this is true the "getChunk" method can get used
	 */
	public function canReadChunks();

	/*
	 * Returns true if an instance of this file object can write chunks of data to its resource
	 *
	 * @return	boolean	When this is true the "putChunk" method can get used
	 */
	public function canWriteChunks();

	/*
	 * Returns a chunk of the file for copy operations
	 *
	 * @return	boolean	When this is set to true the internal pointer will be reset to the start of the file
	 * @return	string	A chunk of the file. The size is determined by the non-abstract implementation of this class
	 */
	public function getChunk($fromStart);

	/*
	 * Writes a chunk to the current file
	 *
	 * @return	boolean	True when the chunk could get successfully written to the file
	 */
	public function putChunk($chunk);



	/********************************************
	 * OPERATIONS
	 *
	 * These are common file operations
	 ********************************************/

	/*
	 * Deletes this file
	 *
	 * @return	boolean	True when successfully deleted
	 */
	public function delete();

	/*
	 * Returns the size of the current file or false if it doesn't exist currently
	 *
	 * @return	mixed The size of the current file in bytes or false if it doesn't exist
	 */
	public function getSize();



	/********************************************
	 * RESOURCE
	 *
	 * When interfacing with the file in means of a PHP resource the below methods are responsible
	 ********************************************/

	/*
	 * Returns true if an instance of this file object can read it's data from a resource
	 *
	 * @return	boolean	When this is true the "readFromResource" method can get used
	 */
	public function canReadFromResource();

	/*
	 * Returns true if an instance of this file object can write it's data to a resource
	 *
	 * @return	boolean	When this is true the "writeToResource" method can get used
	 */
	public function canWriteToResource();

	/*
	 * Writes the content of this file by reading its content from the passed resource
	 *
	 * @param	resource The resource from which to read data to be written to this file
	 * @return	boolean	True if success
	 */
	public function readFromResource($resource);

	/*
	 * Reads the content of this file by writing its content to the passed resource
	 *
	 * @param	resource The resource from which to read data to be written to this file
	 * @return	boolean	True if success
	 */
	public function writeToResource($resource);

	/*
	 * Returns true if an instance of this file object can get accessed by a PHP resource
	 *
	 * @return	boolean	When true is returned an instance of this object can get supply a PHP resource by calling the method getResource() which by default returns $this->handle
	 */
	public function hasResource();



	/********************************************
	 * LOGGING
	 *
	 * Methods regarding logging
	 ********************************************/

	/*
	 * Returns a string label appropriat for logging about this file
	 *
	 * @return	string	A textual representation for this file 
	 */
	public function getLogLabel();


}


?>
