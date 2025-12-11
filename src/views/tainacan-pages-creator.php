<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once(__DIR__ . '/class-tainacan-pages.php');

require_once(__DIR__ . '/dashboard/class-tainacan-dashboard.php');
$Tainacan_Dashboard_Page = \Tainacan\Dashboard::get_instance();

require_once(__DIR__ . '/admin/class-tainacan-admin.php');
$Tainacan_Admin_Page = \Tainacan\Admin::get_instance();

require_once(__DIR__ . '/settings/class-tainacan-settings.php');
$Tainacan_Settings_Page = \Tainacan\Settings::get_instance();

require_once(__DIR__ . '/roles/class-tainacan-roles.php');
$Tainacan_Roles_Page = \Tainacan\Roles_Editor::get_instance();

require_once(__DIR__ . '/system-check/class-tainacan-system-check.php');
$Tainacan_System_Check_Page = \Tainacan\System_Check::get_instance();

require_once(__DIR__ . '/mobile-app/class-tainacan-mobile-app.php');
$Tainacan_Mobile_App_Page = \Tainacan\Mobile_App::get_instance();

require_once(__DIR__ . '/class-tainacan-admin-commands.php');
$Tainacan_Admin_Commands_Page = \Tainacan\Admin_Commands::get_instance();

require_once(__DIR__ . '/class-tainacan-admin-bar-items.php');
$Tainacan_Admin_Bar_Items_Page = \Tainacan\Admin_Bar_Items::get_instance();