---
layout: default
title: How to Reset Your Admin Password
---

### How to Reset Your Admin Password

Since Alkaline is a self-hosted content management system, your admin password resides on the server your Web site is hosted on and no one at Budin Ltd. can retrieve or replace your password. If you cannot remember or locate your password, and you have no other active users which can reset your password on your behalf, follow the instructions below.

###### 1. Access your database

If you're using a MySQL or PostgreSQL database, log into your Web host's control panel and look for your database administration tool (usually, PHPMyAdmin or PHPPgAdmin, respectively). If you're using a SQLite database, download the database from your `/db/` folder to your local machine to edit it.

###### 2. Use a database administration tool.

There are many different Web and desktop applications for accessing your database both remotely and on your local machine. You should be able to use virtually any of these applications to open your database. Next, locate the tab or window that allows you to execute SQL against the database.

###### 3. Run the SQL command.

For most Alkaline installations, run the follow command at the SQL prompt or in the SQL field:

	UPDATE users SET user_pass = "91ef0c1608b20c9c5bd9e003bbb600229c0dfeb1" WHERE user_id = 1;


<div class="note">
	<strong>Tip</strong>
	<p>If you&#8217;re using a table prefix, you&#8217;ll need to alter the SQL table <code>users</code> above to take into account the table prefix. If you&#8217;re salting your passwords, you&#8217;ll need to create your own SHA-1 hash with the salt and replace the hash used in the SQL command.</p>
</div>

You may now close the window and log-in to your Alkaline installation with the password: `reset`. Immediately log-in to your Dashboard and change your password in the **Settings > Users** to a unique password.