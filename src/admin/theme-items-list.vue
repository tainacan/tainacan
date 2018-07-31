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

        // Some components have a different style in listing pages
        .button {
            height: inherit !important;
            box-shadow: none !important;
            border-radius: 6px;
            padding: 2px 15px;
            margin-top: 0px;
            margin-bottom: 0px;
            display: inline-flex;
            font-weight: normal;
            cursor: pointer;

            &.is-secondary:hover, &.is-secondary:focus {
                background: $secondary !important;
            }
            &.is-primary:hover, &.is-primary:focus {
                background: $turquoise3 !important;
            }
            &.is-success:hover, &.is-success:focus {
                background: $success !important;
            }
            &.is-white:hover, &.is-white:focus, &.is-outlined:hover, &.is-outlined:focus {
                background: $white !important;
            }
            &:active {
                -webkit-transform: none !important;
                transform: none !important;
            }
            &.is-outlined {
                color: #150e38 !important;
                background-color: white;
                border-color: $gray4 !important;
            }
            &:focus {
                outline: 0px;
            }
        }
        .button.is-small {
            height: 26px !important;
            line-height: 12px;
        }
        .button:not(.is-small):not(.is-medium):not(.is-large) {
            height: 30px !important;
            line-height: 20px !important;
            font-size: 14px !important;
        }
        .input, .textarea {
            font-size: 14px;
            border: 1px solid $gray2;
            border-radius: 1px !important;
            background-color: white;
            box-shadow: none !important;

            &:focus, &:active {
                box-shadow: none !important;
                background-color: white;
                border: 1px solid $gray2 !important;
            }    
        }
        .dropdown {
            display: inline-flex;
            position: relative;
            vertical-align: top;
        }
        .dropdown-menu {
            display: block;
        }
        .b-checkbox.checkbox {
            align-items: baseline;
            margin-bottom: 5px;

            input[type="checkbox"] {
                box-shadow: none !important;
            }

            input[type="checkbox"] + .check {
                width: 1.0em;
                height: 1.0em;
                flex-shrink: 0;
                border-radius: 0;
                border: 1px solid $gray4;
                transition: background 150ms ease-out;
                box-shadow: none !important;
            }

            &:focus input[type="checkbox"] + .check, 
            &:active input[type="checkbox"] + .check, 
            &:hover input[type="checkbox"] + .check {
                box-shadow: none !important;
                border-color: $gray4 !important;
            }
            input[type="checkbox"]:checked + .check {
                background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3Cpath style='fill:%23000' d='M 0.04038059,0.6267767 0.14644661,0.52071068 0.42928932,0.80355339 0.3232233,0.90961941 z M 0.21715729,0.80355339 0.85355339,0.16715729 0.95961941,0.2732233 0.3232233,0.90961941 z'%3E%3C/path%3E%3C/svg%3E") no-repeat center center !important;
                border-color: $gray4 !important;
            }
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
