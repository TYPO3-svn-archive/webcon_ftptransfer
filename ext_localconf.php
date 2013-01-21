<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE=='BE') {

	require_once(t3lib_extMgm::extPath($_EXTKEY).'Classes/transferFiles.php');
	require_once(t3lib_extMgm::extPath($_EXTKEY).'Classes/additionalFieldProvider.php');
	require_once(t3lib_extMgm::extPath($_EXTKEY).'Classes/container.php');
	require_once(t3lib_extMgm::extPath($_EXTKEY).'Classes/container_additionalFieldProvider.php');

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_scheduler_container'] = array(
		'extension'        => $_EXTKEY,
		'title'            => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:container.name',
		'description'      => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:container.description',
		'additionalFields' => 'tx_scheduler_container_additionalFieldProvider'
	);

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_webconFtptransfer_transferFiles'] = array(
		'extension'        => $_EXTKEY,
		'title'            => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:transferFiles.name',
		'description'      => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:transferFiles.description',
		'additionalFields' => 'tx_webconFtptransfer_additionalFieldProvider'
	);
}

?>
