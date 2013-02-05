<?php

########################################################################
# Extension Manager/Repository config file for ext "webcon_ftptransfer".
#
# Auto generated 22-01-2013 13:47
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'webconsulting FTP transfer',
	'description' => 'This extension allows you to create a scheduler task which is responsible for transfering files locally or to/from an FTP account. All configuration is done from within the task configuration form. Documentation in new TYPO3 documentation format "ReST" compiled at "doc/manual.pdf" or "doc/html/".',
	'category' => 'misc',
	'shy' => 0,
	'dependencies' => 'scheduler',
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
	'version' => '1.0.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-4.7.5',
			'php' => '5.1.0-5.3.10',
			'scheduler' => '1.1.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>
