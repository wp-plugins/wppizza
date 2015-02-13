<?php
		echo'<div id="'.$this->pluginSlug.'-settings" class="wrap wppizza-settings-wrap">';
		echo"<h2>". $this->pluginName." ".__('Global Settings', $this->pluginLocale)."</h2>";
		echo'<form action="options.php" method="post">';
		echo'<input type="hidden" name="'.$this->pluginSlug.'_global" value="1">';
			settings_fields($this->pluginSlug);
			
			echo"<h3>".__('General', $this->pluginLocale)."</h3>";
			do_settings_sections('global');
			
			echo"<h3>".__('Permalinks', $this->pluginLocale)."</h3>";
			do_settings_sections('permalinks');
			
			/**only make this available in multisite installs**/
			if ( is_multisite()){
				echo"<h3>".__('Multisite', $this->pluginLocale)."</h3>";
				do_settings_sections('multisite');
			}
			
			echo"<h3>".__('Miscellaneous', $this->pluginLocale)."</h3>";
			do_settings_sections('global_miscellaneous');
			
			submit_button( __('Save Changes', $this->pluginLocale) );
		echo'</form>';
		echo'</div>';
?>