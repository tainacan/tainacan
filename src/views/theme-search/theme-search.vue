<template>
    <theme-items-page
            class="theme-items-list"
            :collection-id="$root.collectionId"
            :term-id="$root.termId ? $root.termId : null" 
            :taxonomy="$root.taxonomy ? $root.taxonomy : null"
            :enabled-view-modes="$root.enabledViewModes" 
            :default-view-mode="$root.defaultViewMode"
            :is-forced-view-mode="$root.isForcedViewMode"
            :default-items-per-page="$root.defaultItemsPerPage"
            :hide-filters="$root.hideFilters ? $root.hideFilters : false"
            :hide-hide-filters-button="$root.hideHideFiltersButton ? $root.hideHideFiltersButton : false"
            :hide-search="$root.hideSearch ? $root.hideSearch : false"
            :hide-advanced-search="$root.hideAdvancedSearch ? $root.hideAdvancedSearch : false"
            :hide-displayed-metadata-button="$root.hideDisplayedMetadataButton ? $root.hideDisplayedMetadataButton : false"
            :hide-sorting-area="$root.hideSortingArea ? $root.hideSortingArea : false"
            :hide-sort-by-button="$root.hideSortByButton ? $root.hideSortByButton : false"
            :hide-exposers-button="$root.hideExposersButton ? $root.hideExposersButton : false"
            :hide-items-per-page-button="$root.hideItemsPerPageButton ? $root.hideItemsPerPageButton : false"
            :hide-go-to-page-button="$root.hideGoToPageButton ? $root.hideGoToPageButton : false"
            :hide-pagination-area="$root.hidePaginationArea ? $root.hidePaginationArea : false"
            :show-filters-button-inside-search-control="$root.showFiltersButtonInsideSearchControl ? $root.showFiltersButtonInsideSearchControl : false"
            :start-with-filters-hidden="$root.startWithFiltersHidden ? $root.startWithFiltersHidden : false"
            :filters-as-modal="$root.filtersAsModal ? $root.filtersAsModal : false"
            :show-inline-view-mode-options="$root.showInlineViewModeOptions ? $root.showInlineViewModeOptions : false"
            :show-fullscreen-with-view-modes="$root.showFullscreenWithViewModes ? $root.showFullscreenWithViewModes : false"
        />
</template>

<script>
export default {
    name: "ThemeSearch",
    created() {
        this.$userPrefs.init();
    }
}
</script>

<style lang="scss">

    // TAINACAN Variables
    @import "../admin/scss/_variables.scss";

    // Bulma imports
    @import "./scss/theme-basics.sass";

    // Buefy imports
    @import "../../../node_modules/buefy/src/scss/utils/_all.scss";
    @import "../../../node_modules/buefy/src/scss/components/_datepicker.scss";
    @import "../../../node_modules/buefy/src/scss/components/_checkbox.scss";
    @import "../../../node_modules/buefy/src/scss/components/_radio.scss";
    @import "../../../node_modules/buefy/src/scss/components/_tag.scss";
    @import "../../../node_modules/buefy/src/scss/components/_loading.scss";
    @import "../../../node_modules/buefy/src/scss/components/_dropdown.scss";
    @import "../../../node_modules/buefy/src/scss/components/_modal.scss";
    @import "../../../node_modules/buefy/src/scss/components/_dialog.scss";
    @import "../../../node_modules/buefy/src/scss/components/_notices.scss";
    @import "../../../node_modules/buefy/src/scss/components/_numberinput.scss";

    // Tainacan imports
    @import "../admin/scss/_tables.scss";
    @import "../admin/scss/_modals.scss";
    @import "../admin/scss/_buttons.scss"; 
    @import "../admin/scss/_inputs.scss";
    @import "../admin/scss/_checkboxes.scss";
    @import "../admin/scss/_pagination.scss";
    @import "../admin/scss/_tags.scss";
    @import "../admin/scss/_notices.scss";
    @import "../admin/scss/_tabs.scss";
    @import "../admin/scss/_selects.scss";
    @import "../admin/scss/_dropdown-and-autocomplete.scss";
    @import "../admin/scss/_tooltips.scss";
    @import "../admin/scss/_control.scss";
    @import "../admin/scss/_tainacan-form.scss";
    @import "../admin/scss/_filters-menu-modal.scss";
    @import "./scss/_layout.scss";
    @import "../admin/scss/_custom_variables.scss";

    .theme-items-list {
        background: var(--tainacan-background-color, inherit);
        font-size: var(--tainacan-base-font-size, inherit);
        font-family: var(--tainacan-font-family, inherit);
        position: relative;
        -webkit-overflow-scrolling: touch;

        * {
            // For Firefox
            scrollbar-color: var(--tainacan-gray3) transparent;
            scrollbar-width: thin;

            // For Chromium and related
            &::-webkit-scrollbar {
                width: 0.55em;
                opacity: 0.8;
            }
            &::-webkit-scrollbar-thumb {
                background-color: var(--tainacan-gray3);
            }
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0;
        }
        
        .tainacan-icon {
            opacity: 0; // Will make it 1 once window.load is done; 
        }

        a, a:not([href]) { 
            color: var(--tainacan-secondary);
        }
        a:hover, 
        a:hover:not([href]) {
            cursor: pointer;
            color: var(--tainacan-secondary);
            text-decoration: underline;
        }
        ul {
            list-style: none;
        }

        /* WordPress customize shortcut edit buttons are not appearing properly */
        .customize-partial-edit-shortcut button,
        .widget .customize-partial-edit-shortcut button {
            opacity: 1;
            animation: none;
            left: 30px;
            top: 2px;
            pointer-events: auto;
        }

        // We need this because bootstrap messes up with this class
        .dropdown-menu {
            display: block;
        }
        .dropdown.is-inline .dropdown-content {
            display: flex;
            border: none;

            .dropdown-item {
                padding: 0.125em 0.5em !important;

                .gray-icon {
                    padding: 0;
                }
            }
        }

        .date-filter-container,
        .numeric-filter-container {
            @media screen and (min-width: 1366px) {
                flex-wrap: nowrap !important;
                height: auto !important;
            }
        }

        .column-large-width {
            .tainacan-compound-group {
                display: inline-block;
                font-size: 1.125em;
                margin-top: -0.25em;
                text-overflow: ellipsis;
                white-space: nowrap;
                overflow: hidden;
                max-width: 100%;

                & * {
                    display: inline-block;
                }
                .label {
                    font-size: 1em;
                    color: var(--tainacan-info-color);
                    &:not(:first-child)::before {
                        content: ', ';
                        font-size: 1em;
                        font-weight: normal;
                        color: var(--tainacan-info-color);
                        display: inline-block;
                        margin-right: 0.35em;
                        margin-left: -0.15em;
                    }
                    &::after {
                        content: ': ';
                        font-size: 1em;
                        color: var(--tainacan-info-color);
                        display: inline-block;
                        margin-right: 0.15em;
                    }
                }
                p {
                    font-size: 1em !important;
                    line-height: 1.65em !important;
                }
            }
        }
        .metadata-value {
                
            .tainacan-compound-group {
                margin-left: 2px;
                padding-left: 0.875em;
                border-left: 1px solid var(--tainacan-gray2);

                .tainacan-compound-metadatum .label {
                    margin-bottom: 0.25em;
                    font-size: 1em;
                    color: var(--tainacan-info-color);
                }
                .tainacan-compound-metadatum p {
                    margin-bottom: 0.75em;
                    font-size: 1em;
                }
                .multivalue-separator {
                    display: block;
                    max-height: 1px;
                    width: 60px;
                    background: var(--tainacan-gray2);
                    content: none;
                    color: transparent;
                    margin: 1em auto 1em -0.875em;
                }
                
            }
        }
    }
    .loading-overlay {
        min-height: auto !important;
    }
</style>
