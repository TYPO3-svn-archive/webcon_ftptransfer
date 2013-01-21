
CREATE TABLE tx_webconftptransfer_log (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,

	source varchar(255) DEFAULT '' NOT NULL,
	target varchar(255) DEFAULT '' NOT NULL,
	failed varchar(255) DEFAULT '' NOT NULL,
	filename varchar(255) DEFAULT '' NOT NULL,
	filesize int(11) signed DEFAULT '0' NOT NULL,

	successTargetName varchar(255) DEFAULT '' NOT NULL,
	failureTargetName varchar(255) DEFAULT '' NOT NULL,
	
	errors text NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

