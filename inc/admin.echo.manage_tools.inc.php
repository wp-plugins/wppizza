<?php
		echo'<div id="'.$this->pluginSlug.'-settings" class="wrap wppizza-tools-wrap">';
		echo"<h2>". $this->pluginName." ".__('Tools', $this->pluginLocale)."</h2>";
		echo'<form action="options.php" method="post">';
		echo'<input type="hidden" name="'.$this->pluginSlug.'_tools" value="1">';
			settings_fields($this->pluginSlug);
			do_settings_sections('tools');
			if(!isset($_GET['tab']) || $_GET['tab']=='tools'){
			submit_button( __('Save Changes', $this->pluginLocale) );
			}
		echo'</form>';
		echo'</div>';	
?>