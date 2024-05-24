<?php 
    // @deprecated: use tainacan-admin-ui-options instead
    $admin_options = apply_filters('set_tainacan_admin_options', $_GET);
	$admin_options = apply_filters('tainacan-admin-ui-options', $_GET);
	$admin_options = json_encode($admin_options);
?>

<div id='tainacan-admin-app' data-module='admin' data-options='$admin_options'></div>