---
layout: default
title: "Workshop: Flickr"
---

### Flickr

This workshop looks at [Flickr](http://www.flickr.com)--a large photo-based social network by Yahoo!.

<div class="note">
	<strong>New here?</strong><br />
	Read the <a href="/guide/workshop/notes/">"Workshop Notes"</a> for important information before beginning any workshop.
</div>

###### Set-by-Set Navigation

Set-by-set navigation allows your Web site's visitors to quickly navigate complex image library, by traversing between sets to view specific groups of your images.

![Set list](/guide/workshop/flickr/set-list.jpg)

First, we need to figure out which sets the image is a member of:

	$images = new Image($image_ids);
	$images->getSets();
	
	$content = new Canvas;
	$content->load(...); // Your template
	$content->loop($images);
	$content->display();

We can then use the follow example template list all the sets:

	{block:Images}
		{Image_Title} is a member of the following sets:
		{block:Sets}
			{Set_Title}
		{/block:Sets}
	{block:Images}

Next, let's find where the image is located within the entire public library.

	$image_ids = new Find('images');
	$image_ids->published();
	$image_ids->page(null, 1);
	// $id is the ID value of the image on primary display
	$image_ids->with($id);
	$image_ids->privacy('public');
	$image_ids->sort('image_published', 'DESC');
	$image_ids->find();
	
	$max = count($image_ids->ids_before) - 2;
	
	// Let's supply the Image class with the image IDs of
	// the two before, the one on display, and the two after
	$buffer_images = new Image(array($image_ids->ids_before[$max],
		$image_ids->ids_before[++$max],
		$id,
		$image_ids->ids_after[0],
		$image_ids->ids_after[1]));
	$buffer_images->getSizes();
	
	$content = new Canvas;
	$content->load(...); // Your template
	$content->loop($buffer_images);
	$content->display();

We create a new template with another `{block:Images}` to display these:

	{block:Images}
		<img src="{Image_Src_Small}" alt="" />
	{/block:Images}

Lastly, we can repeat the above code for individual sets by simply adding Find's sets() method:
	
	$image_ids->sets($set_id);

For performance reasons, you should load these set-by-set navigation snippets on demand and not on page load, just as Flickr does.