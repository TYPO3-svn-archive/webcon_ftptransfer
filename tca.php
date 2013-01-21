<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_webconftptransfer_log'] = array(
	'ctrl' => $TCA['tx_webconftptransfer_log']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'filename,filesize,source,target,failed,tstamp,successTargetName,failureTargetName,errors',
	),
	'feInterface' => $TCA['tx_webconftptransfer_log']['feInterface'],
	'columns' => array(
		'source' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:webcon_ftptransfer/locallang.xml:transferFiles.fields.path_source',
			'config' => array(
				'type' => 'input',
				'size' => '60',
			)
		),
		'target' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:webcon_ftptransfer/locallang.xml:transferFiles.fields.path_target',
			'config' => array(
				'type' => 'input',
				'size' => '60',
			)
		),
		'failed' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:webcon_ftptransfer/locallang.xml:transferFiles.fields.path_failed',
			'config' => array(
				'type' => 'input',
				'size' => '60',
			)
		),
		'filename' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:webcon_ftptransfer/locallang.xml:logLabel.filename',
			'config' => array(
				'type' => 'input',
				'size' => '60',
			)
		),
		'filesize' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:webcon_ftptransfer/locallang.xml:logLabel.filesize',
			'config' => array(
				'type' => 'input',
				'size' => '15',
			)
		),
		'successTargetName' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:webcon_ftptransfer/locallang.xml:logLabel.successTargetName',
			'config' => array(
				'type' => 'input',
				'size' => '60',
			)
		),
		'failureTargetName' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:webcon_ftptransfer/locallang.xml:logLabel.failureTargetName',
			'config' => array(
				'type' => 'input',
				'size' => '60',
			)
		),
		'errors' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:webcon_ftptransfer/locallang.xml:logLabel.errors',
			'config' => array(
				'type' => 'text',
				'cols' => '40',
				'rows' => '10',
				'wrap' => 'off',
			)
		),
		'tstamp' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:webcon_ftptransfer/locallang.xml:logLabel.tstamp',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'eval' => 'datetime',
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'tstamp, filename, filesize, successTargetName, failureTargetName, source, target, failed, errors'),
	),
	'palettes' => array(
	)
);


?>
