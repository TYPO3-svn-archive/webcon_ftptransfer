..  Editor configuration
	...................................................
	* utf-8 with BOM as encoding
	* tab indent with 4 characters for code snippet.
	* optional: soft carriage return preferred.

.. Includes roles, substitutions, ...
.. include:: _IncludedDirectives.rst

=================
Extension Name
=================

:Extension name: |extension_name|
:Extension key: |extension_key|
:Version: 1.0.0
:Description: manuals covering TYPO3 extension "|extension_name|"
:Language: en
:Author: |author|
:Creation: 21-01-2013
:Generation: |time|
:Licence: Open Content License available from `www.opencontent.org/opl.shtml <http://www.opencontent.org/opl.shtml>`_

The content of this document is related to TYPO3, a GNU/GPL CMS/Framework available from `www.typo3.org
<http://www.typo3.org/>`_


**Table of Contents**

.. toctree::
	:maxdepth: 2

	ProjectInformation
..	UserManual
	AdministratorManual
	TyposcriptReference
	DeveloperCorner
	RestructuredtextHelp

.. STILL TO ADD IN THIS DOCUMENT
	@todo: add section about how screenshots can be automated. Pointer to PhantomJS could be added.
	@todo: explain how documentation can be rendered locally and remotely.
	@todo: explain what files should be versionned and what not (_build, Makefile, conf.py, ...)

What does it do?
=================

This extension allows you to create a scheduler task which is responsible for transfering files locally or to/from an FTP account. All configuration is done from within the task configuration form. A transfer task requires a "source" location and a "target" location. When the task gets executed it will transfer all files which can be found at the "source" location to the "target" location. The "source" location and also the "target" location can be a local directory or a directory on a FTP server. If the transfer to the "target" location doesn't succeed it it possible to have the files get moved to a "failed" location.

.. figure:: Images/screen-scheduler-1.png
		:width: 500px
		:alt: webcon FTP transfer scheduler task configuration

		The scheduler task configuration form for a webcon FTP transfer task


