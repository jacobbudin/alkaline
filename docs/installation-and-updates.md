### Installation and Updates

##### Installation

Experienced individuals, such as Web developers, should be able to install Alkaline in 5-10 minutes. Novices should allocate as much as an hour.

1. [Log in](/users/) to your Alkaline Lounge account.
	- If you don't have one, [create an account](/users/login/) using the same email address you used to purchase Alkaline.
2. Download Alkaline from the [licenses page](/users/licenses/) in the users lounge.
3. Unpack the .zip archive by double-clicking on it.
4. Use an FTP application to move the contents of the folder `/alkaline/` from your computer to your Web site.
	- Set the permissions on the folders: `/cache/`, `/db/`, `/images/`, and `/shoebox/` to 777 (read, write, and execute), also set the same permissions to the file `config.json`
	- Delete the `/update/` folder
5. Once your done uploading the files, using your Web browser, visit the `/install/` directory of your Web site where you installed Alkaline.

**Having issues?** Try our [troubleshooting](/guide/troubleshooting/) guide.

From here, Alkaline will ask you to supply information to complete the installation. Afterwards, you may want:

- **To enable vector support,** choose Dashboard > Configuration and enable ImageMagick.
- **To enable smart URLs,** [read our quick how-to guide](/guide/howto/enable-url-rewriting/).
- **To load the internal geo database,** choose Dashboard > Maintenance and click "Rebuild geographic library".

You should delete the `/install/` folder once you're happy with your new Alkaline installation.

###### Choosing the database type

Alkaline supports [MySQL](http://www.mysql.com/), [PostgreSQL](http://www.postgresql.org/), and [SQLite](http://www.sqlite.org/), but you should only use the database types that were indicated satisfactory in the compatibility suite. (For instance, having access to a PostgreSQL database does not mean you have the PDO driver necessary for Alkaline to utilize it.) When it's available, we recommend choosing MySQL.

###### A quick note on security

Your Web server may allow for more restrictive file and folder permissions than those indicated above. Alkaline only checks for (and warns of) incorrect permissions during installation. If you so desire and your Web server allows, you may make these permissions more restrictive once installed.

If you're using SQLite, you should ensure wherever you located your database file (`alkaline.db`) that it cannot be accessed or downloaded from the outside world. You should move this file to at least one level below the public HTTP directory.

##### Updates

The method of installing updates varies from update to update. Most of the time, it simply requires replacing files. Be careful not to overwrite your installation's themes and extensions, or your `config.php` or `config.json` files unless specifically directed to do so. Refer to the update's documentation for instructions.