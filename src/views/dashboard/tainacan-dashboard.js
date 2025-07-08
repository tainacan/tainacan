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
    tainacanAjaxFetchNews();
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

function tainacanAjaxFetchNews() {

    if ( tainacan_dashboard.disable_news_card )
        return;

    const newsContainer = document.getElementById('tainacan-dashboard-news');
    if ( !newsContainer || newsContainer.offsetParent === null)
        return;

    const data = new URLSearchParams();
    data.append('action', 'tainacan_fetch_dashboard_news');
    data.append('_nonce', tainacan_dashboard.nonce);

    fetch(tainacan_dashboard.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: data
    })
        .then(response => response.json())
        .then(response => {
            if (response.success)
                newsContainer.innerHTML = response.data.html;
            else
                newsContainer.innerHTML = '<p>' + response.data.message + '</p>';
        })
        .catch(error => {
            console.error('Error fetching dashboard news:', error);
            newsContainer.innerHTML = '<p>Something went wrong while loading the news.</p>';
        });
}