<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

?>
<div id="tainacan-dashboard-app" class="content-body">
    <div class="tainacan-dashboard-header">
        <h1>
            <?php

                /**
                 * Tweaks the dashboard logo to use white, monochrome version
                 * 
                 * @param boolean $dashboard_logo_use_white The boolean to indicate if the white logo should be used
                 * 
                 * @return boolean The boolean to indicate if the white logo should be used
                 */
                $dashboard_logo_use_white = apply_filters('tainacan-dashboard-logo-use-white', false);

                /**
                 * Filter the dashboard logo
                 * 
                 * @param string $dashboard_logo The dashboard logo
                 * 
                 * @return string The dashboard logo
                 */
                $dashboard_logo = apply_filters(
                    'tainacan-dashboard-logo',
                    plugin_dir_url( dirname( __FILE__, 2 ) ) . '/assets/images/' . ($dashboard_logo_use_white ? 'tainacan_logo_dashboard_white.svg' : 'tainacan_logo_dashboard.svg')
                );

            ?>
            <img 
                    alt="<?php _e('Tainacan', 'tainacan'); ?>" 
                    width="300" 
                    src="<?php echo esc_attr( $dashboard_logo ); ?>" />
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

    <span class="plugin-version">
        <?php echo sprintf(
            __( 'Version %s' , 'tainacan' ),
            TAINACAN_VERSION
        ); ?>
    </span>
</div>