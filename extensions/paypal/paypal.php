<?php

class PayPal extends Orbit{
	public function __construct(){
		parent::__construct();
		
		$this->pp_email = $this->returnPref('pp_email');
		$this->pp_currency = $this->returnPref('pp_currency');
		$this->pp_shipping = $this->returnPref('pp_shipping');
		$this->pp_tax_rate = $this->returnPref('pp_tax_rate');
		$this->pp_items_html = $this->returnPref('pp_items_html');
		$this->pp_submit_html = $this->returnPref('pp_submit_html');
		$this->pp_format = $this->returnPref('pp_format');
		$this->pp_tag = $this->returnPref('pp_tag');
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_image($images){
		if(!empty($this->pp_email)){
			foreach($images as &$image){
				if(!empty($this->pp_tag)){
					if(!in_array($this->pp_tag, $image['image_tags_array'])){
						continue;
					}
				}
				$paypal = '<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="item_name" value="Image #' . $image['image_id'];
				if(!empty($image['image_title'])){
					$paypal .= ' (' . $this->makeStringSafe($image['image_title']) . ')';
				}
				$paypal .= '">' . $this->pp_items_html . $this->pp_submit_html . '</form>';
				$image['image_paypal'] = $paypal;
			}
		}
		
		return $images;
	}
	
	public function makeStringSafe($str){
		$find = array('&#8220;', '&#8221;', '&#8217;');
		$replace = array('&quot;', '&quot;', '&apos;');
		$str = str_replace($find, $replace, $str);
		return strtr(utf8_decode($str), utf8_decode('“”’ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ'), 'pppaaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr');
	}
	
	public function orbit_body_close(){
		if(!empty($this->pp_email)){
			?>
			<script src="<?php echo BASE . EXTENSIONS . $this->folder; ?>/js/minicart.js" type="text/javascript"></script>
		    <script type="text/javascript">
				PAYPAL.apps.MiniCart.render();
		    </script>
			<?php
		}
	}
	
	public function orbit_config(){
		$this->pp_email = $this->makeHTMLSafe($this->pp_email);
		$this->pp_tag = $this->makeHTMLSafe($this->pp_tag);
		
		?>
		<p>To accept payments using this extension you will need a PayPal account. For more information on PayPal, visit <a href="http://www.paypal.com/">PayPal&#8217;s Web site</a>. Add the Canvas tag <code>{Image_PayPal}</code> to a <code>{block:Images}</code> in your template. You will receive an email from PayPal when you make a sale.</p>

		<table>
			<tr>
				<td class="right pad"><label for="pp_email">Seller email address:</label></td>
				<td>
					<input type="text" id="pp_email" name="pp_email" value="<?php echo $this->pp_email; ?>" class="m" /><br />
					<span class="quiet">PayPal payments will be sent to this email address</span>
				</td>
			</tr>
			<tr>
				<td class="right"><label for="pp_currency">Default currency:</label></td>
				<td>
					<select id="pp_currency" name="pp_currency">
						<?php
						
						$currencies = array('USD', 'AUD', 'BRL', 'GBP', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'SGD', 'SEK', 'CHF', 'THB');
						
						foreach($currencies as $currency){
							echo '<option value="' . $currency . '"';
							if($currency == $this->pp_currency){
								echo ' selected="selected"';
							}
							echo '>' . $currency . '</option>';
						}
						
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="pp_shipping">Shipping (per item):</label></td>
				<td>
					<input type="text" id="pp_shipping" name="pp_shipping" value="<?php echo $this->pp_shipping; ?>" placeholder="0.00" class="xs" /> <span class="quiet" class="pp_currency"></span> <span class="quiet">(optional)</span><br />
					<span class="quiet">If your shipping costs vary on different items, include the cost in the individual items&#8217; prices and offer &#8220;free&#8221; shipping</span>
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="pp_tax_rate">Tax rate:</label></td>
				<td>
					<input type="text" id="pp_tax_rate" name="pp_tax_rate" class="xs" value="<?php echo $this->pp_tax_rate; ?>" placeholder="0.000" /> % <span class="quiet">(optional)</span>
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="">Items:</label></td>
				<td>
					<?php
					
					$pp_items = $this->returnPref('pp_items');
					
					if(empty($pp_items)){
						?>
						<input type="text" id="pp_item_0" name="pp_item_0" placeholder="Name" class="s pp_item" />
						<input type="text" id="pp_price_0" name="pp_price_0" placeholder="Price" class="xs pp_price" />
						<a id="pp_add_item"><button>Add another item</button></a>
						<?php
					}
					else{
						$pp_items = unserialize($pp_items);
						$pp_item_count = count($pp_items);
						
						for($i=0; $i < $pp_item_count; $i++){
							$item_label = 'pp_item_' . $i;
							$price_label = 'pp_price_' . $i;
							?>
							<input type="text" id="<?php echo $item_label; ?>" name="<?php echo $item_label; ?>" value="<?php echo $this->makeHTMLSafe($pp_items[$i]['name']); ?>" placeholder="Name" class="s pp_item" />
							<input type="text" id="<?php echo $price_label; ?>" name="<?php echo $price_label; ?>" value="<?php echo $pp_items[$i]['price']; ?>" placeholder="Price" class="xs pp_price" />
							<?php
							if($pp_item_count != ($i + 1)){ echo '<br />'; }
						}
						
						if($i < 9){
							echo '<a id="pp_add_item"><button>Add another item</button></a>';
						}
						
					}
					
					?>
				</td>
			</tr>
			<tr>
				<td class="right"><label for="pp_format">Item listing format:</label></td>
				<td>
					<select id="pp_format" name="pp_format">
						<option value="emdash" <?php if($this->pp_format == 'emdash'){ echo 'selected="selected"'; } ?>>Item name &#8212; Price</option>
						<option value="parentheses" <?php if($this->pp_format == 'parentheses'){ echo 'selected="selected"'; } ?>>Item name (Price)</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="right"><label for="pp_submit_html">Submit button:</label></td>
				<td>
					<textarea type="text" id="pp_submit_html" name="pp_submit_html" style="width: 30em;" class="code"><?php echo $this->pp_submit_html; ?></textarea><br />
					<span class="quiet">Include a form input submit button above (or leave empty for default).</span>
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="pp_tag">Only sell items with tag:</label></td>
				<td>
					<input type="text" id="pp_tag" name="pp_tag" value="<?php echo $this->pp_tag; ?>" placeholder="!paypal" class="s" /> <span class="quiet">(optional)</span><br />
					<span class="quiet">You may use an <code>!invisible</code> tag</span>
				</td>
			</tr>
		</table>
		<script type="text/javascript">
			$(document).ready(function(){
				$('#pp_add_item').click(function(event){
					count = 0;
					$('.pp_item').each(function(){ count += 1; });
					if(count == 9){ $(this).hide(); }
					count = count.toString();
					$(this).before('<br /><input type="text" id="pp_item_' + count + '" name="pp_item_' + count + '" placeholder="Name" class="s pp_item" /> <input type="text" id="pp_price_' + count + '" name="pp_price_' + count + '" placeholder="Price" class="xs pp_price" /> ');
					event.preventDefault();
				});
			});
		</script>
		<?php
	}
	
	public function orbit_config_save(){
		if(isset($_POST['pp_email'])){
			$pp_items = array();
			
			for($i=0; $i < 10; $i++){
				$item_label = 'pp_item_' . $i;
				$price_label = 'pp_price_' . $i;
				if(!empty($_POST[$item_label]) and !empty($_POST[$price_label])){
					$pp_items[] = array('name' => $this->reverseHTMLSafe($_POST[$item_label]),
						'price' => $_POST[$price_label]);
				}
			}
			
			$shipping = preg_replace('#[^0-9\.]#si', '', $_POST['pp_shipping']);
			
			$pp_items_html = '<input type="hidden" name="cmd" value="_cart">
			<input type="hidden" name="business" value="' . $_POST['pp_email'] . '">
			<input type="hidden" name="lc" value="US">
			<input type="hidden" name="button_subtype" value="products">
			<input type="hidden" name="no_note" value="0">
			<input type="hidden" name="currency_code" value="' . $_POST['pp_currency'] . '">
			<input type="hidden" name="shipping" value="' . number_format($shipping, 2, '.', '') . '">
			<input type="hidden" name="add" value="1">
			<input type="hidden" name="bn" value="PP-ShopCartBF:btn_cart_SM.gif:NonHostedGuest">
			<table><input type="hidden" name="on0" value="Item"><select name="os0" class="paypal_items">';
			if($_POST['pp_format'] == 'emdash'){
				foreach($pp_items as $item){
					$pp_items_html .= '<option value="' . $this->makeHTMLSafe($item['name']) . '">' . $this->makeHTMLSafe($item['name']) . ' &#8212; ' . $item['price'] . '</option>';
				}
			}
			else{
				foreach($pp_items as $item){
					$pp_items_html .= '<option value="' . $this->makeHTMLSafe($item['name']) . '">' . $this->makeHTMLSafe($item['name']) . ' (' . $item['price'] . ')</option>';
				}
			}
			$pp_items_html .= '</select><input type="hidden" name="currency_code" value="USD">';
			$i = 0;
			foreach($pp_items as $item){
				$pp_items_html .= '<input type="hidden" name="option_select' . $i . '" value="' . $this->makeHTMLSafe($item['name']) . '">';
				$pp_items_html .= '<input type="hidden" name="option_amount' . $i . '" value="' . number_format(preg_replace('#[^0-9\.]#si', '', $item['price']), 2, '.', '') . '">';
				$i++;
			}
			$pp_items_html .= '<input type="hidden" name="option_index" value="0">';
			
			$this->setPref('pp_email', $this->reverseHTMLSafe($_POST['pp_email']));
			$this->setPref('pp_currency', $_POST['pp_currency']);
			$this->setPref('pp_shipping', $_POST['pp_shipping']);
			$this->setPref('pp_tax_rate', $_POST['pp_tax_rate']);
			$this->setPref('pp_items', serialize($pp_items));
			$this->setPref('pp_items_html', $pp_items_html);
			$this->setPref('pp_format', $_POST['pp_format']);
			$this->setPref('pp_tag', $this->reverseHTMLSafe($_POST['pp_tag']));
			if(empty($_POST['pp_submit_html'])){
				$_POST['pp_submit_html'] = '<br />' . "\n\n" . '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_cart_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" style="border:none; -webkit-box-shadow: none; -moz-box-shadow: none;">' . "\n\n" . '<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">';
			}
			$this->setPref('pp_submit_html', $_POST['pp_submit_html']);
			$this->savePref();
		}
	}
}

?>