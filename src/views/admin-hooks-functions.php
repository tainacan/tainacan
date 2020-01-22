<?php

/**
 * @see \Tainacan\Admin_Hooks->register()
 */
function tainacan_register_admin_hook( $context, $callback, $position = 'end-left' ) {
    $admin_hooks = \Tainacan\Admin_Hooks::get_instance();
    return $admin_hooks->register( $context, $callback, $position );
}