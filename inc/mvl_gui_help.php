<?php
function mvl_getPageHelp() {
	global $mvl_checks_config;
	global $mvlState;
	$content = mvl_getPageStart('help', 'Help', '', $mvlState->showSubscribe, $mvlState->showProfile);

	if ($mvlState->agreeTaC != true){
		$content .=
		'<div class="container">
			'. __('You have to agree to the Terms and Conditions before using this plugin.') .'
		</div>';
	}else{
		$content .= '
		<div class="container">
		<div id="tocdiv" class="tocdiv">
		<div id="toctitle"><h2>Contents</h2></div>
		<ul>
			<li><a href="#About">'. __('About',MVLTD) .'</a></li>
			<li><a href="#Legend">'. __('Risk Legend',MVLTD) .'</a></li>
			<li><a href="#Details">'. __('Details',MVLTD) .'</a></li>
			<ol>
				<li><a href="#Update_Check">'. __('Update Check',MVLTD) .'</a></li>
				<li><a href="#User_Check">'. __('User Check',MVLTD) .'</a></li>
				<li><a href="#Core_Check">'. __('Core Check',MVLTD) .'</a></li>
			</ol>
			<li><a href="#Profile">'. __('Profile',MVLTD) .'</a></li>
		</ul>
		</div>
			<a id="About"></a>
			<h4>'.__('About: ',MVLTD).'</h4><br/>
			'. __('Our goal is to help you so you never have to say, "Help! I\'ve been hacked!"',MVLTD) . ' '.
			__('Hacking is a very serious threat, but only few people think about it until it\s too late. Just like Windows computers, WordPress is a very attractive target for hackers because it is so popular. One attack can impact tens of millions of WordPress installations.',MVLTD) . ' '.
			__('Unfortunately, you can never eliminate the risk of being hacked completely - even big corporations with million-dollar budgets get hacked often. However, taking three simple steps to secure your website can decrease your risk tremendously.', MVLTD) . ' '.
			__('SECURE helps you understand the risks and supports you in locking down your WordPress installation in three simple and clear steps.',MVLTD) .
			'<br/><br/>'.
			__('Behind these three steps are two very important concepts that are responsible for raising and maintaining a secure WordPress website:',MVLTD) .'
			<br/><br/> <strong>'
				.__('1.) Keeping a secure configuration:',MVLTD).'</strong><br/>' .
				'<blockquote>'.__('Often insecure configurations can be easily exploited by attackers.',MVLTD) . ' '. __('A secure and solid configuration strengthens your website and decreases the risk of successful hack attacks against your website.',MVLTD) . ' '. __('With SECURE you can see all identified security problems of your website at one glance. Each security problem comes with a detailed description and all the information needed so you can eliminate the problems and get secure.',MVLTD) .'</blockquote>
				<strong>'.
				__('2.) Keeping your system updated:',MVLTD) .'</strong><br/>' .
				'<blockquote>'.__('One of the major reasons that websites get hacked is because they are running outdated software - either the WordPress core software itself, or some plugins or themes contain security vulnerabilities and have to be updated. There are many sites on the Internet that share information about security vulnerabilities in WordPress and its eco-system. Hackers can abuse this information and easily exploit these vulnerabilities to take over websites. ',MVLTD) .
				__('With the SECURE you can see which components are outdated and even which of them have known security vulnerabilities. ',MVLTD).
				__('Subscribed users conveniently get e-mail alerts with vulnerability details for their specific and unique WordPress website, without even having to login to the admin interface.',MVLTD) .
				'</blockquote>

			<a id="Legend"></a>&nbsp;

			<h4>'.__('Legend: ',MVLTD).'</h4>
			<strong>'.__('Risk Legend: ',MVLTD).'</strong>
			'. __('All security checks are assigned one of the following risks:',MVLTD).'<br/><br/>
					'. mvl_getIcons(0, true). ' ' .__('The check did not run successfully and <strong>no risk assessment</strong> can be given.',MVLTD).'<br/><br/>
					'. mvl_getIcons(1, true). ' ' .__('Everything is the way it should be, <strong>no violations</strong> have been recorded.',MVLTD).'<br><br/>
					'. mvl_getIcons(2, true). ' ' .__('A <strong>violation</strong> against security best practices has been identified. This issue should be resolved as soon as possible.',MVLTD).'<br><br/>
					'. mvl_getIcons(3, true). ' ' .__('A <strong>major security issue</strong> has been identified. This issue has to be resolved immediately.',MVLTD).'
			<br/><br/><strong>'.__('Action Legend: ',MVLTD).'</strong>
			'. __('All security checks have one of the following actions:',MVLTD).'<br/><br/>
					<span class="icon-stack" style="color: #5b5b5b;margin-top: -7px;text-align: center;"><i class="icon-circle icon-stack-base"></i><i class="icon-info icon-light" style="margin-top:1px;"></i></span> '.__('No risks have been identified, click on the icon to obtain more information about the specific security check.',MVLTD).'<br/><br/>
					<span class="icon-stack" style="color: #5b5b5b;margin-top: -7px;text-align: center;"><i class="icon-circle icon-stack-base"></i><i class="icon-arrow-right icon-light" style="margin-top:1px;"></i></span> '.__('A vulnerability has been identified, click on the icon to obtain information on how to resolve the issue.',MVLTD).'<br>
        <a id="Details"></a>&nbsp;
        <hr>

			<h3>'.__('Details',MVLTD).'</h3>
				'.__('On this page you will find all the details for all the checks of the three security check categories.',MVLTD).'<br />
				<a id="Update_Check"></a>&nbsp;
				<h4>'.__('1. Update Check',MVLTD).'</h4>
				<p>
				'.__('The <strong>"Update Check"</strong> analyses the status of the core WordPress installation including all installed plugins and themes.',MVLTD).'<br />
				'.__('It displays clearly if updates are available and <strong>even if security vulnerabilities are  publicly known</strong> for the installed WordPress Version, Plugins or Themes.',MVLTD).'
				</p>

				<div class="secure-notice-message">';
					if(!$mvlState->siteActive){
						$content .= '<strong>'. __('Your security information is 30 days old!',MVLTD).'</strong>';
					}else{
						$content .=	__('<strong>The free version</strong> comes with vulnerability information that is 30 days delayed from the time a vulnerability appears in our system. They also don\'t get the detailed vulnerability information exposed in the plugin, only the binary information if a vulnerability is known for the installed WordPress version, or a given Plugin or Theme.',MVLTD);
					}
					$content .= '<br/><br/>
					'. __('<strong>With the paid version </strong> you will get the vulnerability information as soon as the new issues show up in our systems.',MVLTD).'
					'.__('Additionally, you will receive real-time email alerts with detailed information about the new issue.',MVLTD).'
					'.__('This is especially useful if you are responsible for multiple sites, because then you will get the security alerts centrally delivered to your inbox, without even having to login to all the admin interfaces.',MVLTD).'
				</div>

        <p>
				<strong>'.__('Note:',MVLTD).' </strong>' .__('The SECURE plugin needs to communicate with our servers to keep information synchronized.',MVLTD) .' ' . mvl_getInfoLinkMod(MVL_COMMUNICATE,'',__('Want to know more?'),false)   .'
				</p>
				<a id="User_Check"></a>&nbsp;
				<h4>'.__('2. User Check: ',MVLTD).'</h4>
				<p>
				'.__('The <strong>"User Check"</strong> analyses potentially dangerous combinations of weak passwords and common usernames for privileged users and administrative users.',MVLTD).'<br />
				'.__('Security violations in this category typically have a very high risk of being exploited, because they are the target of many attackers.',MVLTD).'
				</p>

				<a id="Core_Check"></a>&nbsp;
				<h4>'.__('3. Core Check ',MVLTD).'</h4>
				<p>
				'.__('The <strong>"Core Check"</strong> combines multiple security checks from the following categories:',MVLTD).'<br />
				'.__('Mention the general impact of violations against this check?',MVLTD).'
				</p>

        <p>
				<h4>'.__('3.1 File Check ',MVLTD).'</h4>
				'.__('The <strong>"File Check"</strong> tests if dangerous files exist and warns the user accordingly by providing further information about a violation.',MVLTD).'<br />
				'.__('Mention the general impact of violations against this check?',MVLTD).'
				</p>

				<p>
				<h4>'.__('3.2 Permission Check ',MVLTD).'</h4>
				'.__('The <strong>"Permission Check"</strong> tests if the set file permissions for important files and directories of the WordPress installation are set according to security best practices.',MVLTD).'<br />
				'.__('Mention the general impact of violations against this check?',MVLTD).'
				</p>

				<p>
				<h4>'.__('3.3 Backend Check ',MVLTD).'</h4>
				'.__('The <strong>"Backend Check"</strong> tests if the set file permissions for important files and directories of the WordPress installation are set according to security best practices.',MVLTD).'<br />
				'.__('Mention the general impact of violations against this check?',MVLTD).'
				</p>

				<p>
				<h4>'.__('3.4 WordPress Backend Check ',MVLTD).'</h4>
				'.__('The <strong>"WordPress Backend Check"</strong> tests if the set file permissions for important files and directories of the WordPress installation are set according to security best practices.',MVLTD).'<br />
				'.__('Mention the general impact of violations against this check?',MVLTD).'
				</p>

				<p>
				<h4>'.__('3.5 Database Backend Check ',MVLTD).'</h4>
				'.__('The <strong>"Database Backend Check"</strong> tests if the set file permissions for important files and directories of the WordPress installation are set according to security best practices.',MVLTD).'<br />
				'.__('Mention the general impact of violations against this check?',MVLTD).'
        </p>

				<p>
				<h4>'.__('3.6 PHP Settings Check ',MVLTD).'</h4>
				'.__('The <strong>"PHP Settings Check"</strong> tests if the set file permissions for important files and directories of the WordPress installation are set according to security best practices.',MVLTD).'<br />
				'.__('Mention the general impact of violations against this check?',MVLTD).'
        </p>
        <a id="Profile"></a>&nbsp;
        <hr>

			<h3>'.__('Profile',MVLTD).'</h3>
				'.__('On the profile page you can get an overview of your subscription and manage your profile using the following actions:',MVLTD).'<br /><br />
				<ol>
					<li><strong>'.__('Change Password',MVLTD).'</strong></li>
					<li><strong>'.__('Delete Site',MVLTD).'</strong></li>
					<li><strong>'.__('Delete Account',MVLTD).'</strong></li>
				</ol>
				<p>'.__('The profile page is only available for registered users.',MVLTD).'</p>


		</div>';
	}
	$content .= mvl_getPageEnd();
	return($content);
}

?>
