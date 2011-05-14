<?php

class Lessn extends Orbit{
	public $lessn_uri;
	public $lessn_api_key;
	public $lessn_transmit;
	
	private $tumblr_password;
	
	public function __construct(){
		parent::__construct();
		
		$this->lessn_uri = $this->returnPref('lessn_uri');
		$this->lessn_api_key = $this->returnPref('lessn_api_key');
		$this->lessn_transmit = $this->returnPref('lessn_transmit');
		
		if(empty($this->lessn_transmit)){
			$this->lessn_transmit = array();
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_config(){
		$last_update = 1305226245;
		
		// Import default SQL
		if($this->returnPref('lessn_last_update') < $last_update){
			$queries = file_get_contents(PATH . EXTENSIONS . $this->folder . '/sql/' . $this->db_type . '.sql');
			$queries = explode("\n", $queries);

			foreach($queries as $query){
				$query = trim($query);
				if(!empty($query)){
					$this->exec($query);
				}
			}
			
			$now = time();
			$this->setPref('lessn_last_update', $now);
			$this->savePref();
		}
		
		?>
		<p>Every time you create, edit, or publish an image, post, or page, a shortened link will be generated if it does not already exist using your local <a href="http://www.shauninman.com/archive/2009/08/17/less_n">Lessn</a> installation. You can use <code>{Item_Lessn_URI}</code> (such as, <code>{Image_Lessn_URI}</code>) to use your new shortened links.</p>
		
		<table>
			<tr>
				<td class="right pad"><label for="lessn_uri">Lessn URI:</label></td>
				<td>
					<input type="text" class="m" placeholder="http://mydoma.in/-/" id="lessn_uri" name="lessn_uri" value="<?php echo $this->lessn_uri; ?>" />
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="lessn_api_key">Lessn API Key:</label></td>
				<td>
					<input type="text" class="m" placeholder="" id="lessn_api_key" name="lessn_api_key" value="<?php echo $this->lessn_api_key; ?>" />
					<p class="quiet">Check your Lessn&#8217;s URI (that is, your admin page) for your API key</p>
				</td>
			</tr>
			<tr>
				<td class="right"><label>Generate links for:</label></td>
				<td>
					<table>
						<tr>
							<td class="right" style="width: 20px;"><input type="checkbox" id="lessn_transmit_images" name="lessn_transmit_images" value="images" <?php if(in_array('images', $this->lessn_transmit)){ echo 'checked="checked"'; }; ?> /></td>
							<td><label for="lessn_transmit_images">Images</label></td>
						</tr>
						<tr>
							<td class="right" style="width: 20px;"><input type="checkbox" id="lessn_transmit_pages" name="lessn_transmit_pages" value="pages" <?php if(in_array('pages', $this->lessn_transmit)){ echo 'checked="checked"'; }; ?> /></td>
							<td><label for="lessn_transmit_pages">Pages</label></td>
						</tr>
						<tr>
							<td class="right" style="width: 20px;"><input type="checkbox" id="lessn_transmit_posts" name="lessn_transmit_posts" value="posts" <?php if(in_array('posts', $this->lessn_transmit)){ echo 'checked="checked"'; }; ?> /></td>
							<td><label for="lessn_transmit_posts">Posts</label></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<?php
	}
	
	public function orbit_config_save(){
		$this->setPref('lessn_uri', @$_POST['lessn_uri']);
		$this->setPref('lessn_api_key', @$_POST['lessn_api_key']);
		
		$transmit = array();
		
		if($_POST['lessn_transmit_images'] == 'images'){
			$transmit[] = 'images';
		}
		
		if($_POST['lessn_transmit_pages'] == 'pages'){
			$transmit[] = 'pages';
		}
		
		if($_POST['lessn_transmit_posts'] == 'posts'){
			$transmit[] = 'posts';
		}
		
		$this->setPref('lessn_transmit', $transmit);
		
		$this->savePref();
	}
	
	public function orbit_image($images){
		if(!in_array('images', $this->lessn_transmit)){ return; }
		
		if(count($images) < 1){ return; }
		
		// Seek array for new image
		foreach($images as $image){
			if(empty($image['image_lessn_uri'])){
				$this->storeTask(array($this, 'lessn'), $image, 'image_', 'images');
			}
		}
	}
	
	public function orbit_post($posts){
		if(!in_array('posts', $this->lessn_transmit)){ return; }
		
		if(count($posts) < 1){ return; }
		
		// Seek array for new post
		foreach($posts as $post){
			if(empty($post['post_lessn_uri'])){
				$this->storeTask(array($this, 'lessn'), $post, 'post_', 'posts');
			}
		}
	}
	
	public function orbit_page($pages){
		if(!in_array('pages', $this->lessn_transmit)){ return; }
		
		if(count($pages) < 1){ return; }
		
		// Seek array for new page
		foreach($pages as $page){
			if(empty($page['page_lessn_uri'])){
				$this->storeTask(array($this, 'lessn'), $page, 'page_', 'pages');
			}
		}
	}
	
	/**
	 * Shorten URI with Lessn
	 *
	 * @param string $uri Unshortened URI
	 * @return string Shortened URI
	 */
	public function lessn($item, $prefix, $table){
		$id_field = $prefix . 'id';
		$uri_field = $prefix . 'uri';
		$shortened_uri_field = $prefix . 'lessn_uri';
		
		$params = array('api' => $this->lessn_api_key,
			'url' => $item[$uri_field]);
		echo $this->lessn_uri . '?' . http_build_query($params);
		$shortened_uri = file_get_contents($this->lessn_uri . '?' . http_build_query($params));
		
		$this->updateRow(array($shortened_uri_field => $shortened_uri), $table, $item[$id_field]);
	}
}

?>