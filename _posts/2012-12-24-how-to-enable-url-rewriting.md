---
layout: default
title: How to Enable URL Rewriting
---

### Enable URL Rewriting

URL rewriting goes by many names: "semantic URLs", "fancy URLs", "SEO-friendly URLs", but they all refer to the same result. When possible, you should always enable URL rewriting; it denotes professionalism, increases search engine accessibility, and is easier on the eyes.

	With rewriting disabled:  
	http://www.yourdomain.com/image.php?id=47-my-party
	
	With rewriting enabled:
	http://www.yourdomain.com/image/47-my-party/

###### 1. Determine your server configuration.

Go to your Alkaline Dashboard. Choose **Settings.** Look beneath the "Environment" header, and note your HTTP server. Depending on your HTTP server, proceed to the appropriate step 2.

###### 2a. Apache or LiteSpeed

On your Web server, rename the file `htaccess.txt` to `.htaccess`. Continue to step 3.

###### 2b. Microsoft IIS

Continue to step 3.

###### 2c. Nginx

See this article at the [Nginx Wiki](http://wiki.nginx.org/Alkaline) to modify your Nginx configuration. Continue to step 3.

###### 2d. Other

Determine whether your Web server abides by Apache-style rewriting (known as mod_rewrite via `.htaccess`) or Windows-style rewriting (URL Module 2 via `Web.config`). Some servers are compatible with neither, and need to be dealt with on an individual basis.

###### 3. Enable URL rewriting

Open `config.php` from Alkaline and:

	// Replace this:
	$url_rewrite = false;
	
	// With this:
	$url_rewrite = true;

Save the file to Alkaline.
