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
 * Interface for tasks which support chaining
 *
 * @author	Bernhard Kraft <kraft@web-consulting.at>
 * @package	TYPO3
 * @subpackage	scheduler
 */
interface tx_scheduler_chainableTask {

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
	public function set_chainData($data);

	/*
	 * Returns an array with chain data for this task upon finishing execution.
	 *
	 * @return	array	Data which will get passed on to next tasks
	 */
	public function get_chainDataElement();

}


?>
