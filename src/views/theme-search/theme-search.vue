<template>
    <theme-items-page
            class="theme-items-list"
            :collection-id="$root.collectionId"
            :term-id="$root.termId ? $root.termId : null" 
            :taxonomy="$root.taxonomy ? $root.taxonomy : null"
            :enabled-view-modes="$root.enabledViewModes" 
            :default-view-mode="$root.defaultViewMode"
            :hide-filters="$root.hideFilters ? $root.hideFilters : false"
            :hide-hide-filters-button="$root.hideHideFiltersButton ? $root.hideHideFiltersButton : false"
            :hide-search="$root.hideSearch ? $root.hideSearch : false"
            :hide-advanced-search="$root.hideAdvancedSearch ? $root.hideAdvancedSearch : false"
            :hide-sort-by-button="$root.hideSortByButton ? $root.hideSortByButton : false"
            :hide-items-per-page-button="$root.hideItemsPerPageButton ? $root.hideItemsPerPageButton : false"
            :hide-go-to-page-button="$root.hideGoToPageButton ? $root.hideGoToPageButton : false"
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
    @import "../admin/scss/_tainacan-form.scss";
    @import "../admin/scss/_filters-menu-modal.scss";
    @import "./scss/_layout.scss";
    @import "../admin/scss/_custom_variables.scss";

    .theme-items-list {
        background: var(--tainacan-background-color);
        font-size: var(--tainacan-base-font-size);
        position: relative;
        -webkit-overflow-scrolling: touch;

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

        a, a:not([href]){ color: var(--tainacan-secondary) }
        a:hover, a:hover:not([href]) {
            cursor: pointer;
            color: var(--tainacan-secondary);
            text-decoration: underline;
        }
        ul {
            list-style: none;
        }

        .dropdown {
            display: inline-flex;
            position: relative;
            vertical-align: top;
        }
        .dropdown-menu {
            display: block;
        }
        .dropdown .dropdown-trigger .button .icon, 
        .autocomplete .dropdown-trigger .button .icon {
            align-items: center;
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

        .collapse-all {
            font-size: 0.75em;
        }
        .collapse .collapse-trigger {
            display: inline;
            cursor: pointer;
            .label {
                vertical-align: middle;
                margin-bottom: 0px;
            }
        }
    
        .metadata-alert {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 6px $page-side-padding;
            border-radius: 3px;
            padding: 4px 12px;
            color: var(--tainacan-yellow2);
            background: var(--tainacan-yellow1);
            animation-name: appear;
            animation-duration: 0.5s;

            p {
                margin: 0 auto;
            }
            
            &>div {
                display: flex;
                
                .button,
                .button:hover,
                .button:active,
                .button:focus {
                    background: none;
                    color:var(--tainacan-yellow2);
                    font-weight: bold;
                    border: none;
                    cursor: pointer;
                }
            }
        }
        .b-checkbox.checkbox.is-small {
            font-size: 1em;
        }
    }
    .loading-overlay {
        min-height: auto !important;
    }
</style>
