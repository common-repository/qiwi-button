<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

delete_option('qiwibutton_shop_id');
delete_option('qiwibutton_lifetime');
delete_option('qiwibutton_check_agt');
delete_option('qiwibutton_comment');
