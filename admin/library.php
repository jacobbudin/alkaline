<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$alkaline->setCallback();

// Preference: page_limit
if(!$max = $user->returnPref('page_limit')){
	$max = 100;
}

$image_ids = new Find('images');
$image_ids->clearMemory();
$image_ids->page(null, $max);
$image_ids->find();

$images = new Image($image_ids);
$images->getSizes('square');
$images->hook();

$shoebox_count = $alkaline->countDirectory(PATH . SHOEBOX);
if($shoebox_count > 0){
	$shoebox_count = '(' . $shoebox_count . ') ';
}
else{
	$shoebox_count = '';
}

define('TAB', 'library');
define('TITLE', 'Alkaline Library');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="span-24 last">
	<div class="actions">
		<a href="<?php echo BASE . ADMIN . 'upload' . URL_CAP; ?>">
			<button>Upload image</button>
		</a>
		<?php if($badges['images'] > 0){ ?>
		<a href="<?php echo BASE . ADMIN . 'shoebox' . URL_CAP; ?>">
			<button>Process images (<?php echo $badges['images']; ?>)</button>
		</a>
		<?php } ?>
	</div>
	
	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/images.png" alt="" /> Images (<?php echo number_format($image_ids->count); ?>)</h1>

	<form action="<?php echo BASE . ADMIN; ?>search<?php echo URL_CAP; ?>" method="post">
		<p style="margin-bottom: 0;">
			<input type="search" name="q" style="width: 30em; margin-left: 0;" results="10" /> <input type="submit" value="Search" />
		</p>
		
		<p>
			<span class="switch">&#9656;</span> <a href="#" class="show advanced" style="line-height: 2.5em;">Show options and presets</a>
		</p>
	
		<div class="reveal span-24 last">
			<div class="span-15 append-1">
				<table>
					<tr>
						<td class="right pad"><label for="tags">Tags:</label></td>
						<td class="quiet">
							<input type="text" id="tags" name="tags" class="l" /><br />
							<em>Tip: Use the uppercase boolean operators AND, OR, and NOT.</em>
						</td>
					</tr>
					<tr>
						<td class="right pad"><label for="tags">EXIF metadata:</label></td>
						<td>
							<?php echo $alkaline->showEXIFNames('exif_name'); ?>
							<input type="text" id="exif_value" name="exif_value" class="s" /><br />
						</td>
					</tr>
					<tr>
						<td class="right middle"><label for="rights">Rights set:</label></td>
						<td class="quiet">
							<?php echo $alkaline->showRights('rights'); ?>
						</td>
					</tr>
					<tr>
						<td class="right middle"><label>Date taken:</label></td>
						<td class="quiet">
							between <input type="text" class="date s" name="taken_begin" />
							and <input type="text" class="date s" name="taken_end" />
						</td>
					</tr>
					<tr>
						<td class="right middle"><label>Date uploaded:</label></td>
						<td class="quiet">
							between <input type="text" class="date s" name="uploaded_begin" />
							and <input type="text" class="date s" name="uploaded_end" />
						</td>
					</tr>
					<tr>
						<td class="right middle"><label>Location:</label></td>
						<td class="quiet">
							within
							<select name="location_proximity">
								<option value="10">10</option>
								<option value="25">25</option>
								<option value="50">50</option>
								<option value="100" selected="selected">100</option>
								<option value="250">250</option>
								<option value="500">500</option>
								<option value="1000">1,000</option>
								<option value="2500">2,500</option>
							</select>
							miles of 
							<input type="text" name="location" class="image_geo m" />
						</td>
					</tr>
					<tr>
						<td class="right middle"><label for="color">Dominant color:</label></td>
						<td>
							<select id="color" name="color">
								<option></option>
								<option value="blue">Blue</option>
								<option value="red">Red</option>
								<option value="yellow">Yellow</option>
								<option value="green">Green</option>
								<option value="purple">Purple</option>
								<option value="orange">Orange</option>
								<option value="brown">Brown</option>
								<option value="pink">Pink</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="right middle"><label>Views:</label></td>
						<td>
							<select name="views_operator">
								<option value="greater">&#8805;</option>
								<option value="less">&#8804;</option>
								<option value="equal">&#0061;</option>
							</select>
							<input type="text" name="views" class="xs" />
						</td>
					</tr>
					<tr>
						<td class="right middle"><label for="orientation">Orientation:</label></td>
						<td class="quiet">
							<select id="orientation" name="orientation">
								<option value="">All</option>
								<option value="portrait">Portrait</option>
								<option value="landscape">Landscape</option>
								<option value="square">Square</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="right middle"><label for="privacy">Privacy level:</label></td>
						<td class="quiet">
							<select id="privacy" name="privacy">
								<option value="">All</option>
								<option value="public">Public</option>
								<option value="protected">Protected</option>
								<option value="private">Private</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="right middle"><label for="published">Publication status:</label></td>
						<td class="quiet">
							<select id="published" name="published">
								<option value="">All</option>
								<option value="published">Published</option>
								<option value="unpublished">Unpublished</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="right middle"><label>Sort results by:</label></td>
						<td>
							<select name="sort">
								<option value="taken">Date taken</option>
								<option value="updated">Date last updated</option>
								<option value="published">Date published</option>
								<option value="uploaded" selected="selected">Date uploaded</option>
								<option value="title">Title</option>
								<option value="views">Views</option>
							</select>
							<select name="sort_direction">
								<option value="DESC">Descending</option>
								<option value="ASC">Ascending</option>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<div class="span-8 last">
				<h3>Presets</h3>
				
				<ul>
					<li><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'displayed' . URL_RW; ?>">Displayed images</a></li>
					<li><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'updated' . URL_RW; ?>">Recently updated images</a></li>
					<li><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'views' . URL_RW; ?>">Most viewed images</a></li>
					<li><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'nonpublic' . URL_RW; ?>">Nonpublic images</a></li>
				</ul>
				<ul>
					<li><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'unpublished' . URL_RW; ?>">Unpublished images</a></li>
					<li><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'untitled' . URL_RW; ?>">Untitled images</a></li>
					<li><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'untagged' . URL_RW; ?>">Untagged images</a></li>
				</ul>
			</div>
		</div>
	</form>

	<p>
		<?php

		foreach($images->images as $image){
			?>
			<a href="<?php echo BASE . ADMIN . 'image' . URL_ID . $image['image_id'] . URL_RW; ?>" class="nu">
				<img src="<?php echo $image['image_src_square']; ?>" alt="" title="<?php echo $image['image_title']; ?>" class="frame tip" />
			</a>
			<?php
		}
		?>
	</p>
	<?php
	if($image_ids->page_count > 1){
		?>
		<p>
			<?php
			if(!empty($image_ids->page_previous)){
				for($i = 1; $i <= $image_ids->page_previous; ++$i){
					$page_uri = 'page_' . $i . '_uri';
					echo '<a href="' . $image_ids->$page_uri  .'" class="page_no">' . number_format($i) . '</a>';
				}
			}
			?>
			<span class="page_no">Page <?php echo $image_ids->page; ?> of <?php echo $image_ids->page_count; ?></span>
			<?php
			if(!empty($image_ids->page_next)){
				for($i = $image_ids->page_next; $i <= $image_ids->page_count; ++$i){
					$page_uri = 'page_' . $i . '_uri';
					echo '<a href="' . $image_ids->$page_uri  .'" class="page_no">' . number_format($i) . '</a>';
				}
			}
			?>
		</p>
		<?php
	}
	?>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>