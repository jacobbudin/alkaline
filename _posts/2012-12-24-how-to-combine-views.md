---
layout: default
title: How to Combine Image and Post Views
---

### Combine Image and Post Views

If you want to combine images and posts in a streamlined view, such as "My 10 Most Recent Updates" with links to the items, you can combine the views in the presentation layer.

###### 1. Retrieve images and posts.

First, let's grab the last ten updates of both images and posts.

	$image_ids = new Find('images');
	$image_ids->page(1, 10);
	$image_ids->published();
	$image_ids->privacy('public');
	$image_ids->sort('image_published', 'DESC');
	$image_ids->find();

	$images = new Image($image_ids);
	// Optionally perform methods here such as formatTime()

	$post_ids = new Find('posts');
	$post_ids->page(1, 10);
	$post_ids->published();
	$post_ids->sort('post_published', 'DESC');
	$post_ids->find();

	$posts = new Post($post_ids);
	// Optionally perform methods here such as formatTime()

###### 2. Design a combined view block.

Here's a simple Canvas template (`recent_updates.html`) with our two kinds:

	{block:Images}
		<a href="{Image_URI}">{Image_Title}</a>
	{/block:Images}

	{block:Posts}
		<a href="{Post_URI}">{Post_Title}</a>
	{/block:Posts}
	
###### 3. Display the results.

Display the Canvas block in the order of data published. Here's a working PHP snippet for this example:

	$count = 10; // Show 10 items total
	$i = 0;
	$p = 0;
	
	$index_original = new Canvas;
	$index_original->load('recent_updates'); // recent_updates.html in our theme directory is our Canvas combined view block
	
	// We'll initiate a loop that will count
	for($n=0; $n < $count; $n++){
		$index = clone $index_original;
		if(strtotime($images->images[$i]['image_published']) > strtotime($posts->posts[$p]['post_published'])){
			$index->loop($images, $i, 1);
			$index->display();
			$i++;
		}
		else{
			$index->loop($posts, $p, 1);
			$index->display();
			$p++;
		}
	}
