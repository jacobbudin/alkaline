<?php

class GoogleMaps extends Orbit{
	public $maps_provider;
	public $maps_height;
	public $maps_width;
	
	public function __construct(){
		parent::__construct();
		$this->maps_provider = $this->returnPref('maps_provider', 'google');
		$this->maps_type = $this->returnPref('maps_type');
		$this->maps_height = $this->returnPref('maps_height', 350);
		$this->maps_width = $this->returnPref('maps_width', 425);
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_image($images){
		foreach($images as &$image){
			if(!empty($image['image_geo'])){
				$image_geo = urlencode($image['image_geo']);
				if(($this->maps_provider == 'google') and ($this->maps_type == 'road')){
					$image['image_map'] = '<iframe width="' . $this->maps_width . '" height="' . $this->maps_height . '" frameborder="0" class="image_map" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=' . $image_geo . '&amp;ie=UTF8&amp;output=embed"></iframe>';
				}
				elseif(($this->maps_provider == 'google') and ($this->maps_type == 'sat')){
					$image['image_map'] = '<iframe width="' . $this->maps_width . '" height="' . $this->maps_height . '" frameborder="0" class="image_map" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=' . $image_geo . '&amp;ie=UTF8&amp;t=h&amp;output=embed"></iframe>';
				}
			}
		}
		
		return $images;
	}
	
	public function orbit_config(){
		?>
		<p>Integrate maps by adding the {Image_Map} Canvas tag to your {block:Image} loop.</p>

		<table>
			<!-- <tr>
				<td class="right middle"><label for="maps_provider">Map provider:</label></td>
				<td>
					<select id="maps_provider" name="maps_provider">
						<option value="bing" <?php echo $this->readPref('map_provider', 'bing'); ?>>Bing</option>
						<option value="google" <?php echo $this->readPref('map_provider', 'google'); ?>>Google</option>
					</select>
				</td>
			</tr> -->
			<tr>
				<td class="right middle"><label for="maps_type">Map type:</label></td>
				<td>
					<select id="maps_type" name="maps_type">
						<option value="sat" <?php echo $this->readPref('maps_type', 'sat'); ?>>Satellite</option>
						<option value="road" <?php echo $this->readPref('maps_type', 'road'); ?>>Road</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="right middle"><label>Map size:</label></td>
				<td><input type="text" id="maps_width" name="maps_width" value="<?php echo $this->maps_width; ?>" style="width: 4em;" /> pixels (width) &#0215; <input type="text" id="maps_height" name="maps_height" value="<?php echo $this->maps_height; ?>" style="width: 4em;" /> pixels (height)</td>
			</tr>
		</table>
		<?php
	}
	
	public function orbit_config_save(){
		// $this->setPref('maps_provider', $_POST['maps_provider']);
		$this->setPref('maps_type', $_POST['maps_type']);
		$this->setPref('maps_height', $_POST['maps_height']);
		$this->setPref('maps_width', $_POST['maps_width']);
		$this->savePref();
	}
}

?>