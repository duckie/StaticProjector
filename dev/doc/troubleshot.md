#Troubleshoot#

##### The CSS files or the thumbnails or any other data which are not processed by Static Projector cannot be accessed. There is an error such as "Internal server error" or "Access denied" #####

_Static Projector_ creates some _.htaccess_ files in order to protect by default the directories which are created. But it cannot guess which type of configuration inside your .htaccess files is allowed or not, so it may use some options forbidden on your server. Please check the following files to correct the problem:

* _data/.htaccess_ if exists
* _web-data/styles/.htaccess_
* _cache/web/.htaccess_

The first thing you can try is delete the "Options [...]" line you dont now what to do. You can also have a look at these but they should not create any conflict since they should not be readable from the web:

* _cache/.htaccess_
* _web-data/data/.htaccess_

Please notice that once you modified these files, _Static Projector_ lets them as they are. You dont have to configure anything else.
