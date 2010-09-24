		</div>
		<hr />
		<div id="footer" class="span-24 last">
			Powered by <a href="http://www.alkalineapp.com/">Alkaline</a>. Copyright &#0169; 2010 by <a href="http://www.budinltd.com/">Budin Ltd.</a> All rights reserved.<br />
			<?php
			
			if($alkaline->returnConf('maint_debug')){
				$debug = $alkaline->debug();
				echo 'Execution time: ' . round($debug['execution_time'], 3) . ' seconds. Queries: ' . $debug['queries']  . '.';
			}

			?>
		</div>
	</div>
</body>
</html>