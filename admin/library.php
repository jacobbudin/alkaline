<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$alkaline->setCallback();

if(!empty($_GET['id'])){
	$_GET['page'] = $_GET['id'];
}

// Preference: page_limit
if(!$max = $user->returnPref('page_limit')){
	$max = 100;
}

$photo_ids = new Find();
$photo_ids->clearMemory();
$photo_ids->page(null, $max);
$photo_ids->find();

$photos = new Photo($photo_ids->photo_ids);
$photos->getImgUrl('square');

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
	<div class="span-5 colborderr">
		<h2><a href="<?php echo BASE . ADMIN; ?>upload<?php echo URL_CAP; ?>"><img src="/images/icons/upload.png" alt="" /> Upload &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>shoebox<?php echo URL_CAP; ?>"><img src="/images/icons/shoebox.png" alt="" /> Shoebox <?php echo $shoebox_count; ?>&#9656;</a></h2>
		<hr />

		<h3>Find</h3>
		
		<ul>
			<li><a href="<?php echo BASE . ADMIN; ?>search<?php echo URL_ACT; ?>displayed<?php echo URL_RW; ?>">Displayed photos</a></li>
			<li><a href="<?php echo BASE . ADMIN; ?>search<?php echo URL_ACT; ?>updated<?php echo URL_RW; ?>">Recently updated photos</a></li>
			<li><a href="<?php echo BASE . ADMIN; ?>search<?php echo URL_ACT; ?>views<?php echo URL_RW; ?>">Most viewed photos</a></li>
			<li><a href="<?php echo BASE . ADMIN; ?>search<?php echo URL_ACT; ?>nonpublic<?php echo URL_RW; ?>">Nonpublic photos</a></li>
		</ul>
		<ul>
			<li><a href="<?php echo BASE . ADMIN; ?>search<?php echo URL_ACT; ?>unpublished<?php echo URL_RW; ?>">Unpublished photos</a></li>
			<li><a href="<?php echo BASE . ADMIN; ?>search<?php echo URL_ACT; ?>untitled<?php echo URL_RW; ?>">Untitled photos</a></li>
			<li><a href="<?php echo BASE . ADMIN; ?>search<?php echo URL_ACT; ?>untagged<?php echo URL_RW; ?>">Untagged photos</a></li>
		</ul>
	</div>
	<div class="span-18 colborderl last">
		<h1>Search</h1>
		<form action="<?php echo BASE . ADMIN; ?>search<?php echo URL_CAP; ?>" method="post">
			<p style="margin-bottom: 0;">
				<input type="search" name="search" style="width: 30em; margin-left: 0;" results="10" /> <input type="submit" value="Search" />
			</p>

			<p>
				<span class="switch">&#9656;</span> <a href="#" class="show" style="line-height: 2.5em;">Show options</a>
			</p>
			
			<div class="reveal">
				<table>
					<tr>
						<td class="right pad"><label for="tags">Tags:</label></td>
						<td class="quiet">
							<input type="text" id="tags" name="tags" style="width: 30em;" /><br />
							<em>Tip: Use the uppercase boolean operators AND, OR, and NOT.</em>
						</td>
					</tr>
					<tr>
						<td class="right pad"><label for="tags">EXIF metadata:</label></td>
						<td>
							<input type="text" id="exifs" name="exifs" style="width: 30em;" /><br />
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
							between <input type="text" class="date" name="taken_begin" style="width: 10em;" />
							and <input type="text" class="date" name="taken_end" style="width: 10em;" />
						</td>
					</tr>
					<tr>
						<td class="right middle"><label>Date uploaded:</label></td>
						<td class="quiet">
							between <input type="text" class="date" name="uploaded_begin" style="width: 10em;" />
							and <input type="text" class="date" name="uploaded_end" style="width: 10em;" />
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
								<option value="100">100</option>
								<option value="250">250</option>
								<option value="500">500</option>
								<option value="1000">1,000</option>
								<option value="2500">2,500</option>
							</select>
							miles of 
							<input type="text" name="location" class="photo_geo" style="width: 15em;" />
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
							<input type="text" name="views" style="width: 4em;" />
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
		</form>
		
		<hr />
		
		<h1>Photos (<?php echo number_format($photo_ids->photo_count); ?>)</h1>
		<p>
			<?php

			foreach($photos->photos as $photo){
				?>
				<a href="<?php echo BASE . ADMIN . 'photo' . URL_ID . $photo['photo_id'] . URL_RW; ?>">
					<img src="<?php echo $photo['photo_src_square']; ?>" alt="" title="<?php echo $photo['photo_title']; ?>" class="frame" />
				</a>
				<?php
			}
			?>
		</p>
		<?php
		if($photo_ids->page_count > 1){
			?>
			<p>
				<?php
				if(!empty($photo_ids->page_previous)){
					for($i = 1; $i <= $photo_ids->page_previous; ++$i){
						echo '<a href="' . BASE . ADMIN . 'library' . URL_PAGE . $i . URL_RW . '" class="page_no">' . number_format($i) . '</a>';
					}
				}
				?>
				<span class="page_no">Page <?php echo $photo_ids->page; ?> of <?php echo $photo_ids->page_count; ?></span>
				<?php
				if(!empty($photo_ids->page_next)){
					for($i = $photo_ids->page_next; $i <= $photo_ids->page_count; ++$i){
						echo '<a href="' . BASE . ADMIN . 'library' . URL_PAGE . $i . URL_RW . '" class="page_no">' . number_format($i) . '</a>';
					}
				}
				?>
			</p>
			<?php
		}
		?>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>