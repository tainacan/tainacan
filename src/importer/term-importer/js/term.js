jQuery(document).ready(function() {
	jQuery('body').on('change', '.import_term_csv_taxonomies select.select_taxonomy', function(e) {
		var new_name = jQuery('.import_term_csv_taxonomies input.new_taxonomy');
		if ( jQuery(this).val() == '' ) {
			new_name.show();
		} else {
			new_name.hide();
		}
	});
});