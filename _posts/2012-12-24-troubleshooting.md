---
layout: default
title: Troubleshooting
---

### Troubleshooting

###### I can't find the installation page, or I receive a "No database connection error."

- You must install Alkaline before you can use it. The installation page is located in the `install/` directory. Read [our installation instructions](/guide/installation-and-updates/).
- Make sure you've replaced your `config.php` when instructed to do so.

###### I can't load the installation page.

- Make sure your Web site is running PHP 5. Some Web hosts run PHP 4 by default, but almost all let you switch to PHP 5 through their control panels.
- Make sure your Web host is compatible with Alkaline by viewing your Compatibility Suite file (`cs.php`).
- *(Windows only)* Delete the `Web.config` file. It may cause 500-code errors where URL Rewrite 2 is not installed or your control panel is overriding certain settings.
- *(Windows only)* Make sure your Windows user account has full permissions to read and write the folders: `cache/`, `db/`, `images/`, `shoebox/`, and the file: `config.json`; you can do this through your Web host's control panel.

###### I receive an error "Cannot use object of type Alkaline as array".

- You have a non-standard PHP configuration. You need to turn off `register_globals` in your PHP configuration (PHP.ini). You can do this multiple ways. If you&#8217;re using the Apache HTTP Web server (as most do), you can add a `.htaccess` file with the following data to your base directory:

	<pre><code><IfModule mod_php5.c>
		php_flag register_globals Off
	</IfModule></code></pre>

###### I can't modify the permissions on folders or files.

- Some servers, such as those running suPHP, only need permissions set to `644` for files and `755` for folders. If you're unsure, contact your Web hosting provider.
- *(Windows only)* You cannot change permissions using your FTP client, you must use your Web hosting provider's control panel to edit permissions

###### I can load the installation page, but can't get to the next step.

- Double-check your database credentials (name, username, and password) are correct.
- Some Web hosts have separate database servers. In this case, your Web host should have supplied you with a separate domain to access the database (for example, `mysql.yourwebhost.com`). Enter this domain in the "Database host" field on the installation page.
	- Also, some Web hosts also may not run their database server that allow access via standard ports. If your Web host supplied you with a port number (for example, `9000`), enter it in the "Database port" field on the installation page.

###### I get alert dialog boxes from my Web browser that stop the page from loading, or some features aren't working.

- Update your Web browser. We strongly recommend [Apple Safari](http://www.apple.com/safari/), [Google Chrome](http://www.google.com/chrome/), or [Mozilla Firefox](http://www.mozilla.com/firefox/) for use with the Alkaline Dashboard, all support drag-and-drop uploading. If you're using another Web browser, such as [Microsoft Internet Explorer](http://www.microsoft.com/ie/), make sure it's up-to-date.

###### I restored Alkaline from a backup, and now it doesn't work.

- Your session may be corrupt. Quit your Web browser, and reopen it. 
- Double-check your `config.php` file to ensure it contains your database credentials.
- Double-check the appropriate directories have read and write permissions. (See ["Installation and Updates."](/guide/installation-and-updates/))