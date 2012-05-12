The Static Projector HowTo step by step
========

#Requirements#

You need to know how to code in PHP *a little bit*, as you need to know HTML and CSS, at least the basics. Being familiar with some regular expressions basics is good too but not mandatory.

You need a full web stack with PHP5 for Static Projector to run. Most of web hosts uses Apache + PHP5 + MySQL. Static Projector is only written in PHP so the Apache web server is not mandatory. But please notice that the software has **not** been tested with another web server and that the sofwtare writes some _.htaccess_ files by default. Those are not mandatory but useful to protect some folders that web visitors do not need to see. Any feedback on another web server than Apache is welcome.

Note for Linux/Unix users: if you plan to install Static Projector on your local computer or on a dedicated server fully managed by yourself, please notive that you will have to deal with user and group permission between the Apache server runtime and your user, since Static Projector generates a lot of files for you to modify and to cache some data.

#Step 1: Getting StaticProjector#

##Method 1: from zip archive##

Download the zip file [here](https://github.com/downloads/duckie/StaticProjector/StaticProjector-0.1.zip) and uncompress it into a folder.

##Method 2: from git repository##

__Please be careful__ cause there some steps over just cloning the repository. We suppose you have git installed on your computer and a Unix like shell. If you are running on Windows, we recommend to install [MSys Git](http://msysgit.github.com/) wich bundles Git and a fully efficient bash shell. Then, by replacing *my_user* by your user name and *my_work_dir* by the name you want, type the following commands:

	# mkdir /home/my_user/my_work_dir
	# cd /home/my_user/my_work_dir
	# git clone git://github.com/duckie/StaticProjector.git StaticProjector
	# cd StaticProjector
	# dev/tools/update-3rd-party.sh
	# dev/tools/package.sh

Now you have a ready Static Projector into the directory */home/my_user/my_work_dir/StaticProjector/build/*. This is an important step because of bundled third party softwares needed for Static Projector to work. As of today, the only third party software is the [PHP port](https://github.com/michelf/php-markdown) of [markdown](http://daringfireball.net/projects/markdown/), which is a very useful piece of software.

#Step 2: Get Static Projector to run#

## Install Static Projector on the server ##

You have to upload the directory _sp-includes_ and the file _index.php_ into the web root of the website you want to create. If you want to, you can upload _sp-includes_ into a PHP include dir instead of the web root of the site. By doing this, you can use Static Projector for several websites on one server. You have to give the PHP runtime the right to write into the main folder. If you dont know if this is the case and how to change it, its likely to be already ok with that point.

Create a folder into the web root called _data_. Then open you favorite browser and go on the URL which points to the site: you should see the message "Hello guys !" on a gray background. If you dont have a gray background but a crappy white one, browse the HTML source and try to see the _styles.css_ file. If you cannot, please have a look a the [troubleshot page](https://github.com/duckie/StaticProjector/blob/master/dev/doc/troubleshot.md) to deal with this issue.

## Display you own "Hello world" ##

As you can see by browsing the files on the server, Static Projector created a lot of files. First of all, modify the file _web-data/routes.txt_ and put a content similar to this:

	/ -> default(home.md)

Then create the file _data/home.md_ and write what you want in it. Refresh the root web page: now your own text is displayed.

__How it works__ The meaning of the line you wrote is: redirect the "/" URL on the "default" template by processing the "home.md" resource. Then the "default" template detects the _*.md_ extension and processes the file accordingly. Notice that "default" is the name of the template and not a reserved word. This template is created by default to help you get the website faster.

#Step 3: The template system, controller and view in a single shot#

The first thing you have to do is tweaking the main page in order to put in it your files encoding, some meta tags and whatever other HTML things you want to. _Static Projector_ has a pure PHP template engine based on class hierarchy. It is both simple and powerful, and will let you run the things the way you want it without code duplication. The default template comes with a few common things to help you to start, but please notice you will be able to change everything later.

## Example: Customizing the header ##

Open the file *web-data/templates/default_template.php*. You see a PHP class with member functions that have the responsibility to display the web page. How the _$iData_ parameter is managed will be explained later. Now suppose we need to add a meta tag to add some info about text encoding. Change the _head_ function from this:

	public function head($iData)
	{
	?><title><?php echo(sp_config_value("title"));?></title>
	<link href="<?php echo(sp_resource_url("web-data/styles/style.css"))?>" rel="stylesheet" type="text/css" /> <?php
	}

To this

	public function head($iData)
	{
	?><title><?php echo(sp_config_value("title"));?></title>
	<meta arg="" arg="" />
	<link href="<?php echo(sp_resource_url("web-data/styles/style.css"))?>" rel="stylesheet" type="text/css" /> <?php
	}

And update the page. Now browse the HTML source: you should see your change added in the header. Play around with the file. If you mess with it, juste delete it: _Static Projector_ will re-create it. The only mandatory point is to inherit from _sp_base_template_ and to have a _main_ mehod.

## Creating your own template ##

Once you completed this part, you will be able to customize the default template as much as you want. _Static Projector_ is built over the Model/View/Controller principle. The model is the files in _data/_ and the files in _web-data_. A template s a combination of a controller and a view.

### The context ###

Suppose you have a bunch of _*.md_ files in a folder and you want the URL _http://www.yoursite.com/snippets/name_ to display the file _data/snips/name.md_ for any _name_ available in the folder. Create the _data/snips_ folder and put some crappy _*.md_ files in it.

### Define a route ###

A _route_ is what binds a given URL to a given controller and its corresponding view. The _web-data/routes.txt_ file contains all the routes of your website. Our first job here is to write a line to manage the snippets' URLs. Write it like that:

	/snippets/([^/]+) -> snippet(snips/\1.md)

The first part is the URL part:

	/snippets/([^/]+)

This part is a regular expression made to match the correct URLs. _Static Projector_ will parse the _web-data/routes.txt_ file and try to match the URL with each route in the order they appear in the file. At the first match, the corresponding template is executed. The __([^\]+)__ is important since it allows the regular expression engine to extract the name we are interested in. This is the PHP regular expression engine (this not a _Static Projector_ implementation) so if you are used with it, you can write complex regular expressions to match complex URLs. You can use as many parenthesis groups as you want since you can get them back in the controller.

The second part is the template name part:

	snippet

_Static Projector_ will use it to create the controller and the view.


The third part is the "replace" part:

	snips/\1.md

It is an argument for the route to be processed with [preg_replace](http://www.php.net/manual/en/function.preg-replace.php), which allows you to extract the name directly before the template is executed. This is optional so you could have written such a thing:

	/snippets/([^/]+) -> snippet()

And manage the "replace" part into the controller's code.

### Process the result ###

Know display your browser and go to _http://www.yoursite.com/snippets/snippet1.md_ (I suppose _data/snips/snippet1.md_ exists). You should see a *Hello guys* message or anything you put in the _content_ method of the _default_ template. No browse the files into _web-data/templates_. _Static Projector_ created two new files: *snippet_controller.php* and *snippet_template.php*. Open *snippet_controller.php* into your code editor. You will see a PHP class which inherits from *default_controller*. If you want to, you can inherit from another controller of yours. Do not forget to update the *sp_require_template("default");* if you do so. Change the content of the _execute_ method from:

	$datas = parent::execute($iData);
	return $datas;

To:

	$datas = $iData;
	return $datas;

Then open *snippet_template.php* and change the _content_ method from:

	parent::content($iData);

To:

	print_r($iData);

Then return on the web browser, update the page and show the HTML source to see the *print_r* result. This is a PHP tip to get the content of an array. You will something like that:

	Array
	(
	    [0] => snips/snippet1.md
	    [1] => snippet1
	)

You can see what you got in the controller. The first element is the matched string replaced by the "replace" part. The second element is the name extracted from the parenthesis group. If you have several groups, they will be added in it. If you let the "replace" part blank (try it), the first element is the matched string.

### Display the content ###

Each part have to execute its role. The controller will get the data from the model. The view will format the data into HTML. Change the _execute_ method of the controller from:

	$datas = $iData;
	return $datas;

To:

	$file = sp_get_resource_path($iData[0]);
	$md_text = "The file ".$iData[1]." has not been found.";
	if(file_exists($file))
	{
		$md_text = file_get_contents($file);
	}
	$datas = array();
	$datas['content'] = $md_text;
	return $datas;

Then change the _content_ method of the template from:

	print_r($iData);

To:

	?>
	<div><?php echo(sp_markdown($iData["content"])); ?></div>
	<?php

Then update the page. Your file should have been processed and displayed.

#Step 4: Query the model and add metadata to your data#

## A short example: title your page ##

When _Static Projector_ generates its cache files, it browses the file tree into *data* and creates another tree into *web-data/data* which is similar. In these auto-generated files, you can add some metadata to help the controllers browse and display correctly the other files. We will use it to make the previous example more like a dynamic page.

**Part to be continued**

#Step 5: Mastering the configuration file#

The configuration file is *web-data/config.txt*. It contains a set of *key* = *value* terms.

##_Static Projector_ reserved part##

Each key beginning by *sp.* is _Static Projector_ reserved. All of them are optional since _Static Projector_ gets the value from the default configuration file if you do not provide the value into yours. To get the default config file back, juste delete the config file and update any page in your browser. _Static Projector_ will create it again. A explanation of each key follows.

#### sp.regen_cache ####

Set this value to *Yes* or *No*. The default is *Yes*. When this variable is set, Static Projector will explore *data/* and *web-data/data/* to find changes in it. If you did not make any change, _Static Projector_ will not update anything, but it has to explore to find out. So when you have a stable version of your data and metadata, once the cache is generated, you dont need to generate it again. This is particularly true if you have a lot of files, because browsing them is a time and energy consumer. This is the case on [JMJPhoto](http://www.jmjphoto.fr) where the cache generation is turned off when there is no maintenance on the website.

I recommend to set it to "Yes" when you do some work into the data and the metadata, then set it back to "No" when you are finished.

#### sp.activate_log ####

Set this value to *Yes* or *No*. The default is *No*. When turned on, the *data/log.txt* file is generated at each page generation. The log file is completely *rewritten* each time. The logs do not stack. It may contain some useful information if you have some problems but most of the time, it is useless. I recommend to set it to *No* unless for debugging problems.

#### sp.notfound_route ####

This value contain the name of the route you _Static Projector_ is required for when you redirect a controller with the *redirect_to_notfound()* method. The default is *error404*.

#### sp.timezone ####

This is the [PHP Time Zone](http://php.net/manual/en/timezones.php) to be used. This is useful for generating a consistent cache regarding the timestamps and the date string you may want to display at some point. The default is *Europe/Berlin*.

#### sp.debug ####

Set this value to *Yes* or *No*. The default is *No*. This is to be used by developers. When set to *Yes*, some cache datas are written in a more readable format.

#### sp.override_chunks ####

This one is pretty special. It contains a list of names serarated by a *;*. These names of those of the template chunks you want _Static Projector_ to override by default when it creates a new template file. This setting is useful when you develop the website and you have several similar templates which inherits from each other. It is an helper which avoids to write too much PHP code. It is not blocking in any way.

##Free part##

The "free" keys are those beginning by *website.*. Those are free because you can add as much keys of this kind as you want. Each will be readable into the controllers and the templates. For example, the key *website.title* will be readable by calling *sp_config_value("title")*.
