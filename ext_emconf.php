<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'webconsulting FTP transfer',
	'description' => 'This extension allows you to create a scheduler task which is responsible for transfering files locally or to/from an FTP account. All configuration is done from within the task configuration form.',
	'category' => 'misc',
	'shy' => 0,
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'alpha',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Kraft Bernhard',
	'author_email' => 'kraft@web-consulting.at',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-',
			'php' => '5.1.0-',
			'scheduler' => '1.1.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>
