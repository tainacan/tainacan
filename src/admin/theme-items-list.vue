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
    $speed-slow: 0.5s;
    @import "../../node_modules/buefy/src/scss/utils/_functions.scss";
    @import "../../node_modules/buefy/src/scss/components/_checkbox.scss";
    @import "../../node_modules/buefy/src/scss/components/_radio.scss";
    @import "../../node_modules/buefy/src/scss/components/_tag.scss";
    @import "../../node_modules/buefy/src/scss/components/_loading.scss";
    @import "../../node_modules/buefy/src/scss/components/_dropdown.scss";

    // Tainacan imports
    @import "../admin/scss/_tables.scss";
    @import "../admin/scss/_selects.scss";
    @import "../admin/scss/_dropdown-and-autocomplete.scss";
    @import "../admin/scss/_tooltips.scss";
    @import "../admin/scss/_tainacan-form.scss";
    
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
            border: none;
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
                background: $primary !important;
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
                border-color: $gray-light !important;
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
            border: 1px solid $tainacan-input-background;
            border-radius: 1px !important;
            background-color: white;
            box-shadow: none !important;

            &:focus, &:active {
                box-shadow: none !important;
                background-color: white;
                border: 1px solid $tainacan-input-background !important;
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
                border: 1px solid $gray-light;
                transition: background 150ms ease-out;
                box-shadow: none !important;
            }

            &:focus input[type="checkbox"] + .check, 
            &:active input[type="checkbox"] + .check, 
            &:hover input[type="checkbox"] + .check {
                box-shadow: none !important;
                border-color: $gray-light !important;
            }
            input[type="checkbox"]:checked + .check {
                background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3Cpath style='fill:%23000' d='M 0.04038059,0.6267767 0.14644661,0.52071068 0.42928932,0.80355339 0.3232233,0.90961941 z M 0.21715729,0.80355339 0.85355339,0.16715729 0.95961941,0.2732233 0.3232233,0.90961941 z'%3E%3C/path%3E%3C/svg%3E") no-repeat center center !important;
                border-color: $gray-light !important;
            }
        }
        .b-radio.radio {

            input[type="radio"] + .check {
                width: 13px !important;
                height: 13px !important;
                border: 1px solid $gray-light !important;    
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
                border-color: $gray-light !important;
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

        .pagination-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85em !important;
            font-weight: normal !important;
            border-top: 1px solid $gray;
            padding: 0em 1.0em;
            color: $gray-light;

            .shown-items { 
                flex-grow: 1; 
            }

            .items-per-page { 
                flex-grow: 3;
                margin-top: 0.35em;
                .field-label {
                    flex-grow: 5;
                    margin-right: 0.5em;
                    .label {
                        font-size: 1em !important;
                        font-weight: normal !important;
                        color: $gray-light;
                    }
                }
                select {
                    font-size: 0.85em;
                } 
            }

            .pagination { 
                flex-grow: 1; 

                ul {
                    margin-bottom: 0px;
                    padding: 0px;
                }

                &.is-small {
                    font-size: 0.85em;
                }

                a[disabled="disabled"] {
                    color: $gray-light;
                }
                .pagination-link, .pagination-previous, .pagination-next {
                    background-color: transparent;
                    color: $secondary;
                    border: none;
                }
                .pagination-link.is-current {
                    color: $gray-light;
                }
                .pagination-link::after:not(:last-child) {
                    content: ',';
                    color: $gray-light;
                }
                .mdi-chevron-left::before {
                    content: "\F40A";
                    font-size: 17px;
                    transform: rotate(180deg);
                }
                .mdi-chevron-right::before {
                    content: "\F40A";
                    font-size: 17px;
                }
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
                border-bottom: 5px solid $primary;
                color: $primary; 
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

        #filter-menu-compress-button {
            top: 70px !important;
        }

        .filters-menu {
            height: auto;
            min-width: $filter-menu-width-theme;
            border-right: 0;
            padding: 25px 12px 25px 4.1666667%;

            .columns {
                display: flex;
            }
        }

        .search-control {
            border-bottom: 0;

            .gray-icon, .gray-icon .icon {
                color: $tainacan-placeholder-color !important;
                i::before {
                    font-size: 21px;
                }
            }
            .view-mode-icon {
                margin-right: 8px !important;
                margin-top: 2px;
            }
        }

        #items-list-area {
            width: 100%;
            overflow-y: unset;
        }

    }
</style>
