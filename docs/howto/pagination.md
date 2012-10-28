### Change Pagination of Images and Posts

Alkaline lets you paginate images and posts however you choose, regardless of the theme you're using. Let's take a look at the `page()` method of the `Find` classes, the method works the same for both images and posts.

Let's open `index.php`, you should see a line like this:

	$image_ids->page(null, 12, 1);

This tells the `Find` object to automatically determine the page (`null`), display 12 images per page (`12`), and to display 1 image on the first page (`1`). You can change these values and save/upload the file to observe the changes to your Web site. Here are a few examples:

##### Images

Auto-determine the page number with 25 images on every page:

	$image_ids->page(null, 25);
	
Show page one with all the images (here, `0` is infinity):

	$image_ids->page(1, 0);

##### Posts

Auto-determine the page number, show 25 posts on the first page, and 10 on each subsequent page:

	$post_ids->page(null, 25);
	
Force to show the second page (maybe as a widget of "recent posts on the next page" on your home page) with 10 posts:

	$post_ids->page(2, 10);