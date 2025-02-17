<?php

namespace Tainacan\Traits;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class for getting Admin UI options passed either via query string in
 * the URL or via the 'tainacan-admin-ui-options' filter.
 */
trait Admin_UI_Options {

	protected $admin_ui_options = [];

	/**
	 * Lists a translatable and grouped version of the available admin ui options
	 * 
	 * @return array of available admin ui options
	 */
	public function get_available_admin_ui_options() {
		return apply_filters(
			'tainacan-available-admin-ui-options',
			[
				__( 'Browsing', 'tainacan' ) => [
					'hideTainacanHeaderSearchInput' => __('Hide search field in Tainacan header', 'tainacan'),
					'hideTainacanHeaderAdvancedSearch' => __('Hide advanced search in Tainacan header', 'tainacan'),
					'hideTainacanHeaderProcessesPopup' => __('Hide processes popup in Tainacan header', 'tainacan'),
					'hideRepositorySubheaderExportButton' => __('Hide repository header', 'tainacan'),
					'hideRepositorySubheaderViewCollectionsButton' => __('Hide view collections button in repository header', 'tainacan'),
					'hideRepositorySubheaderViewCollectionButton' => __('Hide view collection button in repository header', 'tainacan'),
					'hideRepositorySubheaderExportButton' => __('Hide export button in repository header', 'tainacan'),
					'hideCollectionSubheader' => __('Hide collection header', 'tainacan'),
					'hidePrimaryMenu' => __('Hide entire side menu', 'tainacan'),
					'hidePrimaryMenuCompressButton' => __('Hide side menu compress button', 'tainacan'),
					'hidePrimaryMenuRepositoryButton' => __('Hide repository button in side menu', 'tainacan'),
					'hidePrimaryMenuCollectionsButton' => __('Hide collections button in side menu', 'tainacan'),
					'hidePrimaryMenuItemsButton' => __('Hide items button in side menu', 'tainacan'),
					'hidePrimaryMenuTaxonomiesButton' => __('Hide taxonomies button in side menu', 'tainacan'),
					'hidePrimaryMenuMetadataButton' => __('Hide metadata button in side menu', 'tainacan'),
					'hidePrimaryMenuFiltersButton' => __('Hide filters button in side menu', 'tainacan'),
					'hidePrimaryMenuImportersButton' => __('Hide importers button in side menu', 'tainacan'),
					'hidePrimaryMenuExportersButton' => __('Hide exporters button in side menu', 'tainacan'),
					'hidePrimaryMenuActivitiesButton' => __('Hide activities button in side menu', 'tainacan'),
					'hidePrimaryMenuCapabilitiesButton' => __('Hide permissions button in side menu', 'tainacan')
				],
				__( 'Dashboard', 'tainacan' ) => [
					'hideHomeRepositorySection' => __('Hide repository section', 'tainacan'),
					'hideHomeThemeCollectionsButton' => __('Hide collections button in theme', 'tainacan'),
					'hideHomeThemeItemsButton' => __('Hide items button in theme', 'tainacan'),
					'hideHomeTaxonomiesButton' => __('Hide taxonomies button', 'tainacan'),
					'hideHomeMetadataButton' => __('Hide repository level metadata button', 'tainacan'),
					'hideHomeFiltersButton' => __('Hide repository level filters button', 'tainacan'),
					'hideHomeImportersButton' => __('Hide importers button', 'tainacan'),
					'hideHomeExportersButton' => __('Hide exporters button', 'tainacan'),
					'hideHomeActivitiesButton' => __('Hide activities button', 'tainacan'),
					'hideHomeCollectionsButton' => __('Hide collections button', 'tainacan'),
					'hideHomeCollectionSettingsButton' => __('Hide settings button in collections section', 'tainacan'),
					'hideHomeCollectionMetadataButton' => __('Hide metadata button in collections section', 'tainacan'),
					'hideHomeCollectionFiltersButton' => __('Hide filters button in collections section', 'tainacan'),
					'hideHomeCollectionActivitiesButton' => __('Hide activities button in collections section', 'tainacan'),
					'hideHomeCollectionThemeCollectionButton' => __('Hide "view in theme" button in collections section', 'tainacan'),
					'showHomeCollectionCreateItemButton' => __('Show create item button in collections section', 'tainacan')
				],
				__( 'Items list', 'tainacan' ) => [
					'hideItemsListBulkActionsButton' => __('Hide bulk actions button', 'tainacan'),
					'hideItemsListMultipleSelection' => __('Hide multiple item selection', 'tainacan'),
					'hideItemsListSelection' => __('Hide individual item selection', 'tainacan'),
					'hideItemsListExposersButton' => __('Hide "View as..." button', 'tainacan'),
					'hideItemsListStatusTabs' => __('Hide status tabs', 'tainacan'),
					'hideItemsListStatusTabsTotalItems' => __('Hide total items in status tabs', 'tainacan'),
					'hideItemsListCreationDropdownBulkAdd' => __('Hide bulk add button in creation dropdown', 'tainacan'),
					'hideItemsListCreationDropdownImport' => __('Hide import button in creation dropdown', 'tainacan'),
					'hideItemsListContextMenu' => __('Hide right-click context menu', 'tainacan'),
					'hideItemsListFilterCreationButton' => __('Hide create filters button', 'tainacan')
				],
				__( 'Item editing page', 'tainacan' ) => [
					'hideItemEditionCollectionName' => __('Hide collection name', 'tainacan'),
					'hideItemEditionStatusOptions' => __('Hide status options', 'tainacan'),
					'hideItemEditionStatusPublishOption' => __('Hide public status option', 'tainacan'),
					'hideItemEditionCommentsToggle' => __('Hide comments option', 'tainacan'),
					'hideItemEditionDocument' => __('Hide document entry completely', 'tainacan'),
					'hideItemEditionDocumentFileInput' => __('Hide file type document entry', 'tainacan'),
					'hideItemEditionDocumentTextInput' => __('Hide text type document entry', 'tainacan'),
					'hideItemEditionDocumentUrlInput' => __('Hide URL type document entry', 'tainacan'),
					'hideItemEditionThumbnail' => __('Hide thumbnail', 'tainacan'),
					'allowItemEditionModalInsideModal' => __('Allow item creation modal inside another modal', 'tainacan')
				],
				__( 'Item page', 'tainacan' ) => [
					'hideItemSingleCollectionName' => __('Hide collection name', 'tainacan'),
					'hideItemSingleCurrentStatus' => __('Hide status', 'tainacan'),
					'hideItemSingleCurrentVisibility' => __('Hide visibility', 'tainacan'),
					'hideItemSingleCommentsOpen' => __('Hide comments condition', 'tainacan'),
					'hideItemSingleDocument' => __('Hide document', 'tainacan'),
					'hideItemSingleThumbnail' => __('Hide thumbnail', 'tainacan'),
					'hideItemSingleActivities' => __('Hide activities', 'tainacan'),
					'hideItemSingleExposers' => __('Hide "View as..." button', 'tainacan')
				]
			]
		);
	}

	/**
	 * 
	 * @return string option value for the given setting
	 */
	public function has_admin_ui_option($option) {

		// Get Admin Options to tweak which components will be displayed
		$this->admin_ui_options = !empty($this->admin_ui_options) ? $this->admin_ui_options : apply_filters('tainacan-admin-ui-options', $_GET);

		return isset($this->admin_ui_options[$option]) && ( $this->admin_ui_options[$option] === 'true' || $this->admin_ui_options[$option] === true );
	}
}