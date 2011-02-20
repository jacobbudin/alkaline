<?php

class Fotomoto extends Orbit{
	private $fm_account_id;
	
	public function __construct(){
		parent::__construct();
		
		$this->fm_account_id = $this->returnPref('fm_account_id');
		$this->fm_buy_html = $this->returnPref('fm_buy_html');
		$this->fm_print_html = $this->returnPref('fm_print_html');
		$this->fm_file_html = $this->returnPref('fm_file_html');
		$this->fm_card_html = $this->returnPref('fm_card_html');
		$this->fm_ecard_html = $this->returnPref('fm_ecard_html');
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_image($images){
		if(!empty($this->fm_account_id)){
			foreach($images as &$image){
				$image['image_buy'] = '<a href="#" onclick="FOTOMOTO.API.showWindow(FOTOMOTO.API.BUY,\'' . $image['image_src_admin'] . '\'); event.preventDefault();">' . $this->fm_buy_html . '</a>';
				$image['image_print'] = '<a href="#" onclick="FOTOMOTO.API.showWindow(FOTOMOTO.API.PRINT,\'' . $image['image_src_admin'] . '\'); event.preventDefault();">' . $this->fm_print_html . '</a>';
				$image['image_file'] = '<a href="#" onclick="FOTOMOTO.API.showWindow(FOTOMOTO.API.FILE,\'' . $image['image_src_admin'] . '\'); event.preventDefault();">' . $this->fm_file_html . '</a>';
				$image['image_card'] = '<a href="#" onclick="FOTOMOTO.API.showWindow(FOTOMOTO.API.CARD,\'' . $image['image_src_admin'] . '\'); event.preventDefault();">' . $this->fm_card_html . '</a>';
				$image['image_ecard'] = '<a href="#" onclick="FOTOMOTO.API.showWindow(FOTOMOTO.API.ECARD,\'' . $image['image_src_admin'] . '\'); event.preventDefault();">' . $this->fm_ecard_html . '</a>';
			}
			
			return $images;
		}
	}
	
	public function orbit_body_close(){
		if(!empty($this->fm_account_id)){
			?>
			<script type="text/javascript" src="http://widget.fotomoto.com/stores/script/<?php echo $this->fm_account_id; ?>.js?api=true"></script>
			<?php
		}
	}
	
	public function orbit_config(){
		$this->fm_account_id = $this->makeHTMLSafe($this->fm_account_id);
		$this->fm_buy_html = $this->makeHTMLSafe($this->fm_buy_html);
		$this->fm_print_html = $this->makeHTMLSafe($this->fm_print_html);
		$this->fm_file_html = $this->makeHTMLSafe($this->fm_file_html);
		$this->fm_card_html = $this->makeHTMLSafe($this->fm_card_html);
		$this->fm_ecard_html = $this->makeHTMLSafe($this->fm_ecard_html);
		
		?>
		<p>To use this extension you will need a <a href="http://www.fotomoto.com/">Fotomoto account</a>. For more information on Fotomoto, visit <a href="http://www.fotomoto.com/">Fotomoto&#8217;s Web site</a>.</p>
		
		<p>Use by adding the following Canvas tags: {Image_Buy}, {Image_Print}, {Image_File}, {Image_Card}, {Image_Ecard}</p>

		<table>
			<tr>
				<td class="right pad"><label for="fm_account_id">Account identifier:</label></td>
				<td>
					<input type="text" id="fm_account_id" name="fm_account_id" value="<?php echo $this->fm_account_id; ?>" class="m" /><br />
					<span class="quiet">Looks like: bbdd80a787dd8decd4ab89ffc831f98836255d15</span>
				</td>
			</tr>
		</table>
		
		<table>
			<tr>
				<td class="right pad"><label for="fm_buy_html">Buy image (HTML):</label></td>
				<td>
					<input type="text" id="fm_buy_html" name="fm_buy_html" value="<?php echo $this->fm_buy_html; ?>" class="m" />
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="fm_print_html">Buy print (HTML):</label></td>
				<td>
					<input type="text" id="fm_print_html" name="fm_print_html" value="<?php echo $this->fm_print_html; ?>" class="m" />
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="fm_file_html">Buy file (HTML):</label></td>
				<td>
					<input type="text" id="fm_file_html" name="fm_file_html" value="<?php echo $this->fm_file_html; ?>" class="m" />
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="fm_card_html">Buy card (HTML):</label></td>
				<td>
					<input type="text" id="fm_card_html" name="fm_card_html" value="<?php echo $this->fm_card_html; ?>" class="m" />
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="fm_ecard_html">Send ecard (HTML):</label></td>
				<td>
					<input type="text" id="fm_ecard_html" name="fm_ecard_html" value="<?php echo $this->fm_ecard_html; ?>" class="m" />
				</td>
			</tr>
		</table>
		<?php
	}
	
	public function orbit_config_save(){
		if(isset($_POST['fm_account_id'])){
			$this->setPref('fm_account_id', $this->reverseHTMLSafe($_POST['fm_account_id']));
			$this->setPref('fm_buy_html', $this->reverseHTMLSafe($_POST['fm_buy_html']));
			$this->setPref('fm_print_html',$this->reverseHTMLSafe($_POST['fm_print_html']));
			$this->setPref('fm_file_html', $this->reverseHTMLSafe($_POST['fm_file_html']));
			$this->setPref('fm_card_html', $this->reverseHTMLSafe($_POST['fm_card_html']));
			$this->setPref('fm_ecard_html', $this->reverseHTMLSafe($_POST['fm_ecard_html']));
			$this->savePref();
		}
	}
}

?>