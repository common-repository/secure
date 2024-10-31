<?php

function mvl_getAdminBaseUrl($php){
	return(network_site_url('/wp-admin/'.$php));
}

function mvl_getAbsoluteAdminUrl($p) {
	return(network_site_url('/wp-admin/admin.php') . '?page=mvl_wp.php' . '&p=' . intval($p));
}

function mvl_getBaseUrl() {
	return(plugins_url('secure/'));
}

function mvl_getMainUrl() {
	return(network_site_url('/wp-admin/admin.php') . '?page=mvl_wp.php');
}

function enqueue_mvl_scripts() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('mvl_3', mvl_getBaseUrl() . 'js/tooltip.js');
	wp_enqueue_script('mvl_5', mvl_getBaseUrl() . 'js/jquery.tablesorter.min.js');
	wp_enqueue_script('mvl_6', mvl_getBaseUrl() . 'js/jquery.validate.min.js');
	wp_enqueue_script('mvl_7', mvl_getBaseUrl() . 'js/jquery.pstrength-min.1.2.js');
	wp_enqueue_script('mvl_8', mvl_getBaseUrl() . 'js/jquery.colorbox-min.js');
	wp_enqueue_script('mvl_9', mvl_getBaseUrl() . 'js/bootstrap.min.js');
	wp_enqueue_script('mvl_99', mvl_getBaseUrl() . 'js/mvl.js');
}


function enqueue_mvl_styles() {
	wp_enqueue_style('mvl_style_1', mvl_getBaseUrl() . 'css/styles.css');
	wp_enqueue_style('mvl_style_2', mvl_getBaseUrl() . 'css/font-awesome.min.css');
	wp_enqueue_style('mvl_style_3', mvl_getBaseUrl() . 'css/colorbox.css');
	wp_enqueue_style('mvl_style_4', mvl_getBaseUrl() . 'css/bootstrap-ns.css');
}


function mvl_getPageStart($descClass = '', $descHeader = '', $descText = '', $showSubscribe=true, $showProfile=true, $showLogin=false) {
	global $mvlState;
	$siteDetails = mvl_readOption(MVIS_LITE_OPT_NAME, 'siteDetails');
	$mainUrl = mvl_getMainUrl();
	$not_subscribed = false;
	//User has no active subscriptions for this site
	if((isset($siteDetails['status']) && $siteDetails['status'] == 'INACTIVE') || !isset($siteDetails['status'])){
		$not_subscribed = true;
		$subscribeDiv = '';
	//The subscription is about to expire
	}elseif(mvl_renewSubscription()){
		$subscribeDiv = '<div class="btnsub"><a href="' . $mainUrl . '&p=10">'.__('Click Here to Renew Your Protection!',MVLTD) .'</a></div>';
	//If the site is active don't show the button
	}else {
		$subscribeDiv = '';
	}
	$userName = mvl_readOption(MVIS_LITE_OPT_NAME, 'userName');
	$authToken = mvl_readOption(MVIS_LITE_OPT_NAME, 'authToken');
	if($subscribeDiv != '')
		$loginDiv = '<div class="your-profile">';
	else
		$loginDiv = '<div class="feedback">';

	if($userName == '' && $authToken =='') {
		$loginDiv .= '<a href="#" onclick="mvl_loginbox();return(false);">'. __('Login',MVLTD) . '</a> | <a href="#" onclick="mvl_forgotPWD();return(false);">'. __('Reset Password',MVLTD) . '</a>';
		$loginDiv .= ' | ';
		$loginDiv .= '<a href="mailto:secure-wp@sec-consult.com"target="_blank">'. __('Support',MVLTD). '</a></div>';
		$showProfile = false;
	}else{
		$showProfile = true;
		$loginDiv .= '<a href="mailto:secure-wp@sec-consult.com"target="_blank">'. __('Support',MVLTD). '</a></div>';
	}
	$content = '
<div id="mvis-wrapper">
    <div id="mvis-header">
        <div id="mvis-logo">'.__('SECURE',MVLTD).'</div>
        <ul class="nav">
        	';
	if(!isset($mvlState->agreeTaC) || $mvlState->agreeTaC != true){
		$content .= '
		<li><a href="'. $mainUrl .'&p=0">' . __('Legal',MVLTD) . '</a></li>';
	}else{
		$content .= '
		<li><a href="'. $mainUrl .'&p=0">' . __('Overview',MVLTD) . '</a></li>';
		$content .= '<li><a href="'. $mainUrl .'&p=1">' . __('Details',MVLTD) . '</a></li>';
		if ($showProfile)
			$content .= '<li><a href="'. $mainUrl .'&p=20">' . __('Profile',MVLTD) . '</a></li>';
		$content .= '
		<li><a href="'. $mainUrl .'&p=6">' . __('Help',MVLTD) . '</a></li>';
	}



	$content .= '
        </ul>
    </div>';
	if($not_subscribed == true && isset($mvlState->agreeTaC) && $mvlState->agreeTaC == true)
		$content .= '<div id="mvis-header-subscribe">Your Security Information Is 30 Days Old.<a class="mvis-header-subscribe-link" href="'.SECURE_LANDER_URL.'">Get Up-To-Date Now.</a></div>';

    $content .= '<div class="mvis-wrapper-inner">
      <div class="mvis-pro"> ';
	if((mvl_getRequestParam('p') >= 10 && mvl_getRequestParam('p') < 20) || (!isset($mvlState->agreeTaC) || $mvlState->agreeTaC != true)){
		$subscribeDiv = '';
		$loginDiv = '';
	}elseif((mvl_getRequestParam('p') >= 20 && mvl_getRequestParam('p') <28) && mvl_getRequestParam('p') != 22 && $mvlState->showProfile && mvl_getRequestParam('p') != 23){
		if($subscribeDiv != '')
			$loginDiv = '<div class="your-profile">';
		else
			$loginDiv = '<div class="feedback">';
		$loginDiv .= '<a href="'. mvl_getMainUrl() . '&p=28&_wpnonce=' . wp_create_nonce('mvl_logout') .'" onclick="return confirm(\''. __('Are you sure that you want to logout?',MVLTD) .'\')">'. __('Logout',MVLTD) . '</a>';
		$loginDiv .= ' | ';
		$loginDiv .= '<a href="mailto:secure-wp@sec-consult.com"target="_blank">'. __('Support',MVLTD). '</a>';
		$loginDiv .= '</div>';
	}

	$content .= '</div>

      <div class="mvis-description">
      	<div style="float:left;width:40%">';
	$content .= '
         <h2><strong>'. $descHeader .'</strong></h2>'
          //'. $descText .'
        .'</div>'.
        $subscribeDiv .
        $loginDiv .'
      </div><div class="clear"></div>';
	return($content);
}


function mvl_getPageEnd() {
	$content = '
	  		</div>
   </div>';
	return($content);
}

function mvl_getIcons($state,$placeholder = false){
		if ($placeholder){
			switch($state) {
				case 1: return('<span class="icon-stack" style="color:#6FB824;margin-top: -7px;text-align: center;"><i class="icon-circle icon-stack-base"></i><i class="icon-ok icon-light" style="margin-left:-1px;"></i></span>'); break;
				case 2: return('<span class="icon-stack" style="color:#F8A029;margin-top: -7px;text-align: center;"><i class="icon-circle icon-stack-base"></i><i class="icon-bullhorn icon-light" style="margin-left:-1px;"></i></span>'); break;
				case 3: return('<span class="icon-stack" style="color:#EB583C;margin-top: -7px;text-align: center;"><i class="icon-circle icon-stack-base"></i><i class="icon-bolt icon-light" style="margin-left:-1px;"></i></span>'); break;
			}
		}else{
			switch($state) {
				case 1: return('<span class="secure-fa-align secure-fa-align-green"><i class="icon-ok icon-light"></i></span>'); break;
				case 2: return('<span class="secure-fa-align secure-fa-align-orange"><i class="icon-bullhorn icon-light"></i></span>'); break;
				case 3: return('<span class="secure-fa-align secure-fa-align-red"><i class="icon-bolt icon-light"></i></span>'); break;
				default: return('<span class="secure-fa-align secure-fa-align-black"><i class="icon-ban-circle icon-light"></i></span>'); break;
			}
		}
}

function secure_getCaptionIcons($state){
	switch($state) {
		case 1: return('<span class="icon-stack" style="color:#6FB824;margin-top: -3px;margin-left: -1px;margin-right: 5px;text-align: center;font-size:12px;"><i class="icon-circle icon-stack-base"></i><i class="icon-ok icon-light" style="margin-top:1px;font-size:12px;"></i></span>'); break;
		case 2: return('<span class="icon-stack" style="color:#F8A029;margin-top: -3px;margin-left: -1px;margin-right: 5px;text-align: center;font-size:12px;"><i class="icon-circle icon-stack-base"></i><i class="icon-bullhorn icon-light" style="margin-top:1px;font-size:12px;"></i></span>'); break;
		case 3: return('<span class="icon-stack" style="color:#EB583C;margin-top: -3px;margin-left: -1px;margin-right: 5px;text-align: center;font-size:12px;"><i class="icon-circle icon-stack-base"></i><i class="icon-bolt icon-light" style="margin-top:1px;font-size:12px;"></i></span>'); break;
		default: return('<span class="icon-stack" style="color:#5b5b5b;margin-top: -3px;margin-left: -1px;margin-right: 5px;text-align: center;font-size:12px;"><i class="icon-circle icon-stack-base"></i><i class="icon-ban-circle icon-light" style="margin-top:1px;font-size:12px;"></i></span>'); break;
	}
}

function mvl_showEnableCron(){
		return '<div class="secure-notice-message">' .'<strong>'. __('Attention: WP_CRON is disabled!',MVLTD).'</strong><br/>'.
				__('Without WP_CRON your sitedetails are not sychronized automatically with our servers, which may result in you receiving outdated information.',MVLTD) .
				'<br/>'.
				__('Enable WP_CRON by removing the line "define(\'DISABLE_WP_CRON\', true)" in the wp-config.php file.',MVLTD) .
				'</div>';
}

// Modal Info Box
function mvl_getInfoLinkMod($id,$aid='',$text = '',$tooltip = true, $tooltext = '') {
	if($tooltip && $tooltext == '')
		$res = '<a class="iframex mvltooltip" title="'.__('No risk was identified.<br/>Click to learn more about this check.',MVLTD).'" href="' . trailingslashit(MVIS_LITE_FURTHER_INFORMATION_URL) . $id;
	elseif($tooltip && $tooltext != '')
		$res = '<a class="iframex mvltooltip" title="'.$tooltext.'" href="' . trailingslashit(MVIS_LITE_FURTHER_INFORMATION_URL) . $id;
	else
		$res = '<a class="iframex" href="' . trailingslashit(MVIS_LITE_FURTHER_INFORMATION_URL) . $id;

	if ($aid == '')
		$res .= '.html"';
	else
		$res .= '.html#' . $aid . '"';

	if($text != '')
		$res .= '>'.$text.'</span></a>';
	else
		$res .= '><span class="secure-fa-align secure-fa-align-black"><i class="icon-info icon-light"></i></span></a>';

	return($res);
}

function mvl_getFurtherInfo($id, $name, $vulnerable = ''){
	$response = '<a href="#" class="mvltooltip" title="'. __('An issue has been identified.<br/> Click to learn more about this threat.',MVLTD) .'" onclick="mvl_infobox(' . "'" . $id ."'" . ",'" . $name. "'";
	if($vulnerable != '')
		$response .= ",'" . $vulnerable. "'";
	$response .= ');return(false);"><span class="secure-fa-align secure-fa-align-black"><i class="icon-arrow-right icon-light"></i></span></a>';

	return($response);
}

function mvl_directUpgrade($name, $type, $text = ''){
	$response = '<a href="#" class="mvltooltip" title="'. __('Click to upgrade to the newest version.',MVLTD) .'" onclick="mvl_upgradebox(' . "'" . $name ."','" . $type ."'";
	$response .= ');return(false);">';
	//if ($response != '')
	$response .= $text . '</a>';
	//else
		//$response .= '<span class="action-arrow"></span></a>'; //TODO: change this to the other button
	return($response);
}


function mvl_getInfoLink3($url) {
	$res = '<a href="' . mvl_htmlEncode($url) . '" class="iframex"><span class="action-arrow"></span></a>'; //TODO: change this to the other button
	return($res);
}

function mvl_getDownloadLink($url) {
	$res = '<a href="' . mvl_htmlEncode($url) . '">'. __('here',MVLTD).'</a>';
	return($res);
}

function mvl_getInfoLink($url) {
	$res = '<a href="' . mvl_htmlEncode($url). '">'. __('here',MVLTD).'</a>';
	return($res);
}

function mvl_state_sort($a, $b) {
	return($a['state'] < $b['state']);
}

function mvl_risk_sort($a, $b) {
	return($a['risk'] < $b['risk']);
}

function mvl_getRequestParam($name, $default = false) {
	if (isset($_REQUEST[$name])) {
		return($_REQUEST[$name]);
	} else {
		return($default);
	}
}

function mvl_getSummaryChecks(&$coreChecks, &$signal, $check_array,$type, $description){
	foreach($check_array as $check) {
		//$actionlink = '<a href="#" onclick="mvl_infobox(\''.mvl_htmlEncode($check['id']).'\',\''.$type.'\');return(false);"><span class="action-arrow"></span></a>'; //TODO: change this to the other button
		$coreCheck = array();
		//$coreCheck['type'] = '<a href="#" class="mvltooltip" title="'.$tooltip .'">'. mvl_htmlEncode($type) .'</a>';
		$coreCheck['type'] = mvl_htmlEncode($description);
		if (isset($check['name'])){
			$name = $check['name'];
		}elseif(isset($check['fileName'])){
			$name = $check['fileName'];
		}elseif(isset($check['type'])){
			$name = $check['type'];
		}
		$coreCheck['name'] = '<a href="#" class="mvltooltip" title="'.$check['message'] .'">' .mvl_htmlEncode($name) .'</a>';
		if ($check['state'] > $signal) $signal = $check['state'];
		$coreCheck['state'] = $check['state'];
		//$coreCheck['actionlink'] = $actionlink;
		$coreChecks[] = $coreCheck;
	}
}

function secure_summary_widget($count, $risk){

	$content = '
	<div class="col-xs-4 col-sm-4 col-md-4">
		<div class="secure_widget_left ';
		if ($risk == 3)
			$content .= 'secure_red">';
		elseif($risk == 2)
			$content .= 'secure_orange">';
		else
			$content .= 'secure_green">';
	$content .= '
			<div class="secure_widget_content">
				<div class="secure_icon">';
		if ($risk == 3)
			$content .= '
					<i class="icon-bolt secure_widget_icon"></i>
					High Risk Issues
				</div>
				<div class="secure_widget_value">
					'.$count;
		elseif($risk == 2)
			$content .= '
					<i class="icon-bullhorn secure_widget_icon"></i>
					Medium Risk Issues
				</div>
				<div class="secure_widget_value">
					'.$count;
		else
			$content .= '
					<i class="icon-ok secure_widget_icon"></i>
					Solved Issues
				</div>
				<div class="secure_widget_value">
					'.$count;
	$content .= '
				</div>
			</div>
				<a href="'. mvl_getMainUrl(). '&p=3#tabs-1" class="secure_widget_more"><i class="icon-search secure_widget_more_icon"></i></a>
			</div>
		</div>';

	return $content;
}

function secure_summary_panel($count, $risk){
	$content = __('<b>Call to Action:</b><br/>',MVLTD);
	if($risk == 3){
		$content .= __('Right now your site is at great risk, start securing it by taking action on all the '. mvl_getIcons(3, true) .' icons on the <a href="'. mvl_getMainUrl() . '&p=3#tabs-1">details page</a> and make sure that these issues are resolved immediately.', MVLTD);
	}

	elseif($risk == 2){
		$content .= __('Your site violates WordPress security best practices, improve the security of your site by taking action on the '. mvl_getIcons(2, true) .' icons on the <a href="'. mvl_getMainUrl() . '&p=3#tabs-1">details page</a>.', MVLTD);
	}

	elseif($risk == 1){
		if(!secure_is_subscribed()){
			$content .= __("Honestly, there is nothing to do here anymore.",MVLTD);
		}else{
			$content .= __("Honestly, there is nothing to do here anymore and you will receive e-mail alerts of new security threats.",MVLTD);
		}
	}

	$content .= __("<br/><br/><b>Icon Classification:</b><br/><br/>".
					mvl_getIcons(3, true) . " The red bold icons represent high risk security holes that have to be addressed right away.<br/><br/>".
					mvl_getIcons(2, true) . " The orange bullhorn icon represents medium risk security issues that violate security best practices.<br/><br/>".
					mvl_getIcons(1, true) . " The green check icon represents a successfully resolved security issue."
			,MVLTD);

	if(!secure_is_subscribed()){
		$content .= "<br/><br/><b>". __('Warning:',MVLTD) ."</b><br/>";
		$content .= __('Your security information is outdated. Get up-to-date and peace of mind by clicking the link on the top of the page.');
	}

	$content .= "<br/><br/>";

	return $content;
}

function secure_is_subscribed(){
	$subscribed = true;
	if((isset($siteDetails['status']) && $siteDetails['status'] == 'INACTIVE') || !isset($siteDetails['status']))
		$subscribed = false;

	return $subscribed;
}


?>
