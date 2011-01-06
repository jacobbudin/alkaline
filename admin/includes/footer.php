		</div>
		<hr />
		<div id="footer" class="span-24 last">
			<img src="/images/icon.png" alt="" /> Powered by <a href="http://www.alkalineapp.com/">Alkaline</a>. Copyright &#0169; 2010 by <a href="http://www.budinltd.com/">Budin Ltd.</a> All rights reserved. <?php echo Alkaline::returnErrors(); ?><br />
			<?php
			
			if(!empty($alkaline) and $alkaline->returnConf('maint_debug')){
				$debug = $alkaline->debug();
				echo 'Execution time: ' . round($debug['execution_time'], 3) . ' seconds. Queries: ' . $debug['queries']  . '.';
			}

			?>
		</div>
	</div>
</body>
</html>