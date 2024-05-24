<?php

require_once(__DIR__ . '/class-tainacan-pages.php');

require_once(__DIR__ . '/dashboard/class-tainacan-dashboard.php');
$Tainacan_Dashboard_Page = \Tainacan\Dashboard::get_instance();

require_once(__DIR__ . '/admin/class-tainacan-admin.php');
$Tainacan_Admin_Page = \Tainacan\Admin::get_instance();

require_once(__DIR__ . '/item-submission/class-tainacan-item-submission.php');
$Tainacan_Item_Submission_Page = \Tainacan\Item_Submission::get_instance();

require_once(__DIR__ . '/reports/class-tainacan-reports.php');
$Tainacan_Reports_Page = \Tainacan\Reports::get_instance();

require_once(__DIR__ . '/roles/class-tainacan-roles.php');
$Tainacan_Roles_Page = \Tainacan\Roles_Editor::get_instance();

require_once(__DIR__ . '/system-check/class-tainacan-system-check.php');
$Tainacan_System_Check_Page = \Tainacan\System_Check::get_instance();

require_once(__DIR__ . '/mobile-app/class-tainacan-mobile-app.php');
$Tainacan_Mobile_App_Page = \Tainacan\Mobile_App::get_instance();