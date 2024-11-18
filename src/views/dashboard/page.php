<?php

// Load the admin dashboard code from core
require_once ABSPATH . 'wp-admin/includes/dashboard.php';

// Register Widgets TO Be Displayed
$this->register_cards();

?>

<div id="tainacan-dashboard-app" class="content-body">
    <div class="tainacan-dashboard-header">
        <h1>
            <img 
                    alt="<?php _e('Tainacan', 'tainacan'); ?>" 
                    width="300" 
                    src="<?php echo plugin_dir_url( dirname( __FILE__, 2 ) ) . '/assets/images/tainacan_logo_dashboard.svg'; ?>" />
        </h1>
        <p>
        <?php
            $welcome_message = __('Welcome to Tainacan, your digital repository platform for WordPress.', 'tainacan');
            $welcome_message = apply_filters('tainacan-dashboard-welcome-message', $welcome_message);
            echo $welcome_message;
        ?>
        </p>
    </div>
    <div class="wrap">

        <?php do_action( 'tainacan-dashboard-before-cards' ); ?>

        <div id="dashboard-widgets-wrap">

            <?php
            // Display Widgets
            wp_dashboard();
            ?>

            <div class="clear"></div>
        </div><!-- dashboard-widgets-wrap -->

        <?php do_action( 'tainacan-dashboard-after-cards' ); ?>

    </div><!-- wrap -->
</div>