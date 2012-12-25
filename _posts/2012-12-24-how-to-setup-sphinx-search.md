---
layout: default
title: How to Setup Sphinx Search with Alkaline
---

### Setup Sphinx Search with Alkaline

Alkaline includes built-in support for the [Sphinx search engine server](http://sphinxsearch.com), which runs on your own server to process ultrafast, relevancy-based searches. Sphinx is superior to traditional SQL-based searches, because it can sort results by relevancy, search using related terms, and rapidly comb large datasets.

**Note: This article is technically complex and presumes you have knowledge of the command line.** It is intended for system administrators and similarly technically-savvy individuals, and is not for the faint of heart. Please read this entire document before deciding whether Sphinx is right for you or your organization.

To get Sphinx and Alkaline running in tandem, you will need a VPS or dedicated server and root (SSH) access.

###### 1. Download and install Sphinx.

Sphinx is free and open source. You can download it one of two ways. First, check your operating system's package management utility, such as `apt-get` or `yum`, for a readymade copy of Sphinx.

Otherwise, if Sphinx is unavailable, download the latest stable version of [Sphinx](http://sphinxsearch.com/downloads/). Alkaline has been tested with Sphinx 0.9.9 and Sphinx 2.0.1, and works well with either. Follow the instructions that came with your copy of Sphinx to install it.

Next, download and install the [PHP PECL Sphinx package](http://pecl.php.net/package/sphinx). If you already have [PHP PEAR](http://pear.php.net/) installed, which many do, you should be able to install the PHP PECL Sphinx package by running this command: `pecl install sphinx`. If that is successful, continue. Otherwise, [install PHP PEAR](http://pear.php.net/manual/en/installation.introduction.php) and then issue the command above.

You can ensure Sphinx has been installed by choosing **Settings** in Alkaline and looking under the "Environment" header.

###### 2. Configure Sphinx.

Next, we need to tell Sphinx where to access and how to process the data in Alkaline. Locate Sphinx's configuration file (usually `/etc/sphinx/sphinx.conf`) and open it. Here's an example configuration:

	source alkaline
	{
		type				= xmlpipe2
		xmlpipe_command		= php /usr/local/html/admin/sphinx-xmlpipe2.php
	}

	index my_site
	{
		source			= alkaline
		path			= /var/lib/sphinx/my_site
		docinfo			= extern
		charset_type	= utf-8
		morphology 		= stem_en
	}

Let's take a look at this file. There are two components: an index and a source. The source is where your data is coming from. The index is what will be searched (and derives its data from one or more sources).

Our first (and only) source will be Alkaline:

1. `type = xmlpipe2`
	- Alkaline sends its contents to Sphinx in an XML-based format
2. `xmlpipe_command = php /usr/local/html/admin/sphinx-xmlpipe2.php`
	- To locate Alkaline's contents, run the PHP file located at `/your_web_root/admin/sphinx-xmlpipe2.php`
	- Be sure to change the root to the directory where Alkaline is installed

Our index will contain our one source:

1. `source = alkaline`
	- Include the source named `alkaline`
2. `path = /var/lib/sphinx/my_site`
	- Store (and retrieve) the index from this location on my server
	- Make sure Sphinx has sufficient privileges to read and write from this location
3. `docinfo = extern`
	- *(Optional)* Affects RAM usage, `extern` is the default
4. `charset_type = utf-8`
	- *(Optional)* Tell Sphinx to use Unicode character encoding
5. `morphology = stem_en`
	- *(Optional)* Tell Sphinx to normalize words (e.g., when searching "dog", results would include "dogs" and vice-versa)
	- Replace or remove this variable your content is not in English

Save this file.

###### 3. Start Sphinx.

Like a Web server such as Apache or a database server such as MySQL, Sphinx is a *search* server. Its daemon must be running continuously to accept and process searches. Start Sphinx now. Also, make sure Sphinx is loaded when your Web server is restarted.

###### 4. Prepare Alkaline.

Go to **Dashboard > Settings > Maintenance > "Build items table"** and wait for the task to complete. You have just created a new documents table that Sphinx and Alkaline can reference to locate the results.

###### 5. Test Sphinx

From the command line, you should now be able to search Alkaline. First, tell Sphinx to create its first index: `indexer --all --rotate;`. Then, try a word you know you've used on your Alkaline-powered Web site, for example `search "New York"`. Sphinx should list one or more results.

###### 6. Set up a Cron job.

In most cases, including this one, Sphinx does not index content in real-time. You must reindex your content at a regular interval to keep the search results up to date. Luckily, this is very easy to do. Cron is a way to tell your server to perform an action at regular intervals. A job is a task to be executed at one of these intervals. Here's a sample shell script named `build-sphinx.sh` that we will use in our Cron job:

	php -f /usr/local/nginx/html/admin/tasks/build-items-job.php;
	indexer --all --rotate;

*Note: A shell script is just a list of commands that are the same as if you typed them into the shell yourself.*

Save this file on your Web server, anywhere that is not publicly accessible. The first line tells Alkaline to update its documents table. The second line tells Sphinx that new content should be added to its index.

You can choose to run this script as often as you wish. We recommend once every 20 minutes. Here's our Cron entry:

	05,25,45 * * * * /path_to_your_script_from_above/build-sphinx.sh >/dev/null 2>&1

You can use the command `crontab -e` to edit your Cron configuration and add the line above. This line tells Cron to execute the script on the 5th, 25th, and 45th minute of every hour, every day, etc. It also says where to find the script, and the last two parts simply tell Cron to ignore any output that may be generated.

###### 7. Tell Alkaline to use Sphinx.

Now that you've got Sphinx up and running, you need to notify Alkaline that Sphinx is installed, configured, and ready for use. Go to **Dashboard > Settings > Configuration**. Scroll down to the "Sphinx" header and click "Use Sphinx to process search queries". Save your configuration.

###### 8. Make sure it works.

Ensure that Alkaline is delivering the search results you were expecting it to. You may want to turn it on and off and compare the results from traditional results to Sphinx results. If the results are not what you were expecting, you can disable Sphinx in the Alkaline configuration pane while you fine-tune Sphinx for your Web site's content.

###### 9. (Optional) Taking it the next level. 

As an enterprise-grade search engine, Sphinx has many options and should be configured for the best performance and results. Read the documentation that correlates to your version of Sphinx for more information.

Additionally, many users will want to show and highlight excerpts from their results. Here's how in `results.php`:

	// This code is being placed after we've retrieved our posts, like so:
	// $posts = new Post($post_ids);
	
	// Let's store our what will be our new excerpts in a new array that's derived from the post's text
	$docs = array();
	
	// For each post, we'll convert the HTML entities to ensure they're processed correctly
	for ($i=0; $i < $posts->post_count; $i++) { 
		$docs[] = html_entity_decode(strip_tags($posts->posts[$i]['post_text']), ENT_QUOTES, 'UTF-8');
	}
	
	// We need to load a new SphinxClient class, even though we won't be doing an searching
	// Here's the documentation for the class:
	// http://us.php.net/manual/en/class.sphinxclient.php
	$sphinx = new SphinxClient;
	
	// Tell Sphinx to use the new array we created to generate excerpts
	// 'my_site' is the name of our Sphinx index
	// $_REQUEST['q'] is the search term our user was seeking
	// The fourth parameter is an associate array with various options
	$docs = $sphinx->buildExcerpts($docs,
		'my_site',
		strip_tags($_REQUEST['q']),
		array('before_match' => '[[[', 'after_match' => ']]]', 'around' => 20));
	
	// Let us format all the data and save it to the object, which we can then use in our Canvas templates
	for ($i=0; $i < $posts->post_count; $i++) { 
		// Convert back the HTML entities
		$posts->posts[$i]['post_text'] = htmlentities($docs[$i], ENT_QUOTES, 'UTF-8');
		
		// Let's wrap the words in the excerpts that match our search term with <span> tags
		$posts->posts[$i]['post_text'] = str_replace(array('[[[', ']]]'),
			array('<span class="highlight">', '</span>'),
			$posts->posts[$i]['post_text']);
	}