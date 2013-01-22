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
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-0.0.0',
			'php' => '5.1.0-0.0.0',
			'scheduler' => '1.1.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:71:{s:13:"Changelog.txt";s:4:"4c6b";s:23:"ext_autoload_unused.php";s:4:"32a3";s:12:"ext_icon.gif";s:4:"99b6";s:17:"ext_localconf.php";s:4:"23bb";s:14:"ext_tables.php";s:4:"8822";s:14:"ext_tables.sql";s:4:"597d";s:13:"locallang.xlf";s:4:"7933";s:13:"locallang.xml";s:4:"f021";s:7:"tca.php";s:4:"3fdf";s:8:"TODO.txt";s:4:"7dae";s:35:"Classes/additionalFieldProvider.php";s:4:"759c";s:21:"Classes/container.php";s:4:"09d2";s:45:"Classes/container_additionalFieldProvider.php";s:4:"b1ec";s:26:"Classes/fieldValidator.php";s:4:"ea84";s:38:"Classes/ftpTransfer_fieldValidator.php";s:4:"57c4";s:25:"Classes/transferFiles.php";s:4:"dc0b";s:25:"Classes/File/Abstract.php";s:4:"5ba5";s:20:"Classes/File/Ftp.php";s:4:"fc5b";s:22:"Classes/File/Local.php";s:4:"3880";s:29:"Classes/Location/Abstract.php";s:4:"c987";s:28:"Classes/Location/Chained.php";s:4:"9e8e";s:24:"Classes/Location/Ftp.php";s:4:"4a90";s:26:"Classes/Location/Local.php";s:4:"7247";s:19:"Interfaces/File.php";s:4:"48bf";s:51:"Interfaces/interface.tx_scheduler_chainableTask.php";s:4:"d877";s:23:"Interfaces/Location.php";s:4:"e925";s:39:"Resources/Templates/template-failed.txt";s:4:"4606";s:38:"Resources/Templates/template-fatal.txt";s:4:"fb85";s:40:"Resources/Templates/template-success.txt";s:4:"5e6e";s:12:"doc/Makefile";s:4:"d267";s:14:"doc/manual.pdf";s:4:"f441";s:19:"doc/html/index.html";s:4:"c149";s:20:"doc/html/objects.inv";s:4:"a21e";s:39:"doc/html/_images/screen-container-4.png";s:4:"b880";s:39:"doc/html/_images/screen-container-5.png";s:4:"a970";s:33:"doc/html/_images/screen-log-3.png";s:4:"8297";s:39:"doc/html/_images/screen-scheduler-1.png";s:4:"1dfc";s:39:"doc/html/_images/screen-scheduler-2.png";s:4:"3bfd";s:26:"doc/html/_images/Typo3.png";s:4:"4fac";s:32:"doc/html/_static/ajax-loader.gif";s:4:"ae66";s:26:"doc/html/_static/basic.css";s:4:"e750";s:35:"doc/html/_static/comment-bright.png";s:4:"0c85";s:34:"doc/html/_static/comment-close.png";s:4:"2635";s:28:"doc/html/_static/comment.png";s:4:"882e";s:28:"doc/html/_static/default.css";s:4:"9085";s:28:"doc/html/_static/doctools.js";s:4:"5ff5";s:33:"doc/html/_static/down-pressed.png";s:4:"ebe8";s:25:"doc/html/_static/down.png";s:4:"f6f3";s:25:"doc/html/_static/file.png";s:4:"6587";s:26:"doc/html/_static/jquery.js";s:4:"273e";s:26:"doc/html/_static/minus.png";s:4:"8d57";s:25:"doc/html/_static/plus.png";s:4:"0125";s:29:"doc/html/_static/pygments.css";s:4:"d625";s:31:"doc/html/_static/searchtools.js";s:4:"d550";s:27:"doc/html/_static/sidebar.js";s:4:"521d";s:30:"doc/html/_static/underscore.js";s:4:"9538";s:31:"doc/html/_static/up-pressed.png";s:4:"8ea9";s:23:"doc/html/_static/up.png";s:4:"ecc3";s:30:"doc/html/_static/websupport.js";s:4:"9e61";s:34:"doc/source/_IncludedDirectives.rst";s:4:"37c5";s:34:"doc/source/AdministratorManual.rst";s:4:"ce18";s:18:"doc/source/conf.py";s:4:"315b";s:30:"doc/source/DeveloperCorner.rst";s:4:"3118";s:20:"doc/source/index.rst";s:4:"c3f7";s:33:"doc/source/ProjectInformation.rst";s:4:"5e42";s:40:"doc/source/Images/screen-container-4.png";s:4:"b880";s:40:"doc/source/Images/screen-container-5.png";s:4:"a970";s:34:"doc/source/Images/screen-log-3.png";s:4:"8297";s:40:"doc/source/Images/screen-scheduler-1.png";s:4:"1dfc";s:40:"doc/source/Images/screen-scheduler-2.png";s:4:"3bfd";s:27:"doc/source/Images/Typo3.png";s:4:"4fac";}',
);

?>