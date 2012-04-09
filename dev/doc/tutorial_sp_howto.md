The Static Projector HowTo step by step
========

#Requirements#

You need a full web stack with PHP5 for Static Projector to run. Most of web hosts uses Apache + PHP5 + MySQL. Static Projector is only written in PHP so the Apache web server is not mandatory. But please notice that the software has **not** been tested with another web server and that the sofwtare writes some _.htaccess_ files by default. Those are not mandatory but useful to protect some folders that web visitors do not need to see. Any feedback on another web server than Apache is welcome.

Note for Linux/Unix users: if you plan to install Static Projector on your local computer or on a dedicated server fully managed by yourself, please notive that you will have to deal with user and group permission between the Apache server runtime and your user, since Static Projector generates a lot of files for you to modify and to cache some data. This tutorial does **not** cover this part.

#Step 1: Getting StaticProjector#

##Method 1: from zip archive##

Download the zip file [here](linkto) and uncompress it into a folder.

##Method 2: from git repository##

__Please be careful__ cause there some steps over just cloning the repository. We suppose you have git installed on your computer and a Unix like shell. If you are running on Windows, we recommend to install [MSys Git](linkto) wich bundles Git and a fully efficient bash shell. Then, by replacing *my_user* by your user name and *my_work_dir* by the name you want, type the following commands:

	# mkdir /home/my_user/my_work_dir
	# cd /home/my_user/my_work_dir
	# git clone (completeit) StaticProjector
	# cd StaticProjector
	# dev/tools/update-3rd-party.sh
	# dev/tools/package.sh

Now you have a ready Static Projector into the directory _/home/my_user/my_work_dir/StaticProjector/build/_. This is an important step because of bundled third party softwares needed for Static Projector to work. As of today, the only third party software is the [PHP port](linkto) of [markdown](linkto), which is a very useful piece of software.

#Step 2: Get Static Projector to run#

## Install Static Projector on the server ##

You have to upload the directory _sp-includes_ and the file _index.php_ into the web root of the website you want to create. If you want to, you can upload _sp-includes_ into a PHP include dir instead of the web root of the site. By doing this, you can use Static Projector for several websites on one server.

Create a folder into the web root called _data_. Then open you favorite browser and go on the URL which points to the site: you should see the message "Hello guys !" on a gray background. If you dont have a gray backgroun but a crappy white on, browse the HTML source and try to see the _styles.css_ file. If you cannot, please have a look a the [troubleshot page](linkto) to deal with this issue.

## Display you own "Hello world" ##

As you can see by browsing the files on the server, Static Projector created a lot of files. 


