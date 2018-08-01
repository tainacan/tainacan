<template>
    <items-page
            class="theme-items-list"
            :enabled-view-modes="$root.enabledViewModes" 
            :default-view-mode="$root.defaultViewMode"
            :collection-id="$root.collectionId" />
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
    @import "../admin/scss/_selects.scss";
    @import "../admin/scss/_dropdown-and-autocomplete.scss";
    @import "../admin/scss/_tooltips.scss";
    @import "../admin/scss/_tainacan-form.scss";
    @import "../admin/scss/_filters-menu-modal.scss";
    
    .theme-items-list {
        position: relative;
        display: flex;

        a{ color: $secondary !important }
        a:hover {
            cursor: pointer;
            color: $secondary !important;
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

        // Tabs 
        .tabs {
            a {
                font-size: 13px;
                margin-bottom: -3px;
                &:hover{
                    border-bottom-color: transparent;
                }
            }
            li.is-active a {
                border-bottom: 5px solid $turquoise3;
                color: $turquoise3; 
            }
        }

        .select select{
            border-radius: 1;
            padding: 4px 16px;
            color: #1d1d1d;
            font-size: 1.0em;
            font-weight: normal;
            cursor: pointer;
            background-color: white;
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
        }

        .search-control {   

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
            overflow-y: unset;
            margin-left: 0;
            &.spaced-to-right {
                margin-left:  $filter-menu-width-theme;

                @media screen and (min-width: 769px) and (max-width: 1023px) {
                    margin-left: 25% !important;
                }
            }
        }

        #filters-mobile-modal {
            // top: 95px;
            @keyframes slide-menu {
                from {
                    -ms-transform: translate(-100%, 0); /* IE 9 */
                    -webkit-transform: translate(-100%, 0); /* Safari */
                    transform: translate(-100%, 0);
                }
                to {
                    -ms-transform: translate(0, 0); /* IE 9 */
                    -webkit-transform: translate(0, 0); /* Safari */
                    transform: translate(0, 0);
                }
            }

            animation-name: slide-menu;
            animation-duration: 0.3s;
            animation-timing-function: ease-out;

            @keyframes appear {
                from {
                    opacity: 0.0;
                    visibility: hidden;
                }
                to {
                    opacity: 1.0;
                    visibility: visible;
                }
            }

            .modal-background {
                animation-name: appear;
                animation-duration: 0.6s;
                animation-timing-function: ease-in;
            }

            .modal-close {
                right: calc(8.3333333% + 20px);
                background-color: $gray1;

                &:hover {
                    background-color: $gray1;   
                }
                &::before, &::after {
                    background-color: $secondary;
                }
            }
            .modal-content {
                margin: 0 8.3333333% 0 0;
                padding: 24px $page-side-padding;
                border-radius: 0;
                height: 100%;
                max-height: 100%;
                overflow-y: auto;

                h3 {
                    font-size: 100%;
                }
            }
        }

    }
</style>
