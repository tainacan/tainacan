<template>
<div>
    <items-page
            v-if="$root.collectionId != undefined && $root.collectionId != ''"
            class="theme-items-list"
            :enabled-view-modes="$root.enabledViewModes" 
            :default-view-mode="$root.defaultViewMode"
            :collection-id="$root.collectionId" />
    <term-items-page
            v-if="$root.termId != undefined && $root.termId != ''"
            class="theme-items-list"
            :taxonomy="$root.taxonomy"
            :enabled-view-modes="$root.enabledViewModes" 
            :default-view-mode="$root.defaultViewMode"
            :term-id="$root.termId" />
</div>
</template>

<script>
export default {
    name: "ThemeItemsList",
    created() {
        this.$userPrefs.init(); 
    }
}
</script>


<style lang="scss">
   @import "../admin/scss/_variables.scss";

    // Bulma imports
    @import "./scss/theme-basics.sass";

    // Buefy imports
    @import "../../node_modules/buefy/src/scss/components/_datepicker.scss";
    @import "../../node_modules/buefy/src/scss/utils/_all.scss";
    @import "../../node_modules/buefy/src/scss/components/_checkbox.scss";
    @import "../../node_modules/buefy/src/scss/components/_radio.scss";
    @import "../../node_modules/buefy/src/scss/components/_tag.scss";
    @import "../../node_modules/buefy/src/scss/components/_loading.scss";
    @import "../../node_modules/buefy/src/scss/components/_dropdown.scss";
    @import "../../node_modules/buefy/src/scss/components/_modal.scss";

    // Tainacan imports
    @import "../admin/scss/_tables.scss";
    @import "../admin/scss/_modals.scss";
    @import "../admin/scss/_buttons.scss";
    @import "../admin/scss/_inputs.scss";
    @import "../admin/scss/_checkboxes.scss";
    @import "../admin/scss/_pagination.scss";
    @import "../admin/scss/_tags.scss";
    // @import "../admin/scss/_tabs.scss";
    @import "../admin/scss/_selects.scss";
    @import "../admin/scss/_dropdown-and-autocomplete.scss";
    @import "../admin/scss/_tooltips.scss";
    @import "../admin/scss/_tainacan-form.scss";
    @import "../admin/scss/_filters-menu-modal.scss";
    
    .theme-items-list {
        position: relative;
        display: flex;
        -webkit-overflow-scrolling: touch;

        a, a:not([href]){ color: $secondary }
        a:hover, a:hover:not([href]) {
            cursor: pointer;
            color: $secondary;
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
        .b-radio.radio {

            input[type="radio"] + .check {
                width: 13px !important;
                height: 13px !important;
                border: 1px solid $gray4 !important;    
            }
            input[type="radio"] + .check::before {
                background: black !important;
                width: 7px !important;
                height: 7px !important;
            }
            &:focus input[type="radio"] + .check,
            &:active input[type="radio"] + .check,
            &:hover input[type="radio"] + .check {
                box-shadow: none !important;
            }
            input[type="radio"]:checked + .check {
                border-color: $gray4 !important;
            }
            &:focus input[type="radio"]:checked + .check {
                box-shadow: none !important;
            }

        }
        .collapse-all {
            font-size: 0.75rem;
        }
        .collapse .collapse-trigger {
            display: inline;
            cursor: pointer;
            .label {
                vertical-align: middle;
                margin-bottom: 0px;
            }
        }

        .filters-menu {
            // height: auto;
            position: absolute !important;
            min-width: $filter-menu-width-theme;
            border-right: 0;
            padding: 25px 2.0833333% 25px 4.1666667%;

            @media screen and (min-width: 769px) and (max-width: 1023px) {
                min-width: 25% !important;
            }

            .columns {
                display: flex;
            }

            .taginput-container {
                .control.has-icons-left .icon {
                    top: 5px;
                }
            }
            .filter-item-forms .datepicker .dropdown-content {
                max-width: 100% !important;
            }
        }

        .search-control {   
            justify-content: flex-start !important;

            @media screen and (min-width: 769px) {
                margin-bottom: $page-small-top-padding !important;
                
                .search-control-item {
                    margin-right: $page-side-padding !important;
                }
            }

            @media screen and (max-width: 768px) {
                justify-content: space-between !important;
            }
            .search-control-item:first-child>div {
                margin-left: -8.3333333%;
            }

            .gray-icon, .gray-icon .icon {
                color: $gray4 !important;
                i::before {
                    font-size: 21px;
                }
            }
            .dropdown-item {
                padding: 0.25rem 1.35rem 0.25rem 0.25rem;
            }
            .view-mode-icon {
                margin-right: 0px !important;
                margin-top: 1px;
                margin-left: 4px;

                &.icon i::before, .gray-icon i::before {
                    font-size: 19px !important;
                }
            }
        }

        #items-list-area {
            width: 100%;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            margin-left: 0;
            &.spaced-to-right {
                margin-left:  $filter-menu-width-theme;

                @media screen and (min-width: 769px) and (max-width: 1023px) {
                    margin-left: 25% !important;
                }
            }
        }

    }
</style>
