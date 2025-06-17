// Checks if document is loaded
const performWhenDocumentIsLoaded = callback => {
    if (/comp|inter|loaded/.test(document.readyState))
        callback();
    else
        document.addEventListener('DOMContentLoaded', callback, false);
}

// Initialize the dashboard settings
performWhenDocumentIsLoaded(() => {
    tainacanSetupDashboardSettings();
});

function tainacanSetupDashboardSettings() {
    if ( tainacan_dashboard.disable_cards_sorting ) {
        document.querySelectorAll('#tainacan-dashboard-app .postbox').forEach(postbox => postbox.classList.add('not-sortable') );
        jQuery(function($) {
            $(".meta-box-sortables").sortable({
                cancel:".not-sortable",
            });
        });
    }
}