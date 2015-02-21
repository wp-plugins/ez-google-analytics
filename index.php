<?php
/*
Plugin Name: EZ Google Analytics
Plugin URI: http://wordpress.ieonly.com/category/my-plugins/google-analytics/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/
Contributors: scheeeli
Description: This plugin includes your <a target="_blank" href="http://www.google.com/analytics/web/">Google Analytics</a> tracking code on your pages and posts.
Version: 4.1.07
*/
/*            ___
 *           /  /\     EZ Google Analytics Main Plugin File
 *          /  /:/     @package EZ Google Analytics
 *         /__/::\
 Copyright \__\/\:\__  © 2014-2015 Eli Scheetz (email: wordpress@ieonly.com)
 *            \  \:\/\
 *             \__\::/ This program is free software; you can redistribute it
 *     ___     /__/:/ and/or modify it under the terms of the GNU General Public
 *    /__/\   _\__\/ License as published by the Free Software Foundation;
 *    \  \:\ /  /\  either version 2 of the License, or (at your option) any
 *  ___\  \:\  /:/ later version.
 * /  /\\  \:\/:/
  /  /:/ \  \::/ This program is distributed in the hope that it will be useful,
 /  /:/_  \__\/ but WITHOUT ANY WARRANTY; without even the implied warranty
/__/:/ /\__    of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
\  \:\/:/ /\  See the GNU General Public License for more details.
 \  \::/ /:/
  \  \:\/:/ You should have received a copy of the GNU General Public License
 * \  \::/ with this program; if not, write to the Free Software Foundation,    
 *  \__\/ Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA        */

if (isset($_SERVER["DOCUMENT_ROOT"]) && ($SCRIPT_FILE = str_replace($_SERVER["DOCUMENT_ROOT"], "", isset($_SERVER["SCRIPT_FILENAME"])?$_SERVER["SCRIPT_FILENAME"]:isset($_SERVER["SCRIPT_NAME"])?$_SERVER["SCRIPT_NAME"]:"")) && strlen($SCRIPT_FILE) > strlen("/".basename(__FILE__)) && substr(__FILE__, -1 * strlen($SCRIPT_FILE)) == substr($SCRIPT_FILE, -1 * strlen(__FILE__)))
	die('You are not allowed to call this page directly.<p>You could try starting <a href="/">here</a>.');
function ezga_install() {
	global $wp_version;
	$min_version = "2.7";
	if (version_compare($wp_version, $min_version, "<"))
		die(sprintf(__("This Plugin requires WordPress version %s or higher", 'ezga'), $min_version));
}
register_activation_hook(__FILE__, "ezga_install");
$GLOBALS["ezga_settings_array"] = get_option("ezga_settings_array", array());
function ezga_filter_tracking_id($tracking_id) {
	return preg_replace('/[^a-zA-Z0-9\-]/', "", $tracking_id);
}
function ezga_get_tracking_code($default = 0) {
	if ($default == 2)
		return '<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(["_setAccount", "%s"]);
  _gaq.push(["_trackPageview"]);
  (function() {
    var ga = document.createElement("script"); ga.type = "text/javascript"; ga.async = true;
    ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";
    var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>';
	elseif (isset($GLOBALS["ezga_settings_array"]["tracking_code"]) && strlen(trim($GLOBALS["ezga_settings_array"]["tracking_code"])) > 3 && strpos($GLOBALS["ezga_settings_array"]["tracking_code"], "%s") && !$default)
		return $GLOBALS["ezga_settings_array"]["tracking_code"];
	else
		return '<script>
  (function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,"script","//www.google-analytics.com/analytics.js","ga");
  ga("create", "%s", "auto");
  ga("send", "pageview");
</script>';
}
if (is_admin()) {
	function ezga_admin_init() {
		register_setting("ezga-settings", "ezga_settings_array");
	}
	add_action("admin_init", "ezga_admin_init");
	function ezga_admin_menu() {
		add_options_page(__("Google Analytics Settings", 'ezga'), __("Google Analytics", 'ezga'), "manage_options", "ezga-settings", "ezga_settings");
	}
	add_action("admin_menu", "ezga_admin_menu");
	function ezga_settings() {
		if (!(isset($GLOBALS["ezga_settings_array"]["tracking_id"]) && strlen(trim(ezga_filter_tracking_id($GLOBALS["ezga_settings_array"]["tracking_id"])))))
			$GLOBALS["ezga_settings_array"]["tracking_id"] = "";
		echo '<div class="wrap"><h2>'.__("Google Analytics Settings", 'ezga').'</h2><form name="settingsForm" method="post" action="options.php">';
		settings_fields("ezga-settings");
		do_settings_sections("ezga-settings");
		echo '<script type="text/javascript">
	function use_code(num) {
		var ga_source = document.getElementById("tracking_code_"+num);
		if (ga_source)
			document.getElementById("tracking_code_0").value = ga_source.value;
	}
</script>
<table class="form-table"><tr valign="top"><th scope="row">'.__("Tracking ID:", 'ezga').'</th><td><input type="text" name="ezga_settings_array[tracking_id]" placeholder="UA-0000000-00" value="'.ezga_filter_tracking_id($GLOBALS["ezga_settings_array"]["tracking_id"]).'" /><br />'.sprintf(__("Get it here: %s Google Analytics", 'ezga'), '<a target="_blank" href="http://www.google.com/analytics/web/">').' </a></td></tr>
<tr valign="top"><th scope="row">'.__("Code Location:", 'ezga').'</th><td><select name="ezga_settings_array[code_location]">';
	foreach (array("wp_head" => __("Header", 'ezga'), "wp_footer" => __("Footer", 'ezga')) as $val => $txt)
		echo "\n<option value='$val'".(isset($GLOBALS["ezga_settings_array"]["code_location"]) && $GLOBALS["ezga_settings_array"]["code_location"]==$val?" selected":"").">$txt</option>";
	echo '</select></td></tr>
<tr valign="top"><th scope="row">'.__("Tracking Code:", 'ezga').'<br /><textarea style="display: none;" id="tracking_code_1">'.htmlspecialchars(ezga_get_tracking_code(1)).'</textarea><a onclick="use_code(1);" href="#settingsForm">'.__("Use Default Code", 'ezga').'</a><br /><textarea style="display: none;" id="tracking_code_2">'.htmlspecialchars(ezga_get_tracking_code(2)).'</textarea><a onclick="use_code(2);" href="#settingsForm">'.__("Use Legacy Code", 'ezga').'</a><br /><a onclick="if (confirm(\''.__("It is not recommend to modify this code. Are you sure you want to customize the Tracking Code?", 'ezga').'\')) {alert(\''.__("Make sure to put the %s in this code where you want your Tracking ID to appear.", 'ezga').'\'); document.getElementById(\'tracking_code_0\').removeAttribute(\'readonly\');}" href="#settingsForm">Modify Code</a></th><td><textarea rows=10 style="width: 100%" id="tracking_code_0" name="ezga_settings_array[tracking_code]" readonly>'.htmlspecialchars(ezga_get_tracking_code()).'</textarea></td></tr></table>';
		submit_button();
		echo "</form></div>\n";
	}
	function ezga_set_plugin_action_links($links_array, $plugin_file) {
		if (strlen($plugin_file) > 10 && $plugin_file == substr(__file__, (-1 * strlen($plugin_file))))
			$links_array = array_merge(array('<a href="options-general.php?page=ezga-settings">'.__("Settings", 'ezga').'</a>'), $links_array);
		return $links_array;
	}
	add_filter("plugin_action_links", "ezga_set_plugin_action_links", 1, 2);
	function ezga_set_plugin_row_meta($links_array, $plugin_file) {
		if (strlen($plugin_file) > 10 && $plugin_file == substr(__file__, (-1 * strlen($plugin_file))))
			$links_array = array_merge($links_array, array('<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8VWNB5QEJ55TJ">'.__("Donate", 'ezga').'</a>', '<a target="_blank" href="http://www.google.com/analytics/web/">'.__("Google Analytics", 'ezga').'</a>'));
		return $links_array;
	}
	add_filter("plugin_row_meta", "ezga_set_plugin_row_meta", 1, 2);
} else {
	function ezga_tracking_code() {
		echo "\n<!-- Tracking Code inserted by EZ Google Analytics -->\n";
		if (isset($GLOBALS["ezga_settings_array"]["tracking_id"]) && strlen(trim(ezga_filter_tracking_id($GLOBALS["ezga_settings_array"]["tracking_id"]))) > 3)
			echo sprintf(ezga_get_tracking_code(), ezga_filter_tracking_id($GLOBALS["ezga_settings_array"]["tracking_id"]))."\n";
		else
			echo "<!-- Error: tracking_id not set! -->\n";
	}
	if (!(isset($GLOBALS["ezga_settings_array"]["code_location"]) && $GLOBALS["ezga_settings_array"]["code_location"] == "wp_footer"))
		$GLOBALS["ezga_settings_array"]["code_location"] = "wp_head";
	add_action($GLOBALS["ezga_settings_array"]["code_location"], "ezga_tracking_code");
}