---
layout: default
title: How to Increase Your Upload File Size Limit
---

### Increase Your Upload File Size Limit

As of Alkaline v1.1.2, the **Upload** page of your Alkaline Dashboard will display your Web server's file size upload limit. Your PHP configuration is the likely culprit.

<div class="note">
	<strong>Bypass the limit</strong><br />
	These limits do not apply to files upload via FTP, SFTP, WebDAV, or similar methods. You can save yourself the trouble of following this guide by choosing one of these upload methods.
</div>

<div class="note">
	<strong>No root access?</strong><br />
	If you don&#8217;t have root access to your server (for example, you&#8217;re on shared Web hosting), you should contact your Web hosting provider for assistance. You will not be able to make the changes on your own.
</div>

###### 1. Determine your desired file size limit.

You should set your limit high enough to accommodate the largest images you hope to upload into Alkaline, with some additional space to act as a buffer for an occasional, larger file.

Do not set your limit unnecessarily high. You may encourage or facilitate abuse of your server by malicious individuals.

###### 2. Edit your PHP configuration.

Your PHP configuration has three values that will need to match or exceed your desired file size limit:

1. `upload_max_filesize` ([info](http://www.php.net/manual/en/ini.core.php#ini.upload-max-filesize)) -- Sets the maximum size of an uploaded file.
2. `post_max_size` ([info](http://www.php.net/manual/en/ini.core.php#ini.post-max-size)) -- Sets maximum size of post data allowed, including file uploads.
3. `memory_limit` ([info](http://www.php.net/manual/en/ini.core.php#ini.memory-limit)) -- Sets the maximum amount of memory in bytes that a script is allowed to allocate.

You may also need to increase `max_input_time` if your Internet connection is very slow or your files are very large, and the time it takes to upload any one file exceeds this value (in seconds).

Take note that these values are with a single proceeding uppercase letter (for example, a limit of 10 MB would have a configuration value of "10M"). Only change those values which are lower than your desired limit; reducing these values may cause undesirable effects.

###### 3. Reload PHP.

In most Web environments, reloading (or restarting) your Web server will cause the new configuration to take effect. If you&#8217;re using a PHP daemon such as PHP-FPM, reload (or restart) that daemon instead.

<p class="note">
	<strong>Still not working?</strong><br />
	Some HTTP servers including Nginx have additional configuration values that may need to be upwardly adjusted. Review the documentation that came with your HTTP server.
</p>
