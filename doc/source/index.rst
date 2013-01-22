
.. include:: _IncludedDirectives.rst

=================================
webcon FTP transfer
=================================

:Extension name: |extension_name|
:Extension key: |extension_key|
:Version: 1.0.0
:Description: This extension allows you to create a scheduler task which is responsible for transfering files locally or to/from an FTP account. All configuration is done from within the task configuration form.
:Language: en
:Author: |author|
:Creation: 21-01-2013
:Generation: |time|
:Licence: Open content license available from `www.opencontent.org/opl.shtml <http://www.opencontent.org/opl.shtml>`_

The content of this document is related to TYPO3, a GNU/GPL CMS/framework available from `www.typo3.org <http://www.typo3.org/>`_


What does it do?
=================

This extension allows you to create a scheduler task which is responsible for transfering files locally or to/from an FTP account. All configuration is done from within the task configuration form. A transfer task requires a "source" location and a "target" location. When the task gets executed it will transfer all files which can be found at the "source" location to the "target" location. The "source" location and also the "target" location can be a local directory or a directory on a FTP server. If the transfer to the "target" location doesn't succeed it it possible to have the files get moved to a "failed" location.

.. figure:: Images/screen-scheduler-1.png
		:width: 500px
		:alt: webcon FTP transfer scheduler task configuration

		The scheduler task configuration form for a webcon FTP transfer task

**Table of Contents**

.. toctree::
	:maxdepth: 5

	ProjectInformation
	AdministratorManual
	DeveloperCorner


