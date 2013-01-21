=====================
Project Information
=====================

This project was created for a customer of webconsulting in the need of transfering uploaded files to a FTP server. The website featured a multi-page formular where the visitor could upload a PDF document to the webserver. Those PDF files should then get uploaded to a FTP server for further processing. Of course such a task could have been achieved with shell script being executed from a cron daemon. But as this project was developed with TYPO3 and there (AFAIK) are no similar extensions we decided to create such a tool. The result is this extension which of course could easily get extended to support more protocols than just FTP.

If you like to contribute simply contact us at `office@webconsulting.at <office@webconsulting.at>`_


The application logic introduces so called "locations" which are internally represented by location object instances. There are three types of locations: "source", "target" and "failed". A webcon_ftptransfer task will retrieve a list of all files which can be found at the "source" location and then try to transfer them to the "target" location. If transfer of a file from "source" to "target" fails it will try to move or copy it to the "failed" location.

Releases
-----------

* The `current development version`_ can be downloaded from forge.typo3.org subversion repository
* The `latest stable`_ release of this extension can be found at the TYPO3 extension repository

Bugs and Known Issues
-----------------------------------

All current bugs are listed at forge.typo3.org issue tracker issuetracker_
This is also the place where new bugs, feature requests or other issues can get submitted.

To Do
-----------

The following tasks are still open:

* Move the "container" scheduler task into an extension of its own.
* Instead of having two built-in location objects: "local" and "FTP" alter those classes to suit a plugin system which allows other extensions to add transfer methods for SCP, HTTP-POST, WEBDAV, etc.
* Probably make some aspects of location objects configuratble via TS-Config (i.e. handling of files already existing at target location)
* Improve e-mail notification system (better markers, eventually BE-stdWrap, etc.)
* Properly implement the autoloader feature of newer TYPO3 versions

Change Log
-----------

A changelog of the progress for this extension can be found in the `current development version`_ in the file "Changelog.txt"

.. _issuetracker: http://forge.typo3.org/projects/extension-webcon_ftptransfer/issues
.. _latest stable: http://typo3.org/extensions/repository/view/webcon_ftptransfer
.. _current development version: http://forge.typo3.org/projects/extension-webcon_ftptransfer/repository

