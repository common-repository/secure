<?php

function mvl_processSummary($p = '', $status = '') {
	$mainUrl = mvl_getMainUrl();
	global $mvlState;

	if ($p == -1 && (!isset($mvlState->agreeTaC)||$mvlState->agreeTaC != true)){
		$res = mvl_getPageSummary();
		return($res);
	}

	if ($p == 22) { // Delete Site
		if(mvl_verifyNonce(mvl_getRequestParam('_wpnonce'),'mvl_profile')){
			$userName = mvl_readOption(MVIS_LITE_OPT_NAME, 'userName');
			$authToken = mvl_readOption(MVIS_LITE_OPT_NAME, 'authToken');
			$siteDetails = mvl_readOption(MVIS_LITE_OPT_NAME, 'siteDetails');
			$siteId = $siteDetails['id'];
			$apiRes = mvl_deleteSite($code, $userName, $authToken, $siteId);
			if ($code == 200) {
				$status = 'deletedsite';
				mvl_clearData();
			} else {
				$err = mvl_getApiError('deleteSite', $code);
				$status =  __('There has been an error deleting the site.');
			}
		}else
			$status =  __('Security Check failed, please try again!',MVLTD);
	}

	if ($p == 23) { // Delete Account
		if(mvl_verifyNonce(mvl_getRequestParam('_wpnonce'),'mvl_profile')){
			$userName = mvl_readOption(MVIS_LITE_OPT_NAME, 'userName');
			$authToken = mvl_readOption(MVIS_LITE_OPT_NAME, 'authToken');
			$apiRes = mvl_deleteUser($code, $userName, $authToken);
			if ($code == 200) {
				$status = 'deletedaccount';
				mvl_clearData();
			} else {
				$err = mvl_getApiError('deleteUser', $code);
				$status =  __('There has been an error deleting your account.');
			}
		}else
			$status =  __('Security Check failed, please try again!',MVLTD);
	}

	if ($p == 41) { // rerun checks
		mvl_doAllChecks();
		mvl_initState();
	}

	if ($p == 42) { // resync manually after httpError
		$res =  mvl_get_versions($code);
	}

	if ($p == 43){
		$captchaCode = mvl_getRequestParam('captcha_code');
		$captchaId = mvl_readOption(MVIS_LITE_OPT_NAME, 'captchaId');
		$userName = mvl_getRequestParam('username');
		mvl_forgotPassword($code, $userName, $captchaCode, $captchaId);
		if($code != 200){
			$err = mvl_getApiError('resetPassword', $code);
			$status = '<div class="error-message">' . $err . '</div>';
		}else{
			$status = '<div class="success-message">' . __('The password reset e-mail has been sent, please follow its instructions.') . '</div>';
		}
	}


// read results from option and use it to display
	$mvl_checks_result = mvl_readOption(MVIS_LITE_OPT_CHECKS_RESULT, 'checks_result');
	if (!isset($mvl_checks_result['lastRun'])) {
		mvl_doAllChecks();
		mvl_initState();
		$mvl_checks_result = mvl_readOption(MVIS_LITE_OPT_CHECKS_RESULT, 'checks_result');
	}

	$updateElements = mvl_getUpdateElements(true);
	mvl_writeOption(MVIS_LITE_OPT_NAME, 'updateElements', $updateElements);
	$criticalcount = 0;
	$count = 0;
	$criticalrows = '';
	$solvedissues = 0;
	$rows = '';
	$signal = 0;
	foreach($updateElements as $updateElement) {
		$risk = $updateElement['risk'];
		if ($risk == 3){
			$criticalcount += 1;
		}elseif($risk == 2){
			$count += 1;
		}else
			$solvedissues+= 1;
	}

	$usersData = mvl_doUserChecks();
	//usort($usersData, 'mvl_risk_sort');
	foreach($usersData as $userData) {
		$state = $userData['risk'];
		if ($state == 3){
			$criticalcount += 1;
		}elseif($state == 2){;
			$count += 1;
		}else
			$solvedissues+= 1;
	}

	$coreChecks = array();
	$signal = 0;
	$actionlink = '';
	mvl_getSummaryChecks($coreChecks, $signal, $mvl_checks_result['fileChecks'], 'filecheck', __('Dangerous files were identified.',MVLTD));
	mvl_getSummaryChecks($coreChecks, $signal, $mvl_checks_result['permissionChecks'], 'permissioncheck', __('Insecure file permissions were identified.',MVLTD));
	mvl_getSummaryChecks($coreChecks, $signal, $mvl_checks_result['backendChecks'], 'backendChecks', __('Insecure web server settings were identified.',MVLTD));
	mvl_getSummaryChecks($coreChecks, $signal, $mvl_checks_result['WPbackendChecks'], 'wpbcheck', __('Insecure WordPress settings were identified.',MVLTD));
	mvl_getSummaryChecks($coreChecks, $signal, $mvl_checks_result['DBbackendChecks'], 'dbcheck', __('Insecure database settings were identified.',MVLTD));
	mvl_getSummaryChecks($coreChecks, $signal, $mvl_checks_result['phpSettingsChecks'], 'phpsettingcheck', __('Insecure PHP settings were identified.',MVLTD));

	//usort($coreChecks, "mvl_state_sort");
	foreach($coreChecks as $check) {
		$state = $check['state'];
		if ($state == 3)
			$criticalcount += 1;
		elseif($state ==2 )
			$count += 1;
		else
			$solvedissues+= 1;
	}

	$content = "";

	$res = mvl_getPageSummary($content, $status, $criticalcount, $count, $solvedissues);
	return($res);
}



function mvl_getPageSummary($content2 = '', $status = '', $criticalcount = 0, $count = 0, $solvedissues = 0) {
	global $mvlState;
	global $mvl_checks_config;
	$summary_count = 0;
	//$criticalcount = 0;
	//$count = 0;
	$summary_risk = 0;
	if($criticalcount > 0){
		$summary_risk = 3;
		$summary_count = $criticalcount;
	}elseif($count > 0){
		$summary_risk = 2;
		$summary_count = $count;
	}else{
		$summary_risk = 1;
		$summary_count = $solvedissues;
	}

	$mainUrl = mvl_getMainUrl();
	$text = '';
	//Todo: make it work with multiple error messages
	if($status == 'active')
		$text .= '<div class="success-message">' .  __('You were successfully logged in and can now enjoy the full benefits of SECURE.',MVLTD) . '</div>';
	elseif($status == 'created' || $status == 'inactive')
		$text .= '<div class="secure-notice-message">' .  __('You were successfully logged in, but are not subscribed yet. Subscribe first to get the full benefits immediately, or enter your coupon on the profile page.',MVLTD) . '</div>';
	elseif($status != '' && mvl_getRequestParam('p') == 43 )
		$text .= $status;
	elseif($status == 'deletedsite')
		$text .= '<div class="success-message">' . __('The site has been deleted successfully. You have been logged out.') . '</div>';
	elseif($status == 'deletedaccount')
		$text .= '<div class="success-message">' . $status = __('Your account has been marked for deletion, please follow the instructions you have received by mail. You have been logged out.',MVLTD) . '</div>';
	elseif($status != '')
		$text .= '<div class="error-message">' . $status . '</div>';
	elseif ($mvlState->httpError &&  $mvlState->agreeTaC && mvl_getRequestParam('p') != 42) {
		$reSyncUrl = $mainUrl . '&p=42';
		//TODO: set one variable that is set after one successful connection. so we know that the connection worked before.
		$text .= '
		<div class="error-message"><strong>' .
		__('Warning:',MVLTD) . '</strong><br/>' .
		__('The Plugin failed to communicate with the MVIS-Server. If this message remains after clicking the button below, please verify the connectivity settings on your server.',MVLTD) .'<br/>'.
		__('If this problem persists and you have never successfully synced with our site, then please check your network and firewall settings to allow the communcation with the MVIS-Server.',MVLTD) .'
		<br/><a href="'.$reSyncUrl .'"><i class="icon-arrow-right"></i>&nbsp;'.__('Try Again', MVLTD) .'</a>
		</div>';
	}elseif(defined('DISABLE_WP_CRON') &&(DISABLE_WP_CRON === true))
		$content .= mvl_showEnableCron();
	if(!isset($mvlState->agreeTaC) || $mvlState->agreeTaC != true)
		$content = mvl_getPageStart('summary', __('Legal', MVLTD), '', $mvlState->showSubscribe, $mvlState->showProfile);
	else
		$content = mvl_getPageStart('summary', __('Overview', MVLTD), '', $mvlState->showSubscribe, $mvlState->showProfile);

	if (!isset($mvlState->agreeTaC) || $mvlState->agreeTaC != true) {
		$content .= '<div class="container"><br/>';
		$content .= __('By clicking the button below you agree to the ',MVLTD) . '<a href="'. MVIS_LITE_FURTHER_INFORMATION_URL .'/TERMS_AND_CONDITIONS.pdf" target="_blank">' . __('Terms and Conditions',MVLTD) .'</a> ' .__('for SECURE.', MVLTD) . '<br/>';
		$content .= __('You also allow the plugin to communicate with our servers.',MVLTD). '&nbsp;'. mvl_getInfoLinkMod(MVL_COMMUNICATE,'',__('Find out why!',MVLTD),false);
	}else{
		$content .= $text;
		$content .= '<div class="container">';
		$content .=
		'<br/>
		<div class="clear"></div>
		<div class="securewp-bootstrap-container">
			<div class="container">
	  			<div class="row">';
	  				$content .= secure_summary_widget($criticalcount, 3);
	  				$content .= secure_summary_widget($count, 2);
	  				$content .= secure_summary_widget($solvedissues, 1);
	  				$content .='
  				</div>
  				<div class="row">
  					<br/>
  				</div>
  				<div class="row">
					<div class="col-md-12">';
	  				if ($criticalcount > 0){
	  					$content .= '<div class="panel panel-danger">
	  									<div class="panel-heading">';
	  							$content .= __("Your site contains $criticalcount high risk issues",MVLTD);
	  				}elseif ($count > 0){
	  					$content .= '<div class="panel panel-warning">
	  									<div class="panel-heading">';
  							$content .= __("Your site contains $count medium risk issues",MVLTD);
	  				}elseif ($solvedissues > 0){
	  					$content .= '<div class="panel panel-success">
	  									<div class="panel-heading">';
	  						$content .= __('Congratulations, your site is secure',MVLTD);
	  				}

	  			$content .= '
							</div>
						  	<div class="panel-body">';
				$content .= secure_summary_panel($summary_count, $summary_risk);
				$content .= '
							</div>
							<div class="panel-footer"><i>';
								if (isset($mvlState->agreeTaC) && $mvlState->agreeTaC == true) {
									$reRunUrl = $mainUrl . '&p=41';
									$content .=
										__('Last run of the checks: ', MVLTD) . mvl_htmlEncode($mvlState->lastChecksRunDT) . '.&nbsp;&nbsp;</i>'.
										'<a href="'.$reRunUrl .'"><i class="icon-arrow-right"></i>&nbsp;'.__('Rerun Checks', MVLTD) .'</a><br />';
								}
			$content .=  	'
							</div>
						</div>
	  				</div>
	  			</div>
  			</div>
    	</div>
		';

		$content .=
			$content2 .'
			<div class="clear"></div>';
	}
	if (!isset($mvlState->agreeTaC) || $mvlState->agreeTaC != true) {
			$continueUrl = $mainUrl . '&p=99';
	$content .= '<br/><br/>
		<form method="post" action="'. $continueUrl.'">
		<input type="submit" value="'. __('Continue',MVLTD) .'" />
		<br/> <br/> <div id="loadingMsg"><strong>'. __('Note:',MVLTD) . '</strong> ' . __('Clicking "Continue" will run the checks for the first time - this might take a minute...',MVLTD) .'</div>
		</form>
		</div>';
	}

	$content .= mvl_getPageEnd();
	return($content);
}


?>
