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


require_once(t3lib_extMgm::extPath('scheduler').'interfaces/interface.tx_scheduler_additionalfieldprovider.php');

/**
 * Scheduler additional fields for "container"
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	scheduler
 */
class tx_scheduler_container_additionalFieldProvider implements tx_scheduler_AdditionalFieldProvider {
		// This variable contains wheter validation of values was successfull
	protected $validFields = array();

		// An instance of the validator object
	protected $validator = null;

		// A pointer to the scheduler instance
	protected $scheduler = null;

		// This will be set to true when validation errors occur and a flash message has been set
	protected $hasErrors = false;

		// All configuration fields and their type
	protected $fields = array(
		'taskChain' => array(
			'type' => 'recordList',
			'table' => 'tx_scheduler_task',
		),
	);	

	/**
	 * Constructor
	 * Makes an instance of the validator object
	 *
	 * @return	void
	 */
	public function __construct() {
		$this->validator = t3lib_div::makeInstance('tx_webconFtpTransfer_fieldValidator');
	}



	/**
	 * Gets additional fields to render in the form to add/edit a task
	 *
	 * @param	array	Values of the fields from the add/edit task form
	 * @param	tx_scheduler_Task	The task object being eddited. Null when adding a task!
	 * @param	tx_scheduler_Module	Reference to the scheduler backend module
	 * @return	array	A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
	 */
	public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $schedulerModule) {
		$this->scheduler = &$schedulerModule;
		if ($task) {
			$data = $this->getCurrentSettings($taskInfo['scheduler_container'], $task);
		} else {
			$data = $taskInfo['scheduler_container'];
		}
		$data = $this->getDefaults($data);

		$result = array();
		foreach ($this->fields as $fieldName => $fieldConfig) {
			$result[$fieldName] = $this->getFieldSchedulerData($fieldName, $data);
		}
		return $result;
	}

	/**
	 * Validates the additional fields' values
	 *
	 * @param	array	An array containing the data submitted by the add/edit task form
	 * @param	tx_scheduler_Module	Reference to the scheduler backend module
	 * @return	boolean	True if validation was ok (or selected class is not relevant), false otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $schedulerModule) {
		$valid = true;

			// Validate FTP credentials
		$data = $submittedData['scheduler_container'];
		foreach ($this->fields as $field => $config) {
			$valid &= $this->validFields[$field] = $this->validator->validate($data[$field], $config);
		}
		return $valid;
	}


	/**
	 * Takes care of saving the additional fields' values in the task's object
	 *
	 * @param	array	An array containing the data submitted by the add/edit task form
	 * @param	tx_scheduler_Task	Reference to the scheduler backend module
	 * @return	void
	 */
	public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
		foreach ($submittedData['scheduler_container'] as $field => $value) {
			$task->set_option($field, $value);
		}
	}

	
	/*************************************************
	 *
	 * SUPPORT METHODS
	 *
	 * Methods to avoid spaghetti code
	 *
	 *************************************************/

	/*
	 * Sets default values for all parameters
	 *
	 * @param	array	$data: The configuration parameters
	 * @return	array	The passed array with default parameters set if not already defined
	 */
	protected function getDefaults($data) {
		if (!is_array($data)) {
			$data = array();
		}
		return $data;
	}

	/*
	 * Sets current values from task instance without overwriting already set ones
	 *
	 * @param	array	$data: The configuration parameters
	 * @param	object	$task: An instance of the task object
	 * @return	array	The passed array with current task parameters set
	 */
	protected function getCurrentSettings($data, $task) {
		foreach ($this->fields as $field => $config) {
			if ($config['type'] == 'label') {
				continue;
			}
			if (isset($data[$field])) {
				continue;
			}
				// If the field is neither a label nor it has already been passed retrieve it from the
				// task object instance
			$data[$field] = $task->get_option($field);
		}
		return $data;
	}

	/*
	 * Returns the configuration array required for a field to be shown in the scheduler task configuration dialog
	 *
	 * @param	string	$field: The name of the field which should get rendered
	 * @param	array	$data: The current configuration parameter values
	 * @return	array	The configuration array for this field
	 */
	protected function getFieldSchedulerData($field, $data = false) {
		$result = array();
		$result['label'] = 'LLL:EXT:webcon_ftptransfer/locallang.xml:scheduler_container.fields.'.$field;
		if ($data !== false) {
			$result['code'] = $this->getFieldCode($field, $data);
		}
		return $result;
	}

	/*
	 * Creates HTML code for all fields being shown
	 *
	 * @param	string	$field: The name of the field which should get rendered
	 * @param	array	$data: The current configuration parameter values
 	 * @return	array	The HTML code for the input element of this field
	 */
	protected function getFieldCode($field, $data) {
		$content = '';
		$valid = true;
		if (isset($data[$field])) {
				// The reason for this validation is only to display error messages if appropriate
				// Of course the error messages will only get shown if the values are already set by a POST
				// so check if they are set in $data. If this is not the case this is maybe a new fresh form
			$valid = $this->validator->validate($data[$field], $this->fields[$field]);
		}
		if (!$valid) {
			$content .= $this->fieldError($field);
		}
		switch ($this->fields[$field]['type']) {
			case 'label':
			break;
			case 'hostname':
			case 'port':
			case 'FTP_string':
			case 'path':
			case 'email':
			case 'record':
			case 'recordList':
			case 'page':
				if ($this->fields[$field]['fieldExtra'] == 'password') {
					$content .= '<input type="password" name="tx_scheduler[scheduler_container]['.$field.']" value="'.htmlspecialchars($data[$field]).'" />';
				} else {
					$content .= '<input type="text" name="tx_scheduler[scheduler_container]['.$field.']" value="'.htmlspecialchars($data[$field]).'" />';
				}
			break;
			case 'boolean':
				$checked = $data[$field] ? 'checked="checked"' : '';
				$content .= '<input type="hidden" name="tx_scheduler[scheduler_container]['.$field.']" value="0" />';
				$content .= '<input type="checkbox" name="tx_scheduler[scheduler_container]['.$field.']" value="1" '.$checked.' />';
			break;
			default:
				$content .= 'No field code defined!';
			break;
		}
		return $content;
	}

	/*
	 * Creates HTML code showing an error message for a field
	 *
	 * @param	string	$field: The name of the field for which an error message should get shown
 	 * @return	string	The error message
	 */
	protected function fieldError($field) {
		global $LANG;
		$message = $LANG->sL('LLL:EXT:webcon_ftptransfer/locallang.xml:fieldError.field.'.$field);
		if (!$message) {
			$message = $LANG->sL('LLL:EXT:webcon_ftptransfer/locallang.xml:fieldError.type.'.$this->fields[$field]['type']);
		}
		if (!$message) {
			$message = $LANG->sL('LLL:EXT:webcon_ftptransfer/locallang.xml:fieldErrorDefault');
		}
		$title = $LANG->sL('LLL:EXT:webcon_ftptransfer/locallang.xml:fieldErrorTitle');
		$flashMessage = t3lib_div::makeInstance('t3lib_FlashMessage', $message, $title, t3lib_FlashMessage::ERROR);
		if (!$this->hasErrors) {
			$message = $LANG->sL('LLL:EXT:webcon_ftptransfer/locallang.xml:fieldErrorTop');
			$this->scheduler->addMessage($message ,t3lib_FlashMessage::ERROR);
			$this->hasErrors = true;
		}
		return $flashMessage->render();
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/container_additionalFieldProvider.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/webcon_ftptransfer/Classes/container_additionalFieldProvider.php']);
}

?>
