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
$orbit = new Orbit;

$user->perm(true, 'rights');

if(!empty($_GET['id'])){
	$right_id = $alkaline->findID($_GET['id']);
}

if(!empty($_GET['act'])){
	$right_act = $_GET['act'];
}

// SAVE CHANGES
if(!empty($_POST['right_id'])){
	$right_id = $alkaline->findID($_POST['right_id']);
	
	$right = new Right($right_id);
	
	// Merge rights set
	if(@$_POST['right_merge'] == 'merge'){
		$right_merge_id = $_POST['right_merge_id'];
		
		if(empty($right_merge_id)){
			$right_merge_id = '';
		}
		
		$right->merge($right_merge_id);
	}
	
	// Delete rights set
	if(!empty($_POST['right_delete']) and ($_POST['right_delete'] == 'delete')){
		if($right->delete()){
			$alkaline->addNote('The right set has been deleted.', 'success');
		}
	}
	elseif(!empty($_POST['right_recover']) and ($_POST['right_recover'] == 'recover')){
		if($right->recover()){
			$alkaline->addNote('The right set has been recovered.', 'success');
		}
	}
	// Update rights set
	else{
		$right_title = $_POST['right_title'];
		$right_description_raw = $_POST['right_description_raw'];
		
		// Configuration: right_markup
		if(!empty($_POST['right_markup'])){
			$right_markup_ext = $_POST['right_markup_ext'];
			$right_description = $orbit->hook('markup_' . $right_markup_ext, $right_description_raw, $right_description_raw);
			$right_title = $orbit->hook('markup_title_' . $right_markup_ext, $right_title, $right_title);
		}
		elseif($alkaline->returnConf('web_markup')){
			$right_markup_ext = $alkaline->returnConf('web_markup_ext');
			$right_description = $orbit->hook('markup_' . $right_markup_ext, $right_description_raw, $right_description_raw);
			$right_title = $orbit->hook('markup_title_' . $right_markup_ext, $right_title, $right_title);
		}
		else{
			$right_markup_ext = '';
			$right_description = $this->nl2br($right_description_raw);
		}
		
		$fields = array('right_title' => $alkaline->makeUnicode($right_title),
			'right_description_raw' => $alkaline->makeUnicode($right_description_raw),
			'right_description' => $alkaline->makeUnicode($right_description));
		
		$right->updateFields($fields);
	}
	
	unset($right_id);
}
else{
	$alkaline->deleteEmptyRow('rights', array('right_title'));
}

// CREATE RIGHTS SET
if($right_act == 'add'){
	$right_id = $alkaline->addRow(null, 'rights');
}

define('TAB', 'features');

// GET RIGHTS SETS TO VIEW OR RIGHTS SET TO EDIT
if(empty($right_id)){
	$alkaline->updateCounts('images', 'rights', 'right_image_count');
	
	$right_ids = new Find('rights');
	$right_ids->sort('right_modified', 'DESC');
	$right_ids->find();
	
	$rights = new Right($right_ids);
	
	define('TITLE', 'Alkaline Right Sets');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'rights' . URL_ACT . 'add' . URL_RW; ?>"><button>Add rights set</button></a></div>

	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/rights.png" alt="" /> Right Sets (<?php echo $rights->right_count; ?>)</h1>
	
	<p>Right sets clarify which copyrights you retain on your images to discourage illicit use.</p>
	
	<p>
		<input type="search" name="filter" placeholder="Filter" class="s" results="0" />
	</p>
	
	<table class="filter">
		<tr>
			<th>Title</th>
			<th class="center">Images</th>
			<th>Created</th>
			<th>Last modified</th>
		</tr>
		<?php
	
		foreach($rights->rights as $right){
			echo '<tr class="ro">';
				echo '<td><strong class="large"><a href="' . BASE . ADMIN . 'rights' . URL_ID . $right['right_id'] . URL_RW . '" class="tip" title="' . $alkaline->fitStringByWord($right['right_description'], 150) . '">' . $right['right_title'] . '</a></strong></td>';
				echo '<td class="center"><a href="' . BASE . ADMIN . 'search' . URL_ACT . 'rights' . URL_AID . $right['right_id'] . URL_RW . '">' . $right['right_image_count'] . '</a></td>';
				echo '<td>' . $alkaline->formatTime($right['right_created']) . '</td>';
				echo '<td>' . ucfirst($alkaline->formatRelTime($right['right_modified'])) . '</td>';
			echo '</tr>';
		}
	
		?>
	</table>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{	
	// Update image count on rights set
	$image_ids = new Find('images');
	$image_ids->rights($right_id);
	$image_ids->find();
	
	$fields = array('right_image_count' => $image_ids->count);
	$alkaline->updateRow($fields, 'rights', $right_id, false);
	
	// Get rights set
	$right = $alkaline->getRow('rights', $right_id);
	$right = $alkaline->makeHTMLSafe($right);

	if(!empty($right['right_title'])){	
		define('TITLE', 'Alkaline Right Set: &#8220;' . $right['right_title']  . '&#8221;');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'rights' . URL_AID . $right['right_id'] . URL_RW; ?>"><button>View images (<?php echo $image_ids->count; ?>)</button></a></div>
	
	<?php
	
	if(empty($right['right_title'])){
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/rights.png" alt="" /> New Right Set</h1>';
	}
	else{
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/rights.png" alt="" /> Right Set: ' . $right['right_title'] . '</h1>';
	}
	
	?>
	
	<form id="rights" action="<?php echo BASE . ADMIN . 'rights' . URL_CAP; ?>" method="post">
		<div class="span-24 last">
			<div class="span-15 append-1">
				<input type="text" id="right_title" name="right_title" placeholder="Title" value="<?php echo $right['right_title']; ?>" class="title notempty" />
				<textarea id="right_description_raw" name="right_description_raw" placeholder="Description" class="<?php if($user->returnPref('text_code')){ echo $user->returnPref('text_code_class'); } ?>"><?php echo $right['right_description_raw']; ?></textarea>
			</div>
			<div class="span-8 last">
				<table>
					<tr>
						<td class="right" style="width: 5%"><input type="checkbox" id="right_merge" name="right_merge" value="merge" /></td>
						<td>
							<label for="right_merge">Transfer images to <?php echo $alkaline->showRights('right_merge_id'); ?> rights set.</label><br />
							This action cannot be undone.
						</td>
					</tr>
					<?php if(empty($right['right_deleted'])){ ?>
					<tr>
						<td class="right" style="width: 5%"><input type="checkbox" id="right_delete" name="right_delete" value="delete" /></td>
						<td>
							<label for="right_delete">Delete this rights set.</label>
						</td>
					</tr>
					<?php } else{ ?>
					<tr>
						<td class="right" style="width: 5%"><input type="checkbox" id="right_recover" name="right_recover" value="recover" /></td>
						<td>
							<strong><label for="right_recover">Recover this right.</label></strong>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>
		</div>
		
		<input type="hidden" id="right_markup" name="right_markup" value="<?php echo $right['right_markup']; ?>" />
		
		<p>
			<input type="hidden" name="right_id" value="<?php echo $right['right_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a>
		</p>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>