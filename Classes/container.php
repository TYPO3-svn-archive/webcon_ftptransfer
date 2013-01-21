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

/**
 * Scheduler container task
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	scheduler
 */
class tx_scheduler_container extends tx_scheduler_Task implements tx_scheduler_chainableTask {
	private $chainData = array();

		// Configuration options. Get also set from the task additionalFieldProvider class and will be stored in serialized task.
	private $configOptions = array();
	private $allowedConfigOptions = array('taskChain');

	/*
	 * The constructor. Simply calls the parent constructor and initializes class variables
	 *
	 * @return	void
	 */
	public function __construct() {
		parent::__construct();
	}

	/*
	 * Gets called by the scheduler for executing this tasks logic
	 *
	 * @return	boolean	Should return true if the task finished successfully so it should get run again
	 */
	public function execute() {
		$taskChain = $this->get_option('taskChain');
		$taskUids = t3lib_div::trimExplode(',', $taskChain, 1);
		$this->chainData[__CLASS__]['tasksCount'] = count($taskUids);
		$cnt = 0;
		foreach ($taskUids as $taskUid) {
			$this->chainData[__CLASS__]['taskIndex'] = $cnt++;
			$this->executeChainedTask($taskUid);
		}

			// Return wheter this scheduler task succeeded.
		return true;
	}

	/*
	 * Executes a single chained task
	 *
	 * @param	integer	$taskUid: The uid of the task to execute
	 * @return	void
	 */
	protected function executeChainedTask($taskUid) {
                        	// Try getting the next task and execute it
                	try {
				$task = $this->scheduler->fetchTask($taskUid);
				$taskRecord = $this->scheduler->fetchTaskRecord($taskUid);
				$taskKey = get_class($task);
				try {
					if ($task instanceof tx_scheduler_chainableTask) {
						$task->set_chainData($this->chainData);
					}
					$this->scheduler->executeTask($task);
					if ($task instanceof tx_scheduler_chainableTask) {
						$this->chainData[$taskKey] = $task->get_chainDataElement();
					}
				}
				catch (Exception $e) {
						// We ignore any exception that may have been thrown during execution,
						// as this is a background process.
						// The exception message has been recorded to the database anyway
					continue;
				}
			}
			catch (OutOfBoundsException $e) {
	                        	// There are no more tasks for this container
				break;
			}
				// A task could not be unserialized properly, skip to next task
			catch (UnexpectedValueException $e) {
				continue;
			}
	}



	/*************************************************
	 *
	 * GETTER and SETTER METHODS
	 *
	 * Methods for getting and setting options
	 *
	 *************************************************/

	/*
	 * Sets a configuration option from the additional field provider
	 *
	 * @param	string	$type: The configuration option which should get set
	 * @param	string	$value: The value to which the option should get set
	 * @return	boolean	True if the option was set successfully or false if the passed option is not a valid configuration option
	 */
	public function set_option($option, $value) {
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
	public function get_option($option) {
		if (in_array($option, $this->allowedConfigOptions)) {
			return isset($this->configOptions[$option]) ? $this->configOptions[$option] : '';
		}
		return false;
	}

	/*************************************************
	 *
	 * INTERFACE: tx_scheduler_chainableTask
	 *
	 * Methods implementing the chainableTask interface
	 *
	 *************************************************/

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
		$this->chainData = $data;
	}

	/*
	 * Returns an array with chain data for this task upon finishing execution.
	 *
	 * @return	array	Data which will get passed on to next tasks
	 */
	public function get_chainDataElement() {
		return array();
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/container.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/container.php']);
}

?>
