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

require_once(t3lib_extMgm::extPath('scheduler').'class.tx_scheduler_task.php');

	// We need the fieldValidator to know about the FTP path prefix
$extPath = t3lib_extMgm::extPath('webcon_ftptransfer');
require_once($extPath.'Interfaces/interface.tx_scheduler_chainableTask.php');
require_once($extPath.'Classes/ftpTransfer_fieldValidator.php');

	// Include location handler objects
require_once($extPath.'Classes/Location/Local.php');
require_once($extPath.'Classes/Location/Ftp.php');
require_once($extPath.'Classes/Location/Chained.php');

/**
 * Scheduler task for webcon_ftptransfer
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	webcon_ftptransfer
 */
class tx_webconFtptransfer_transferFiles extends tx_scheduler_Task implements tx_scheduler_chainableTask {
		// Options which are allowed for FTP. Get set from withint the task configuration and stored in the serialized task.
	private $ftpOptions = array();
	private $allowedFtpOptions = array('hostname', 'port', 'username', 'password');

		// Options which are define the used paths. (Stored in serialized task)
	private $pathOptions = array();
	private $allowedPathOptions = array('source', 'target', 'failed');

		// Configuration options. Get also set from the task configuration class and will be stored in serialized task.
	private $configOptions = array();
	private $allowedConfigOptions = array('logPid', 'doLog', 'emailSuccess', 'emailFailed', 'emailFatal', 'emailSender', 'targetMove', 'failedMove');


		// This class variable will store error log messages
	private $errors = array();

		// Will contain all handled files depending on the outcome of their transfer
	private $transferedFiles = array(
		'target' => array(),
		'failed' => array(),
		'fatal' => array(),
	);
		// If this task is called as second or later element in a chain (using the tx_scheduler_container) then this variable
		// will contain the "transferedFiles" of the last chain element
	private $chainTransferedFiles = false;


		// Class member properties concerning logging. Will get filled from task configuration.
	private $doLog = false;
	private $logPid = 0;

		// Class member properties about email sending. Will get filled from task configuration.
	private $emailSuccess = '';
	private $emailFailed = '';
	private $emailFatal = '';

		// If this task is called in a chain it will contain the information set from the previous chain element
	private $chainParentInfo = array();

	private $newSwiftVersion = false;

	/*
	 * The constructor. Simply calls the parent constructor and initializes class variables
	 *
	 * @return	void
	 */
	public function __construct() {
		parent::__construct();
	}

	/*
	 * Magic method "__sleep". This method will get called prior to serialization and will return all
	 * member variables which have to get serialized.
	 *
	 * @return	void
	 */
	public function __sleep() {
			// Remove errors collected during this task run.
		$this->errors = array();
		return array('taskUid', 'disable', 'execution', 'executionTime', 'ftpOptions', 'pathOptions', 'configOptions');
	}

	/*
	 * Initalizes internally used variables from the configuration variables which get stored in this class while serialized
	 *
	 * @return	void
	 */
	protected function initConfig() {
			// Set configuration variables
		$this->doLog = intval($this->get_ConfigOption('doLog')) ? true : false;
		$this->logPid = intval($this->get_ConfigOption('logPid'));
		$this->emailSuccess = $this->parseEmailAddresses($this->get_ConfigOption('emailSuccess'));
		$this->emailFailed = $this->parseEmailAddresses($this->get_ConfigOption('emailFailed'));
		$this->emailFatal = $this->parseEmailAddresses($this->get_ConfigOption('emailFatal'));
		$this->emailSender = $this->parseEmailAddresses($this->get_ConfigOption('emailSender'));

		$swiftVersion = file_get_contents(PATH_site.'typo3/contrib/swiftmailer/VERSION');
		$swiftVersion = str_replace('Swift-', '', $swiftVersion);
		$swiftVersion = t3lib_utility_VersionNumber::convertVersionNumberToInteger($swiftVersion);
		if ($swiftVersion >= 4000000) {
			$this->newSwiftVersion = true;
		}
	}

	/*
	 * Parses an E-Mail address (or addresses) into an array of email=>name elements
	 *
	 * @return	void
	 */
	protected function parseEmailAddresses($emails) {
		$parts = t3lib_div::trimExplode(',', $emails, true);
		$result = array();
		if ($this->newSwiftVersion) {
			foreach ($parts as $email) {
				if (preg_match('/^([^<]+)<([^>]+@[^>]+)>\s*$/', $email, $matches)) {
					if (t3lib_div::validEmail($matches[2])) {
						$result[] = array($matches[2] => $matches[1]);
					}
				} else {
					if (t3lib_div::validEmail($email)) {
						$result[] = array($email);
					}
				}
			}
			if (count($result) == 1) {
					// If there is only ONE email address don't put it in an array
				$result = array_pop($result);
			}
		} else {
			foreach ($parts as $email) {
				if (preg_match('/^([^<]+)<([^>]+@[^>]+)>\s*$/', $email, $matches)) {
					if (t3lib_div::validEmail($matches[2])) {
						$result[] = $matches[2];
					}
				} else {
					if (t3lib_div::validEmail($email)) {
						$result[] = $email;
					}
				}
			}
			$result = array_shift($result);
		}
		return $result;
	}


	/*
	 * Gets called by the scheduler for executing this tasks logic
	 *
	 * @return	boolean	Should return true if the task finished successfully so it should get run again
	 */
	public function execute() {
			// Initialize configuration variables
		$this->initConfig();
			// Will be the return value of this method
		$finishedWithoutFatalErrors = true;

		if ($this->initLocations()) {
				// Retrieve all files which have to get transfered
			$files = $this->location['source']->getFiles();
				// Iterate over all files
			foreach ($files as $file) {
					// Aggregate fatal errors
				$finishedWithoutFatalErrors &= $this->transferFile($file);
			}
		} else {
			$result = $this->initLogRow();
				// Log to database
			if ($this->doLog) {
				$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_webconftptransfer_log', $result);
			}
			if (count($this->emailFatal)) {
				$this->sendMail('fatal', $this->emailFatal, $result);
			}
			$finishedWithoutFatalErrors = false;
		}
			// Return wheter this scheduler task succeeded. Only a fatal error will yield in returning "false"
		return $finishedWithoutFatalErrors;
	}


	/*
	 * Takes care of transfering a single file to the target
	 *
	 * @param	tx_webconFtptransfer_File	$file: An instance of a file object retrieved from the source object
	 * @return	boolean	Returns true if the transfer to the target (or at least to the "failed" target) succeeded
	 */
	protected function transferFile($file) {
			// Reset variables used in this loop
		$failureTargetName = false;
		$this->errors = array();

			// Put file to target location
			// Files get retrieved from the source location. Then the below method "putFile" from the
			// target location is used to store the file. So the target location has to take care to
			// read the file from the source location. In some cases this is not possible. For example
			// the FTP handler class can only interact with files in the filesystem - or to be more
			// precise: with files in the filesystem OR stream resources. But it does not provide
			// stream resources or read/write methods.
			// So when a file should get read from an FTP location and get stored to the local filesystem
			// then the target class (locationLocal) has to take care to retrieve the file from the FTP
			// class. This is not possible. Instead code of the FTP class (method) would have to get called
			// to store the file somewhere in the local filesystem (parameter).
			// So this case is controverse to the below convention that the target location (object/method)
			// gets a paramter which represents the source.
			// So here we only have the source file object and the target location object. But internally the
			// target location object creats an instance for an target file object. Then the game can be
			// turned around and the source location (file) can get asked to write the data to the target file.
			// This is done in the "heterogenCopy" method of the location instance.
		$successTargetName = $this->location['target']->putFile($file);
		if ($successTargetName !== false) {
			$this->transferedFiles['target'][] = $file;
		} else {
				// If putting file didn't succeed try to put it to the failed location
			$failureTargetName = $this->location['failed']->putFile($file);
			if ($failureTargetName !== false) {
				$this->transferedFiles['failed'][] = $file;
			} else {
				$this->transferedFiles['fatal'][] = $file;
			}
		}

			// Put together information about handling of this file
		$result = $this->initLogRow();
		$result['filename'] = $file->getLogLabel();
		$result['filesize'] = $file->getSize();
		$result['successTargetName'] = $successTargetName !== false ? $successTargetName : '';
		$result['failureTargetName'] = $failureTargetName !== false ? $failureTargetName : '';

			// Log to database
		if ($this->doLog) {
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_webconftptransfer_log', $result);
		}

			// Handle sending of emails
		if (($successTargetName !== false) && count($this->emailSuccess)) {
			$this->sendMail('success', $this->emailSuccess, $result);
		}
		if (($successTargetName === false) && ($failureTargetName !== false) && count($this->emailFailed)) {
			$this->sendMail('failed', $this->emailFailed, $result);
		}
		if (($successTargetName === false) && ($failureTargetName === false) && count($this->emailFatal)) {
			$this->sendMail('fatal', $this->emailFatal, $result);
		}

		return ($successTargetName !== false) || ($failureTargetName !== false);
	}

	/*
	 * Initializes the source/target/failed location object instances
	 *
	 * @return	boolean	Returns true if initalizing all three instances did succeed
	 */
	protected function initLocations() {
			// Prepare location objects
		$initSuccess = true;
		$initSuccess &= $this->prepareLocation('source');
		$initSuccess &= $this->prepareLocation('target');
		$initSuccess &= $this->prepareLocation('failed');
		return $initSuccess;
	}

	/*
	 * Returns an array containing fields for a log row with initial content being the same for all log rows (current time, etc.)
	 *
	 * @return	array	An array which can get stored in the webconf_ftptransfer log row
	 */
	protected function initLogRow() {
		return array(
			'pid' => $this->logPid,
			'source' => $this->location['source']->getLogLabel(),
			'target' => $this->location['target']->getLogLabel(),
			'failed' => $this->location['failed']->getLogLabel(),
			'tstamp' => time(),
			'errors' => implode(chr(10), $this->errors),
		);
	}

	/*
	 * This method creates an instance of a transfer object (implements tx_webconFtpTransfer_location) and initializes
	 * the object instance with a reference to the current object and the path it should handle.
	 *
	 * @param	string	The type of location object to make an instance for (source/target/failed)
	 * @return	boolean	Wheter initializing the location did succeed
	 */
	protected function prepareLocation($type) {
		$ftpPathPrefix = tx_webconFtpTransfer_fieldValidator::$ftpPathPrefix;
		$chainPathPrefix = tx_webconFtpTransfer_fieldValidator::$chainPathPrefix;
		$path = $this->get_PathOption($type);
		if (substr($path, 0, strlen($ftpPathPrefix)) == $ftpPathPrefix) {
			$path = substr($path, strlen($ftpPathPrefix));
			$this->location[$type] = t3lib_div::makeInstance('tx_webconFtpTransfer_locationFtp');
		} elseif (substr($path, 0, strlen($chainPathPrefix)) == $chainPathPrefix) {
			$path = substr($path, strlen($chainPathPrefix));
			$this->location[$type] = t3lib_div::makeInstance('tx_webconFtpTransfer_locationChained');
		} elseif ($path) {
			$this->location[$type] = t3lib_div::makeInstance('tx_webconFtpTransfer_locationLocal');
		} else {
			$this->set_Error('No object for handling "'.$type.'" location!', t3lib_FlashMessage::ERROR);
			return false;
		}
		if (!($this->location[$type] instanceof tx_webconFtpTransfer_location)) {
			$this->set_Error('Object for handling "'.$type.'" location does not implement "tx_webconFtptransfer_location" interface!', t3lib_FlashMessage::ERROR);
			return false;
		}
			// Initialize the location (FTP connect, etc.)
		return $this->location[$type]->init($this, $path, $type);
	}

	/*
	 * Sends a mail about the outcome of a file transfer
	 *
	 * @param	string The type of mail being sent (success/failed/fatal)
	 * @param	string E-Mail address a mail should be sent to
	 * @param	array	The result data for the file transfer (same data which is stored to log table)
	 * @return	void
	 */
	protected function sendMail($type, $email, $data) {
		global $LANG;
		$template = t3lib_div::getURL(t3lib_extMgm::extPath('webcon_ftptransfer').'Resources/Templates/template-'.$type.'.txt');
		if (strlen($template)) {
			$message = t3lib_parsehtml::substituteMarkerArray($template, $data, '###|###');
			$mail = t3lib_div::makeInstance('t3lib_mail_message');
			if (count($this->emailSender)) {
				$mail->setFrom($this->emailSender);
			}
			$mail->setTo($email);
			$mail->setSubject($LANG->sL('LLL:EXT:webcon_ftptransfer/locallang.xml:mailSubject.'.$type));
			$mail->setBody($message);
			$mail->send();
		}
	}

	/*
	 * Returns true if files should get moved instead of making a copy
	 *
	 * @return	boolean	True if files should get moved
	 */
	public function moveInsteadOfCopy($type) {
		return intval($this->get_ConfigOption($type.'Move')) ? true : false;
	}

	/*************************************************
	 *
	 * GETTER and SETTER METHODS
	 *
	 * Methods for getting and setting FTP/path options
	 *
	 *************************************************/

	/*
	 * Sets FTP options
	 *
	 * @param	string	$type: The FTP option which should get set
	 * @param	string	$value: The value to which the option should get set
	 * @return	boolean	True if the option was set successfully or false if the passed option is not a valid FTP option
	 */
	public function set_FtpOption($option, $value) {
		if (in_array($option, $this->allowedFtpOptions)) {
			$this->ftpOptions[$option] = $value;
			return true;
		}
		return false;
	}

	/*
	 * Retrieves a FTP option
	 *
	 * @param	string	$type: The FTP option which should get retrieved
	 * @return	mixed	The FTP option which has been requested or an empty string if it isn't set or false if the requested option is not a valid FTP option
	 */
	public function get_FtpOption($option) {
		if (in_array($option, $this->allowedFtpOptions)) {
			return isset($this->ftpOptions[$option]) ? $this->ftpOptions[$option] : '';
		}
		return false;
	}

	/*
	 * Sets a path option
	 *
	 * @param	string	$type: The path option which should get set
	 * @param	string	$value: The value to which the option should get set
	 * @return	boolean	True if the option was set successfully or false if the passed option is not a valid path option
	 */
	public function set_PathOption($option, $value) {
		if (in_array($option, $this->allowedPathOptions)) {
			$this->pathOptions[$option] = $value;
			return true;
		}
		return false;
	}

	/*
	 * Retrieves a path option
	 *
	 * @param	string	$type: The path option which should get retrieved
	 * @return	mixed	The path option which has been requested or an empty string if it isn't set or false if the requested option is not a valid path option
	 */
	public function get_PathOption($option) {
		if (in_array($option, $this->allowedPathOptions)) {
			return isset($this->pathOptions[$option]) ? $this->pathOptions[$option] : '';
		}
		return false;
	}

	/*
	 * Sets a configuration option
	 *
	 * @param	string	$type: The configuration option which should get set
	 * @param	string	$value: The value to which the option should get set
	 * @return	boolean	True if the option was set successfully or false if the passed option is not a valid configuration option
	 */
	public function set_ConfigOption($option, $value) {
		if (in_array($option, $this->allowedConfigOptions)) {
			$this->configOptions[$option] = $value;
			return true;
		}
		return false;
	}

	/*
	 * Retrieves a configuration option
	 *
	 * @param	string	$type: The configuration option which should get retrieved
	 * @return	mixed	The configuration option which has been requested or an empty string if it isn't set or false if the requested option is not a valid configuration option
	 */
	public function get_ConfigOption($option) {
		if (in_array($option, $this->allowedConfigOptions)) {
			return isset($this->configOptions[$option]) ? $this->configOptions[$option] : '';
		}
		return false;
	}

	/*
	 * Sets an error message
	 *
	 * @param	string	$message: An error message
	 * @param	integer	$severity: The error severity (Values taken from t3lib_FlashMessage)
	 * @return	void
	 */
	public function set_Error($message, $severity = t3lib_FlashMessage::NOTICE) {
		switch ($severity) {
			case t3lib_FlashMessage::NOTICE:
				$severityStr = 'NOTICE';
			break;
			case t3lib_FlashMessage::INFO:
				$severityStr = 'INFO';
			break;
			case t3lib_FlashMessage::OK:
				$severityStr = 'OK';
			break;
			case t3lib_FlashMessage::WARNING:
				$severityStr = 'WARNING';
			break;
			case t3lib_FlashMessage::ERROR:
				$severityStr = 'ERROR';
			break;
			default:
				$severityStr = 'UNKNOWN';
			break;
		}
		$this->errors[] = $severityStr.': '.$message;
	}

	/*
	 * Sets the chain data
	 * When task are chained the container task will check if a task implements "tx_schedulder_chainableTask".
	 * If this is the case it sets data on the new task instance by using this method.
	 * The information from each task is contained in a subarray which has the task classname as array key.
	 *
	 * The container task itself automatically sets the following variables:
	 * $data['tx_scheduler_container']['tasksCount'] ... Number of all tasks in this chain
	 * $data['tx_scheduler_container']['taskIndex'] ... Index of the current task (for which this method is called)
	 *
	 * @param	mixed	$data: Data which is passed along the tasks. 
	 * @return	void
	 */
	public function set_chainData($data) {
		if (is_array($data[__CLASS__]) && count($data[__CLASS__])) {
			$this->chainTransferedFiles = $data[__CLASS__];
		}
	}

	/*
	 * Returns an array with chain data for this task upon finishing execution.
	 *
	 * @return	array	Data which will get passed on to next tasks
	 */
	public function get_chainDataElement() {
		if ($this->chainTransferedFiles) {
			return $this->chainTransferedFiles;
		} else {
			return $this->transferedFiles;
		}
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/transferFiles.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/transferFiles.php']);
}

?>
