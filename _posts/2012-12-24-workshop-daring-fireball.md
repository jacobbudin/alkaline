---
layout: default
title: "Workshop: Daring Fireball"
---

### Daring Fireball (John Gruber)

This workshop looks at [daringfireball.net](http://www.daringfireball.net)--a professional, single-author blog written by John Gruber.

<div class="note">
	<strong>New here?</strong><br />
	Read the <a href="/guide/workshop/notes/">"Workshop Notes"</a> for important information before beginning any workshop.
</div>

###### Markdown Those Quotes

You should use [Alkaline Labs' Markdown extension](http://www.alkalinelabs.com/#markdown) and apply "Markdown" to be your default markup language (**Settings > Configuration**)--even if only to transform straight quotation marks into educated ones (see below).

![Uneducated vs. educated](/guide/workshop/daring-fireball/educated.png)

###### Source Links are Savvy

Some articles are "linked" (based on an external site) and others are complete posts. You can use the **Source** field (under additional fields) on your Post pages to attribute a source to your post.

You can then use that data in determining whether users should go when clicking on a post title:

	<a href="{if:Post_Source}{Post_Source}{else:Post_Source}{Post_URI}{/if:Post_Source}">
		{Post_Title}
	</a>

Daring Fireball also has a special character allowing you to link to the commentary as opposed to the original article. These are Unicode glyphs, and if you'd like, you can find one to use <a href="http://theorem.ca/~mvcorks/code/charsets/auto.html">here</a>. You can then have this appear when you're linking to the original article and want a way to access the commentary on your site.

Here's our improved title link (we used a midline horizontal ellipses, &#8943;):

	<a href="{if:Post_Source}{Post_Source}{else:Post_Source}{Post_URI}{/if:Post_Source}">
		{Post_Title}
	</a>
	
	{if:Post_Source}
		<a href="{Post_URI}">&#8943;</a>
	{/if:Post_Source}

Daring Fireball uses the same technique in its newsfeed. Let's change the `atom.php` file which generates the newsfeed to do the same:
	
	// Find this:
	<link href="{Post_URI}" />
	
	/// Replace with this:
	<link href="{if:Post_Source}{Post_Source}{else:Post_Source}{Post_URI}{/if:Post_Source}" />


###### Date-based Headers

Daring Fireball only displays the date once for every grouping of articles beneath it that were posted on the same day. Alkaline can achieve the same effect with a quick addition to your page's code.

First, let's modify our post dates to look the same:

	$posts->formatTime('l, j F, Y');
	// This is based on the PHP date() function:
	// http://us3.php.net/manual/en/function.date.php

Halfway there. Now let's remove all the duplicate dates:

	// After you're done with $posts, but before you display them, add this:
	$date_stamp = '';
	for($i=0; $i < $posts->post_count; $i++){
		if($posts->posts[$i]['post_published_format'] == $date_stamp){
			$posts->posts[$i]['post_published_format'] = '';
		}
		else{
			$date_stamp = $posts->posts[$i]['post_published_format'];
		}
	}

This will loop through all of your posts and remove any `Post_Published_Format` tags that were the same as a previous one. So in a template such as this one:

	{block:Posts}
		{if:Post_Published_Format}
			<h1>{Post_Published_Format}</h1>
		{/if:Post_Published_Format}
		
		<h2>{Post_Title}</h2>
	{/block:Posts}

Alkaline will only publish the date if it exists, and will give you the grouping you desire.

###### Navigation and Page Links

The left-hand navigation includes links to various static pages with clean URLs. You can do the same by creating pages by choosing **Editor > Pages**.

Like on Daring Fireball, you can use your Web server's rewrite abilities to rewrite your URLs to make them more semantic and useful. But you do not need to have rewriting abilities to implement these types of pages and links, we can manually create them as well. Let's open a page (or create a new one) in Alkaline, and note the ID in your Web browser's address field.

Let's create a new `index.php` file for the URL `mydomain.com/projects/index.php`:

	<?php
	
	// Go up one directory and load the page with ID of 4
	require_once('./../page.php?id=4');
	
	?>
	
Even if you change the title of this page (say from "Projects" to "My Projects"), this page will continue to load because it's finding it based on its ID, not any other criteria.

Now we can add the link on our home page or to the `header.html` template or anywhere:

	<a href="/projects/" title="My projects including Project X and Y">Projects</a>


###### Permanent Links

Alkaline automatically generates permanent links with the Canvas tags: `{Image_URI}`, `{Post_URI}`, and so forth.

You can also use <a href="http://www.shauninman.com/archive/2009/08/17/less_n">Lessn</a> with <a href="http://www.alkalinelabs.com/#lessn">Alkaline Labs&#8217; Lessn extension</a> to generate shortened Web addresses for use on social networks and in emails.


###### Sharing Your Email Address (Minus the Spam)

You can use [Hivelogic Enkoder](http://hivelogic.com/enkoder/) to post your email address on your Web site and have it be invisible to most types of "crawlers" that search for email addresses to spam.