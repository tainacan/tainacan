<?php

// Load the admin dashboard code from core
require_once ABSPATH . 'wp-admin/includes/dashboard.php';

// Register Widgets TO Be Displayed
$this->tainacan_register_widgets();

?>

<div class="content-body">
    <h1>Tainacan</h1>
    <div class="wrap">

        <div id="dashboard-widgets-wrap">

            <?php
            // Display Widgets
            wp_dashboard();
            ?>

            <div class="clear"></div>
        </div><!-- dashboard-widgets-wrap -->

    </div><!-- wrap -->
</div>