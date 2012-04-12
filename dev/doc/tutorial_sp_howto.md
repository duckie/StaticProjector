The Static Projector HowTo step by step
========

#Requirements#

You need to know how to code in PHP *a little bit*, as you need to know HTML and CSS, at least the basics.

You need a full web stack with PHP5 for Static Projector to run. Most of web hosts uses Apache + PHP5 + MySQL. Static Projector is only written in PHP so the Apache web server is not mandatory. But please notice that the software has **not** been tested with another web server and that the sofwtare writes some _.htaccess_ files by default. Those are not mandatory but useful to protect some folders that web visitors do not need to see. Any feedback on another web server than Apache is welcome.

Note for Linux/Unix users: if you plan to install Static Projector on your local computer or on a dedicated server fully managed by yourself, please notive that you will have to deal with user and group permission between the Apache server runtime and your user, since Static Projector generates a lot of files for you to modify and to cache some data.

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

You have to upload the directory _sp-includes_ and the file _index.php_ into the web root of the website you want to create. If you want to, you can upload _sp-includes_ into a PHP include dir instead of the web root of the site. By doing this, you can use Static Projector for several websites on one server. You have to give the PHP runtime the right to write into the main folder. If you dont know if this is the case and how to change it, its likely to be already ok with that point.

Create a folder into the web root called _data_. Then open you favorite browser and go on the URL which points to the site: you should see the message "Hello guys !" on a gray background. If you dont have a gray background but a crappy white one, browse the HTML source and try to see the _styles.css_ file. If you cannot, please have a look a the [troubleshot page](linkto) to deal with this issue.

## Display you own "Hello world" ##

As you can see by browsing the files on the server, Static Projector created a lot of files. First of all, modify the file _web-data/routes.txt_ and put a content similar to this:

	/ -> default(home.md)

Then create the file _data/home.md_ and write what you want in it. Refresh the root web page: now your own text is displayed.

__How it works__ The meaning of the line you wrote is: redirect the "/" URL on the "default" template by processing the "home.md" resource. Then the "default" template detects the _*.md_ extension and processes the file accordingly. Notice that "default" is the name of the template and not a reserved word. This template is created by default to help you get the website faster.

#Step 3: Intro to the template system#

The first thing you have to do is tweaking the main page in order to put in it your files encoding, some meta tags and whatever other HTML things you want to. _Static Projector_ has a pure PHP template engine based on class hierarchy. It is both simple and powerful, and will let you run the things the way you want it without code duplication. The default template comes with a few common things to help you to start, but please notice you will be able to change everything later.

## Example: Customizing the header ##

Open the file *web-data/templates/default_template.php*. You see a PHP class with member functions that have the responsibility to display the web page. How the _$iData_ parameter is managed will be explained later. Do not hesitate to have a look to *sp-includes/templates/base_template.php* to see how it is made. Now suppose we need to add a meta tag to add some info about text encoding. Change the _head_ function from this:

	public function head($iData) {
		parent::head($iData);
	}

To this

	public function head($iData) {
		parent::head($iData);
	?>
	<meta arg="" arg="" />
	<?php 
	}

And update the page. Know browse the HTML source: you should see your change added in the header.




