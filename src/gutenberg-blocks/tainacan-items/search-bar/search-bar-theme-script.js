jQuery( document ).ready(function() {
    jQuery('#taincan-search-bar-block').submit(function(e) {
        e.preventDefault();
        var val = jQuery('#taincan-search-bar-block_input').val();
        if (val) {
            window.location.href = e.target.action + '?search=' + val;
        }
        return;
    });
}); 