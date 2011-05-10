		</div>
		<hr class="invisible" />
		<div id="footer" class="span-24 last">
			<img src="<?php echo BASE . ADMIN; ?>images/icon.png" alt="" /> Powered by <a href="http://www.alkalineapp.com/">Alkaline</a>. Copyright &#0169; 2010-2011 by <a href="http://www.budinltd.com/">Budin Ltd.</a> All rights reserved.
			<?php 
			
			if(!empty($alkaline)){
				if($alkaline->returnConf('maint_debug')){
					$debug = $alkaline->debug();
					echo 'Execution time: ' . round($debug['execution_time'], 3) . ' seconds. Queries: ' . $debug['queries']  . '. ';
				}
				
				echo Alkaline::returnErrors();
			}
			
			echo Orbit::promptTasks();
			
			?>
		</div>
	</div>
</body>
</html>