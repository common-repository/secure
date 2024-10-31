<?php
/*
Plugin Name: SECURE
Plugin URI: http://wordpress.org/extend/plugins/secure/
Author: SEC Consult
Author URI: https://www.sec-consult.com/en
Version: 1.0
Description: SECURE shows you how to lock down your installation and sends real-time e-mail alerts to premium users.
License: GPLv2 or later
*/

/* Stefan Streichsbier (email : s.streichsbier@sec-consult.com)

 This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

require_once('inc/mvl_config.php');
require_once('inc/mvl_wp_lib.php');
require_once('inc/mvl_core_lib.php');
require_once('inc/mvl_wp_checks.php');
require_once('inc/mvl_api.php');
require_once('inc/mvl_ajax.php');
require_once('inc/mvl_gui_lib.php');
require_once('inc/mvl_gui_steps.php');
require_once('inc/mvl_gui_profile.php');
require_once('inc/mvl_gui_summary.php');
require_once('inc/mvl_gui_help.php');
require_once('inc/mvl_gui_ajax.php');


class c_mvlState {
}

$mvlState = new c_mvlState();

function mvl_initState() {
	global $mvlState;
	$mvlState->userRegistered = ((mvl_readOption(MVIS_LITE_OPT_NAME, 'userName') <> '') && (mvl_readOption(MVIS_LITE_OPT_NAME, 'authToken') <> ''));
	$siteDetails = mvl_readOption(MVIS_LITE_OPT_NAME, 'siteDetails');
	$mvlState->siteActive = ((isset($siteDetails['status'])) && ($siteDetails['status'] == 'ACTIVE'));
	$mvlState->newActivation = mvl_readOption(MVIS_LITE_OPT_NAME, 'newActivation');

	$mvlState->showSubscribe = !$mvlState->siteActive;
	$mvlState->showProfile = $mvlState->userRegistered;

	if(mvl_readOption(MVIS_LITE_OPT_NAME,'newActivation') === false){
		$mvlState->agreeTaC = true;
	}

	if(isset($_REQUEST['p']) && intval($_REQUEST['p']) == 99 && mvl_readOption(MVIS_LITE_OPT_NAME,'newActivation') === true){
		mvl_writeOption(MVIS_LITE_OPT_NAME, 'newActivation', false);
		$mvlState->agreeTaC = true;
		$res = mvl_sync($message);
		//Todo: show proper error message in div
		if(!$res)
			echo "The following error has occured: " . $message;
	}

	$mvl_checks_result = mvl_readOption(MVIS_LITE_OPT_CHECKS_RESULT, 'checks_result');
	if (isset($mvl_checks_result['lastRun'])) {
		$mvlState->lastChecksRun = $mvl_checks_result['lastRun'];
		$mvlState->lastChecksRunDT = date('D, d. F Y \a\t H:i:s', $mvl_checks_result['lastRun']);
	} else {
		$mvlState->lastChecksRun = 0;
		$mvlState->lastChecksRunDT = 'NEVER';
	}

	$mvlState->lastSync = mvl_readOption(MVIS_LITE_OPT_NAME, 'lastSync', 0);
	if ($mvlState->lastSync == 0) {
		$mvlState->lastSyncDT = 'NEVER';
	} else {
		$mvlState->lastSyncDT = date('Y/m/d - H:i', $mvlState->lastSync);
	}
	$mvlState->httpError = mvl_readOption(MVIS_LITE_OPT_NAME, 'httpError', false);

}

function mvl_sync(&$message) {
	global $mvlState;
	$message = '';
	$succeeded = true;
	if (!isset($mvlState->agreeTaC) || $mvlState->agreeTaC != true) {
		$message = 'agreeTaC = false';
		return(false);
	}

	$siteDetails = mvl_readOption(MVIS_LITE_OPT_NAME, 'siteDetails');
	$siteActive = ((isset($siteDetails['status'])) && ($siteDetails['status'] == 'ACTIVE'));

	$code = 0;
	$apiRes = mvl_get_versions($code);
	if ($code <> 200) {
		$err = mvl_getApiError('get_versions', $code);
		$message = "ERROR getting versions, status: $err; details: " . print_r($apiRes, true);
		$succeeded = false;
	}else{
		$message .= "getVersions OK\r\n";
		$versions = $apiRes;
		mvl_writeOption(MVIS_LITE_OPT_NAME, 'versions', $versions);
	}
	$check_version = mvl_readOption(MVIS_LITE_OPT_NAME, 'checks_version', 0);
	if (intval($versions['SecCheckConfig']) > intval($check_version)) {
		$message .= "loading new SecCheckConfig\r\n";
		$apiRes = mvl_get_seccheckconfig($code);
		if ($code <> 200) {
			$err = mvl_getApiError('', $code);
			$message .= "ERROR getting secCheckConfig, status: $err; details: " . print_r($apiRes, true);
			$succeeded = false;
		}else{
			$message .= "getSecCheckConfig OK\r\n";
			mvl_writeOption(MVIS_LITE_OPT_CHECKS_CONFIG, 'checks_config', $apiRes);
			mvl_writeOption(MVIS_LITE_OPT_NAME, 'checks_version', $versions['SecCheckConfig']);
			mvl_deleteOption(MVIS_LITE_OPT_CHECKS_RESULT, 'checks_result');
		}
	}

	$vulnstatus_version = mvl_readOption(MVIS_LITE_OPT_NAME, 'vulnstatus_version', 0);
		$message .= "loading new VulnStatus\r\n";
		$apiRes = mvl_get_vulnstatus($code);
		if ($code <> 200) {
			$err = mvl_getApiError('', $code);
			$message .= "ERROR getting VulnerabilityStatus, status: $err; details: " . print_r($apiRes, true);
			$succeeded = false;
		}else{
			$message .= "getVulnStatus OK\r\n";
			mvl_writeOption(MVIS_LITE_OPT_VULNSTATUS, 'vuln_status', $apiRes);
			mvl_writeOption(MVIS_LITE_OPT_NAME, 'vulnstatus_version', $versions['VulnStatus']);
		}

	if ($siteActive) {
		$oldSiteDetailsHash = mvl_readOption(MVIS_LITE_OPT_NAME, 'sitedetails_hash', '');
		$thisSiteDetails = mvl_getThisSiteDetails(true);
		$newSiteDetailsHash = sha1($thisSiteDetails);
		if ($oldSiteDetailsHash <> $newSiteDetailsHash) {
			$message .= "updating site\r\n";
			$userName = mvl_readOption(MVIS_LITE_OPT_NAME, 'userName');
			$authToken = mvl_readOption(MVIS_LITE_OPT_NAME, 'authToken');
			$siteDetails = mvl_readOption(MVIS_LITE_OPT_NAME, 'siteDetails');
			$apiRes = mvl_updateSite($code, $userName, $authToken, $siteDetails['id'], $thisSiteDetails);
			if ($code <> 200) {
				$err = mvl_getApiError('updateSite', $code);
				$message .= "ERROR updating Site, $err; details: " . print_r($apiRes, true);
				$succeeded = false;
			}else{
				$message .= "updateSite OK\r\n";
				mvl_writeOption(MVIS_LITE_OPT_NAME, 'sitedetails_hash', $newSiteDetailsHash);
			}
		}

		//Loading sitealerts
		$message .= "loading siteAlerts\r\n";
		$userName = mvl_readOption(MVIS_LITE_OPT_NAME, 'userName');
		$authToken = mvl_readOption(MVIS_LITE_OPT_NAME, 'authToken');
		$siteDetails = mvl_readOption(MVIS_LITE_OPT_NAME, 'siteDetails');
		$apiRes = mvl_getSiteAlerts($code, $userName, $authToken, $siteDetails['id']);
		if ($code <> 200) {
			$err = mvl_getApiError('getSiteAlerts', $code);
			$message .= "ERROR getting Site Alerts, $err; details: " . print_r($apiRes, true);
			$succeeded = false;
		}else{
			$message .= "getSiteAlerts OK\r\n";
			mvl_writeOption(MVIS_LITE_OPT_SITEALERTS, 'site_alerts', $apiRes);
		}
	}

	return($succeeded);
}

function mvl_manualSync() {
	global $mvlState;
	$lastSync = mvl_readOption(MVIS_LITE_OPT_NAME, 'lastSync', 0);
	$now = time();
	$message = '';
	if (intval($now) < (intval($lastSync) + intval(MVL_SYNC_INTERVAL))) {
		return;
	}


	$res = mvl_sync($message);
	if (!$res) {
		//TODO: Log errors in an object and inform user
		$err = $message;
	}
	mvl_writeOption(MVIS_LITE_OPT_NAME, 'lastSync', $now);
	$mvlState->lastSyncDT = date('Y/m/d - H:i', $now);
	mvl_initState();
}


function mvl_Main() {
	global $mvlState;
	mvl_initState();
	mvl_initChecksConfig();

	//Schedule WP_CRON job
	if(!wp_next_scheduled('sync_daily'))
		wp_schedule_event(time(), 'daily', 'sync_daily');

	isset($_REQUEST['p']) ? $p = intval($_REQUEST['p']) : $p = 0;
	if (defined('MVL_TEST') && ($p >= 800)) {
		$page = mvl_processTest($p);
	} else {
		if (!isset($mvlState->agreeTaC) || $mvlState->agreeTaC != true) {
			if ($p <> 6) {
				$p = -1;
			}
		}
		//To catch the scenario that the user has not clicked on the "return to plugin" button in the iframe after the successful subscription
		if(!$mvlState->siteActive && $p != 15){
			$userName = mvl_readOption(MVIS_LITE_OPT_NAME, 'userName');
			$authToken = mvl_readOption(MVIS_LITE_OPT_NAME, 'authToken');
			$siteDetails = mvl_readOption(MVIS_LITE_OPT_NAME, 'siteDetails');

			if( $userName != '' && $authToken != '' && is_array($siteDetails) && isset($siteDetails['id'])){
				$apiRes = mvl_getSiteDetails($code, $userName, $authToken, $siteDetails['id']);
				if ($code == 200)
					mvl_updateSiteDetailsAndSync($apiRes);
			}
		}

		switch ($p) {
			case 1:
			case 2:
			case 3:
			case 4:
				$page = mvl_getPageSteps($p);
				break;
			case 6:
				$page = mvl_getPageHelp();
				break;
			case 7:
				mvl_doUserChecks(1);
				$page = mvl_getPageSteps(3);
				break;
			case 8:
				mvl_doCoreChecks(1);
				$page = mvl_getPageSteps(3);
				break;
			case 9:
				mvl_doUpdateChecks();
				$page = mvl_getPageSteps(3);
				break;

// Profile Page
			case 20: // Profile Page
			case 21: // Change Password
			case 24: // Sync Site
			case 25: // Resend Verification
			case 26: // Toggle Summary E-Mails
			case 27: // Activate Site with coupon
			case 31: // Success with coupon
			case 32: // Error with coupon
				$page = mvl_processProfile($p);
				break;

			case 22: // Delete Site
			case 23: // Delete Account
				$page = mvl_processSummary($p);
				break;

			case 28: // Logout
				$err = mvl_Logout(mvl_getRequestParam('_wpnonce'));
				$page = mvl_processSummary(0,$err);
				break;

			case 29: // Login
				$err = mvl_Login(mvl_getRequestParam('email'),mvl_getRequestParam('password'),mvl_getRequestParam('coupon'));
				$page = mvl_processSummary(0,$err);
				break;


// Overview Page
			case 41: // rerun Checks
			case 42: // resync manually after httpError
			case 43: // reset Password
				$page = mvl_processSummary($p);
				break;

			default:
				$page = mvl_processSummary($p);
		}
	}
	echo($page);
}


function mvl_Menu() {
	if(is_multisite()){
		if(is_super_admin()){
			$plugin_page = add_menu_page('SECURE', 'SECURE', 'manage_options', 'mvl_wp.php', 'mvl_Main', plugins_url('secure/images/secure-icon-16x16.png'));
		}
	}elseif(current_user_can('update_plugins')){
		$plugin_page = add_menu_page('SECURE', 'SECURE', 'manage_options', 'mvl_wp.php', 'mvl_Main', plugins_url('secure/images/secure-icon-16x16.png'));
	}
}

function mvl_plugin_init() {
  load_plugin_textdomain(MVLTD, false, dirname(plugin_basename( __FILE__ )) . '/languages/');
}

function mvl_plugin_activate(){
	mvl_writeOption('secure_core', 'newActivation', true);
}

function mvl_plugin_deactivate() {
	if(wp_next_scheduled( 'sync_daily' ))
		wp_clear_scheduled_hook('sync_daily');
}

function mvl_sync_daily(){
	mvl_manualSync();
}

function secure_plugin_curl_ssl_v3( $handle ) {
	curl_setopt($handle, CURLOPT_SSLVERSION, 3);
}

add_action( 'http_api_curl', 'secure_plugin_curl_ssl_v3' );
register_activation_hook( __FILE__, 'mvl_plugin_activate' );
register_deactivation_hook(__FILE__, 'mvl_plugin_deactivate');
add_action('admin_menu', 'mvl_Menu');
add_action('admin_print_scripts', 'enqueue_mvl_scripts' );
add_action('admin_print_styles', 'enqueue_mvl_styles' );
add_action('plugins_loaded', 'mvl_plugin_init');
add_action('sync_daily', 'mvl_sync_daily');
?>
