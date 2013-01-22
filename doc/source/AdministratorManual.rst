===================================
Administrator manual
===================================

As already explained the extension allows you to transfer files from local or remote directories to another directory. So the application logic introduces so called "locations". There are three types of locations: "source", "target" and "failed". A webcon_ftptransfer task will retrieve a list of all files which can be found at the "source" location and then try to transfer them to the "target" location. If transfer of a file from "source" to "target" fails it will try to move or copy it to the "failed" location.

The first step to take if you wish to use file transfers automated on a time basis is to make sure the "scheduler" extension is installed. The "scheduler" extension is a TYPO3 system extension so it should be shipped with your TYPO3 installation usually. Use the TYPO3 extension manager to install the "scheduler" extension. Of course you can also test the webcon_ftptransfer extension without having a cron job set up. In such a case you would have to manually start the FTP transfer task for testing purposes. But the "scheduler" extension is required in any case as webcon_ftptransfer only makes another scheduler task type available.

When the "scheduler" extension is installed you can install the "webcon_ftptransfer" extension either by fetching it from TER, uploading a T3X or otherwise making the extension directory available in "typo3conf/ext/".


Creating a task
-----------------------------------

.. figure:: Images/screen-scheduler-2.png
		:width: 500px
		:alt: Scheduler module

                Use the scheduler module to create a new webcon_ftptransfer task

If both extensions are properly installed open the "Scheduler" BE module and click on the top icon for creating a new task. If you use an older versions of the scheduler there probably is no "Create new task" icon and you have to select the option "Information" from the drop-down. On the next screen you can choose which type of task should get created. Choose "FTP Transfer (webcon_ftptransfer)".


Transfer task configuration
-----------------------------------

Now the created task has to get configured. The above options are the same as for every scheduler task. Set it according to your needs and the scheduler documentation. There are four sections of configuration for the FTP transfer extension:

* FTP configuration: Allows to set FTP server, port, username and password
* Path configuration: Defines from which "source" location to which "target" location a transfer is performed
* E-Mail addresses: If configured a mail is sent for every successfull/failed file transfer
* Logging configuration: Sets if and where log records should get stored

.. figure:: Images/screen-scheduler-1.png
		:width: 500px
		:alt: webcon FTP transfer scheduler task configuration

		The scheduler task configuration form for a webcon FTP transfer task

In the following sections each of the above configuration settings gets explained in more detail.

FTP configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This section is required if you would like to transfer files from/to an FTP server. You simply have to fill in a valid hostname (i.e. ftp.yourserver.com) the IP port of the FTP control connections (usually port 21) and a valid username/password combination of an account on the FTP server. You can then use a prefix "**ftp:**" in the following path configuration to denote a source/target/failed location on this FTP server.

As the primary goal of this extension was to transfer files from a local web upload directory to an FTP server this way of configuration has been choosen. This could maybe change in future versions. The default syntax for FTP url's in web-browsers and other extensions is "**ftp://username:password@hostnamce:port/path**". So it can be that in future versions of this extension the configuration section for FTP credentials gets droppend and instead full RFC compliant URLs with an "**ftp://**" prefix are allowed.

Path configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

In this section there are three path (locations) which can be configured. The "source" and "target" path are mandatory the "failed" path is optional. Each of the paths can either be a relative or absolute path on the local filesystem. An absolute path is recognized by starting with a slash "/" or "C:\" (or any other drive letter) on Windows. Please be aware that Windows support is not tested at all. If the path doesn't start with a slash it is supposed to be a path relative to the TYPO3 root directory (TYPO3 constant "PATH_site").

If the path starts with a prefix of "**ftp:**" then the path is supposed to be on the FTP server from the above FTP configuration section. Please note that the prefix is only "ftp" followed by a semicolon. No double slash! So a valid target directory could be "ftp:test-upload/" which is relative to the FTP home directory for the configured FTP username/password credentials. Another example would be "ftp:/home/ftp/uploads" which is an absolute path on the remote FTP site. Please be aware that many FTP servers feature a virtual filesystem and do not allow direct access to the servers UNIX/Windows filesystem structure.

So what is the difference between "source", "target" and this mysterious "failed" path - and what are the checkboxes visible in above screenshot for? Here are the answers:


.. _source-path:

Source path
"""""""""""""""""""""""""""""""""""

The "source" location defines a directory from where a list of files is retrieved. If a local filesystem path is used simply all files (not directories) from within the specified path/directory will be queued for transfer. If an FTP location is specified a list of files in this directory is retrieved - every entry is checked for wheter it is a file or a directory and only files will be queued for transfer. So there is (currently) no way to do a recursive transfer including subdirectories of the specified location.

For every file which is found in the source path also a log entry will get created after the transfer - may its outcome be successful ("success"), unsucessfull ("failed") or even "**fatal**". Uhm. "fatal"? What's this now. Thats easy to explain: When a file transfer from "source" to "target" doesn't succeed (probably because the target is a FTP server and it is offline or it's disks are full) then the file will get moved/copied to the "failed" location: Its outcome/result is "failed". But what is if even this transfer to the "failed" location fails? Then this transfer is considered to be "fatal"! No way to move, copy or properly handle the source file. You will recognize such a case as the scheduler task will stop working by returning "false". This means that it also won't get executed next time it is due. So a fatal outcome of a transfer usually disables further operation as its reason usually is improper configuratin.

Target path
"""""""""""""""""""""""""""""""""""

The target path defines the location to which the files from the source path will get copied or moved. If a file will get copied or moved depends on the checkbox right below the target path field: "Move to target location instead of copy". If it is checked the file is move (deleted at the source location). If the checkbox is not checked the file is copied and exists twice afterwards.

One may ask what will happen if the file already exists at the target location. Nifty as programmers are this has also been taken into account (in fact this is the reason why programs always become more and more complicated: hundreds of possible things happen which a standard user did not expect). So if a file already exists at the target location simply a postfix of "_01" will get added to the filename at the target locatin. If a file with the same name gets copied/moved to the target location again (A third time) a postfix of "_02" will get appended. And then "_03", "_04" and so on until "_10". If there are already 11 versions of the same file(name) on the server something seems to be not fine and simply a 5-character random string gets appended to the filename. Please note that the postfix doesn't simply get appended at the end of the filename+extension but rather only to the filename without extensions. So "userDocument.pdf" uploaded a second time becomes "userDocument_01.pdf" and NOT "userDocument.pdf_01".


Failed path
"""""""""""""""""""""""""""""""""""

If the tranasfer of a file from "source" to "target" didn't succeed then this extension will try to copy/move the file to the "failed" location. So if you are trying to upload files from a local directory to a FTP server and the target (FTP server) is offline won't accept the uploaded file for some reason you have the possibility to move it to some other directory on the local machine for taking further actions.

The "failed" path can also be a "ftp:" location of course altough it is suggested to use a local filesystem path for sane reasons.

Here you also have a checkbox labeled "Move to failure location instead of copy" which determines if the file from the "source" location will get moved to the "failed" location (removed from the "source" location) or if a copy is made.


E-Mail addresses
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The concept of "success", "failed" and "fatal" has already been shortly described in above section :ref:`source-path`. Again: When a file gets transfered the result can either be a successful transfer to the "target" location which is considered as "success". Another outcome could be that transfering the file to "target" didn't work but it could get moved to the "failed" location. Then the result is considered "failed". If neither copy/move to "target" nor to "failed" properly works the outcome of this file transfer will be considered "fatal".

Usually the scheduler will notice this by getting a negative (false) result from the FTP transfer task and will stop to further execute an instance of this task. For the purpose of prompt error handling (if for example a required directory on the server got accidentaly deleted by a non-experience admin/user) there is the possibility of having a email notification. Of course you will also get notified of successful transfers if configured.

For this purpose there are three fields which can get filled with an e-mail address (Syntax: "*Recipient Name <recipient@server.com>*"). These are:

* Success e-mail: A mail will get sent to this addresse (addresses) upon successful transfer from "source" to "target".
* Failed e-mail: When transfer from "source" to "target" doesn't succeed an email will get sent to this address
* Fatal e-mail: When neither the transfer to the "target" nor to the "failed" location succeed an email will get sent to the "fatal" e-mail address

Depending on your TYPO3 version a different version of the `SWIFT`_ mailing framework is available in your system. Newer versions of SWIFT allow multiple e-mail addresses as recipient. To use this feature of SWIFT simply separate the multiple e-mail addresses in one of the above webcon_ftptransfer e-mail fields with a comma.


Logging configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The last part of the configuration options is about logging. The checkbox allows you to enable/disable logging at all. When a file gets transfered a database entry will get inserted into the table "tx_webconftptransfer_log". These are common TYPO3 TCA tables and you have to define a PID for them.

.. figure:: Images/screen-log-3.png
		:width: 500px
		:alt: Scheduler module

                The log of a failed FTP transfer

If you view the log records afterwards you will notice that they are read-only. Which of course makes sense for log entries.


Other path prefixes
-----------------------------------

If you read above chapter carefully you will probably already have learned about the "ftp:" prefix which designates paths at a remote FTP server. In fact this prefix takes care of using another location handler than the one for the local filesystem. You can read about location handlers in the next chapter. But there is one more location handler which is shipped with webcon_ftptransfer: The "chain:" location handler. You can use this if you are in jail ... No. Wait. Then you made some other mistake. No. The "chain:" location handler makes only sense in conjunction with container tasks. Two new things at once - this will get complicated so continue to read and you will understand.

Assume the following situation: You want to copy a file to a remote FTP server and on success move it to some other place in the local filesystem. You can easily achieve the copy to the FTP server by using a "ftp:" target location and by not checking the "Move instead of copy" checkbox for the "target" location. But how can you achive the move to abother local directory afterwards. You can not use the "failed" location as this is for handling failures of moving a file: source -> target.

This is where container tasks come into play. Container tasks are a feature which is integrated into webcon_ftptransfer but will probably be integrated in the default TYPO3 scheduler extension. A container task is simply a task where you can set a comma separated list of other tasks in the task configuration interface. See next chapter for more information.

So what we need in the above situation are two webcon_ftptransfer tasks. The first one is responsible for making a copy of each file to the remove FTP server and a second transfer task which is reponsible for moving successfully uploaded files to another location in the local filesystem. And then a third task is required which is responsible for executing the other two tasks in a chained manner. The first two tasks should not get executed automatically on a timed basis but instead be set to get executed once at some already elapsed time. Only the container task should get executed via the scheduler cronjob.

.. figure:: Images/screen-container-5.png
		:width: 500px
		:alt: Scheduler module

                Overview of scheduler tasks showing Container task and two webcon_ftptransfer tasks

But how can you make the second task to only move those files to some other location for which the FTP upload did succeed? If you move away "failed" files to another location you could of course only make a second task which has set the same source directory and moves the remaining (success?) files in to another place. But this is a hole for race conditions. What would happen if someone puts a new file in the "source" location between the first and second task being executed. To overcome such a race condition there is a "chain:" location handler.

In the second task you have to set "chain:success" as the "source" path. Please note that the "chain:" location handler is only valid for source paths. This can get explained by internal implementation details: When a webcon_ftptransfer task is transfering one file after the other from "source" to "target" it registers each file in an array having three subarrays: "success", "failed" and "fatal" depening on the outcome of a file transfer. This array gets passed on to the next task in a chain. If the next task uses a "chain:" location object as "source" it fetches the files to transfer from this array variable. But of course there is no real implementation for storing a file anywhere - so "chain:" location handlers are only valid as "source".

Of course you could add a third task having "chain:failed" as "source" which cares about handling files which didn't succeed to get transfered to "target" by the primary task. It is NOT possible to take actions upon the outcome of secondary or third tasks in a chain.


Container tasks
-----------------------------------

In the previous section the usage of "chain:" location handler was explained. The use of this location handler is strongly coupled with the use of a container task. Currently a container task is some kind of task which is available because webcon_ftptransfer is installed. But maybe this task type will get integrated into future versions of the TYPO3 scheduler extension.

.. figure:: Images/screen-container-4.png
		:width: 500px
		:alt: Scheduler module

                A container task configuration form

When creating a container task the only possible configuration option (except default task configuration fields) is a field in which you have to fill a comma separated list of task UIDs to execute in an ordered chained manner. No matter whether a task returns false or true - every task listed in this field will get executed one after the other.


.. _SWIFT: http://swiftmailer.org/

