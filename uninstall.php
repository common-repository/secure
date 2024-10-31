<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

delete_option('secure_core');
delete_option('secure_sitealerts');
delete_option('secure_checks_config');
delete_option('secure_checks_result');
delete_option('secure_vulnstatus');
delete_option('secure_complied');