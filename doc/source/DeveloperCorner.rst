===================================
Developer corner
===================================

In the previous chapter(s) location objects were already mentioned a few times. So what are these location objects and how can I create a new one. Currently there are location object to handle local locations, FTP locations and chained locations. A local location is simply a directory on your local machine filesystem. Of course this directory could be a SMB or NFS mount in UNIX - the extension doesn't care about this. The FTP location object is used for accessing files on a (remote) FTP server. And the chained locations make sense for handling the results of a previous webcon_ftptransfer task in conjunction with container tasks.

If you require something similar as featured by this extension: Copy the contents of a directory to another location on a timed regular basis but using another protocol (i.e: SCP or RSYNC) then what you need is a new location handler object. Currently location handlers are not implemented as pluggable system. So if you create a news location handler you will have to modify the source code of webcon_ftptransfer to recognize your new location handler. Maybe future versions feature some kind of plugin system which allows to simply add new location handlers by creating a php class for them and registering them within the webcon_ftptransfer extension by adding an entry to a TYPO3_CONF_VARS subarray.

Another feature which was already written about in the previous chapter are "Container Tasks". These are tasks which execute other tasks upon their own execution. Their sole purpose is to act as container for other tasks allowing them to pass data from one to the other. You will probably notice that such a task could get used for many purposes and does surely not belong to the webcon_ftptransfer extension. Effort is underway to make such container tasks available by default in the TYPO3 scheduler extension.

If you have any other questions regarding the development of this extension or you require professional support don't hesitate to contact us at `office@webconsulting.at <office@web-consulting.at>`_

