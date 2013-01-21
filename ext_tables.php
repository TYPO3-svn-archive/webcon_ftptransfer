<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_webconftptransfer_log'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:webcon_ftptransfer/locallang.xml:tx_webconftptransfer_log',
		'label' => 'filename',
		'label_alt' => 'source,target',
		'label_alt_force' => 1,
		'tstamp'    => 'tstamp',
		'default_sortby' => 'ORDER BY tstamp DESC',
		'readOnly' => 1,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif',
	),
	'feInterface' => array(
		'fe_admin_fieldList' => 'filename,filesize,source,target,failed,tstamp,successTargetName,failureTargetName,errors',
	)
);

?>
