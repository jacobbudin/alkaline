<?php

class Gravatar extends Orbit{
	public $gravatar_default;
	public $gravatar_size;
	public $gravatar_max_rating;
	
	public function __construct(){
		parent::__construct();
		
		$this->gravatar_size = $this->returnPref('gravatar_size');
		$this->gravatar_default = $this->returnPref('gravatar_default');
		$this->gravatar_max_rating = $this->returnPref('gravatar_max_rating', 'r');
		
		if(empty($this->gravatar_size) or !intval($this->gravatar_size)){
			$this->gravatar_size = 80;
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function comment_add($fields){
		$email = $fields['comment_author_email'];
		$gravatar = 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=' . urlencode($this->gravatar_default) . '&s=' . $this->gravatar_size . '&r=' . $this->gravatar_max_rating . '&$img=false';
		$fields['comment_author_avatar'] = $gravatar;
		return $fields;
	}
	
	public function config(){
		?>
		<p>For more information on Gravatar, visit <a href="http://www.gravatar.com/">Gravatar&#8217;s Web site</a>.</p>

		<table>
			<tr>
				<td class="right pad"><label for="gravatar_size">Avatar size:</label></td>
				<td><input type="text" id="gravatar_size" name="gravatar_size" value="<?php echo $this->gravatar_size; ?>" class="xs" /> pixels</td>
			</tr>
			<tr>
				<td class="right pad"><label for="gravatar_default">Default avatar:</label></td>
				<td>
					<input type="text" id="gravatar_default" name="gravatar_default" value="<?php echo $this->gravatar_default; ?>" style="width: 40em;" /><br />
					<span class="quiet">Full URL of avatar image file (optional)</span>
				</td>
			</tr>
			<tr>
				<td class="right middle"><label for="gravatar_max_rating">Maximum rating:</label></td>
				<td>
					<select id="gravatar_max_rating" name="gravatar_max_rating">
						<option value="g" <?php echo $this->readPref('gravatar_max_rating', 'g'); ?>>G</option>
						<option value="pg" <?php echo $this->readPref('gravatar_max_rating', 'pg'); ?>>PG</option>
						<option value="r" <?php echo $this->readPref('gravatar_max_rating', 'r'); ?>>R</option>
						<option value="x" <?php echo $this->readPref('gravatar_max_rating', 'x'); ?>>X</option>
					</select>
				</td>
			</tr>
		</table>
		<?php
	}
	
	public function config_save(){
		if(isset($_POST['gravatar_size'])){
			$this->setPref('gravatar_size', $_POST['gravatar_size']);
			$this->setPref('gravatar_default', $_POST['gravatar_default']);
			$this->setPref('gravatar_max_rating', $_POST['gravatar_max_rating']);
			$this->savePref();
			
			if($_POST['gravatar_size'] != $this->gravatar_size){
				$query = $this->prepare('SELECT comment_id, comment_author_avatar FROM comments WHERE LOWER(comment_author_avatar) LIKE :comment_author_avatar;');
				$query->execute(array(':comment_author_avatar' => '%www.gravatar.com%'));
				$comments = $query->fetchAll();
				
				if(@count($comments) > 0){
					$query = $this->prepare('UPDATE comments SET comment_author_avatar = :comment_author_avatar WHERE comment_id = :comment_id;');
					foreach($comments as $comment){
						$comment_author_avatar = preg_replace('#\&s=(.*?)&#si', '&s=' . $_POST['gravatar_size'] . '&', $comment['comment_author_avatar']);
						$query->execute(array(':comment_author_avatar' => $comment_author_avatar, ':comment_id' => $comment['comment_id']));
					}
				}
			}
			
			if($_POST['gravatar_default'] != $this->gravatar_default){
				$query = $this->prepare('SELECT comment_id, comment_author_avatar FROM comments WHERE LOWER(comment_author_avatar) LIKE :comment_author_avatar;');
				$query->execute(array(':comment_author_avatar' => '%www.gravatar.com%'));
				$comments = $query->fetchAll();
				
				if(@count($comments) > 0){
					$query = $this->prepare('UPDATE comments SET comment_author_avatar = :comment_author_avatar WHERE comment_id = :comment_id;');
					foreach($comments as $comment){
						$comment_author_avatar = preg_replace('#\?d=(.*?)&#si', '?d=' . urlencode($_POST['gravatar_default']) . '&', $comment['comment_author_avatar']);
						$query->execute(array(':comment_author_avatar' => $comment_author_avatar, ':comment_id' => $comment['comment_id']));
					}
				}
			}
			
			if($_POST['gravatar_max_rating'] != $this->gravatar_max_rating){
				$query = $this->prepare('SELECT comment_id, comment_author_avatar FROM comments WHERE LOWER(comment_author_avatar) LIKE :comment_author_avatar;');
				$query->execute(array(':comment_author_avatar' => '%www.gravatar.com%'));
				$comments = $query->fetchAll();
				
				if(@count($comments) > 0){
					$query = $this->prepare('UPDATE comments SET comment_author_avatar = :comment_author_avatar WHERE comment_id = :comment_id;');
					foreach($comments as $comment){
						$comment_author_avatar = preg_replace('#\&r=(.*?)&#si', '&r=' . $_POST['gravatar_max_rating'] . '&', $comment['comment_author_avatar']);
						$query->execute(array(':comment_author_avatar' => $comment_author_avatar, ':comment_id' => $comment['comment_id']));
					}
				}
			}
		}
	}
}

?>