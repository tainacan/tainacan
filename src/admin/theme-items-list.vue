<template>
    <items-page
            class="theme-items-list" 
            :collection-id="$root.collectionId" />
</template>

<script>
export default {
    name: "ThemeItemsList",
}
</script>


<style lang="scss">
    @import "./scss/theme-basics.sass";
    @import "../../node_modules/buefy/src/scss/components/_datepicker.scss";
    $speed-slow: 0.5s;
    @import "../../node_modules/buefy/src/scss/utils/_functions.scss";
    @import "../../node_modules/buefy/src/scss/components/_checkbox.scss";
    @import "../../node_modules/buefy/src/scss/components/_radio.scss";
    @import "../../node_modules/buefy/src/scss/components/_tag.scss";

    /* Rules for sizing the icon. */
    .material-icons.md-18 { font-size: 18px; }
    .material-icons.md-24 { font-size: 24px; }
    .material-icons.md-36 { font-size: 36px; }
    .material-icons.md-48 { font-size: 48px; }

    /* Rules for using icons as black on a light background. */
    .material-icons.md-dark { color: rgba(0, 0, 0, 0.54); }
    .material-icons.md-dark.md-inactive { color: rgba(0, 0, 0, 0.26); }

    /* Rules for using icons as white on a dark background. */
    .material-icons.md-light { color: rgba(255, 255, 255, 1); }
    .material-icons.md-light.md-inactive { color: rgba(255, 255, 255, 0.3); }

    // Tainacan custom colors
    $primary: #2cb4c1;
    $primary-invert: findColorInvert($primary);
    $secondary: #298596;
    $secondary-invert: findColorInvert($secondary);
    $tertiary: #01295c;
    $tertiary-invert: findColorInvert($tertiary);

    $primary-light:#c1dae0;
    $primary-lighter: #e6f6f8;
    $primary-dark: #55A0AF;
    $primary-darker: darken($primary-dark, 5%);

    $success: #25a189;
    $success-invert: findColorInvert($success);

    $separator-color: #2b98a4;
    $tainacan-input-color: #1d1d1d;
    $tainacan-input-background: #e5e5e5;
    $tainacan-placeholder-color: #898d8f;
    $draggable-border-color: #d8d8d8;

    $gray: #b1b1b1; 
    $gray-invert: findColorInvert($gray);
    $gray-light: #898d8f; 
    $gray-light-invert: findColorInvert($gray-light);

    $danger: #a23939; 
    $danger-invert: findColorInvert($danger);
    
    .theme-items-list {

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
            color: #1d1d1d;
            font-size: 1.0em;
            font-weight: normal;
            cursor: pointer;
            background-color: white;

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

        .select {
            padding-top: 0px !important;
            select {
                border: none;
                border-radius: 1px !important;
                font-weight: normal;
                font-size: 14px !important;
                height: 30px !important;
                padding: 2px 25px 2px 15px!important;
                margin-top: 0px !important;
                margin-bottom: 0px !important;
                color: $tainacan-input-color;
                option:checked, option:hover {
                    background-color: $primary-lighter !important;
                }
                &:focus, &:active {
                    box-shadow: none !important;
                    text-decoration: none !important;
                }
            }
            &:not(.is-multiple)::after {
                content: "\F35D" !important;
                font: normal normal normal 24px/1 "Material Design Icons" !important;
                border: none !important;
                transform: none !important;
                margin-top: -15px !important;
                right: 10px !important;
                color: $primary;
                display: flex !important;
                align-items: initial;
            }       
        }
        .dropdown {
            display: inline-flex;
            position: relative;
            vertical-align: top;
        }
        .dropdown, .autocomplete {
            .dropdown-trigger{
                .button {
                    border: none;
                    .icon {
                        color: $secondary;
                        align-items: start;
                    }
                }
                .button.is-primary, .button.is-secondary, .button.is-success {
                    .icon {
                        color: $white;
                    }
                }
            }
            .dropdown-menu {
                display: block;
                .dropdown-content {
                    font-size: 13px !important;
                    border-radius: 0px !important;
                    .dropdown-item {
                        .b-checkbox { width: 100% };
                        &:hover {
                            background-color: $primary-lighter;
                        }
                        .is-small {
                            color: gray;
                        }
                    }
                } 
            }
        }
        .taginput-container {
            padding: 0 !important;
            background-color: white !important;

            &:focus, &:active {
                border: none !important;
            }
            .input { margin-bottom: 0px !important; }
            .input.has-selected, .input:focus, .input:active {
                background-color: white;
                border: 1px solid $tainacan-input-background !important;
            }
            .tag {
                background: white;
                padding-right: 0;
                padding-left: 0.5em;

                &.is-delete {
                    color: $gray-light;
                    &::after {
                        height: 30% !important;
                        width: 1px !important;
                    } 
                    &::before {
                        width: 30% !important;
                        height: 1px !important;
                    } 
                    &:hover, &:focus {
                        background-color: white;
                        color: black;
                    }
                }
            }
        }
        .selected-list-box {
            padding: 4px 0px;
            border: 1px solid $tainacan-input-background;
            background-color: white;
            display: flex;

            .tags { 
                margin-right: 8px;
            }
            .tag {
                background: white;
                padding-right: 0;
                padding-left: 0.5em;

                &.is-delete {
                    color: $gray-light;
                    &::after {
                        height: 30% !important;
                        width: 1px !important;
                    } 
                    &::before {
                        width: 30% !important;
                        height: 1px !important;
                    } 
                    &:hover, &:focus {
                        background-color: white;
                        color: black;
                    }
                }
            }
        }
        .switch {
            input[type="checkbox"] + .check {
                background-color: $gray-light;
                border: 2px solid $gray-light;
                width: 2.7em;
                height: 1.7em;

                &::before {
                    background-color: white;
                    box-shadow: none;
                }
            } 
            &:hover input[type="checkbox"]:checked + .check {
                background-color: $primary-light;
            }

            input[type="checkbox"]:checked + .check {
                border: 2px solid $secondary;
                background-color: transparent;

                &::before {
                    background-color: $secondary;
                    transform: translate3d(78%, 0, 0);
                }
            }
            &:hover input[type="checkbox"] + .check {
                background-color: $gray-light;
            }

            &:focus input[type="checkbox"] + .check,
            &:focus input[type="checkbox"]:checked + .check {
                box-shadow: none;
            }

            &.is-small {
                font-size: 9px;

                input[type="checkbox"] + .check {
                    border: 1.5px solid $gray-light;
                    width: 2.55em;
                    height: 1.7em;

                    &::before {
                        width: 1.0em;
                        height: 1.0em;
                    }
                }
                input[type="checkbox"]:checked + .check {
                    border: 1.5px solid $secondary;

                    &::before {
                        transform: translate3d(84%, 0, 0);
                    }
                }
            }

        }
        .b-checkbox.checkbox {

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

        // Tables
        .table-wrapper {
            .table {
                tbody {
                    td { border-bottom: 0px solid $gray-light !important; }
                }
                .b-checkbox.checkbox input[type="checkbox"] + .check {
                    width: 0.85em !important;
                    height: 0.85em !important;
                    border-radius: 2px !important;
                    border: 1px solid #7a7a7a !important;
                }
                .th-wrap {
                    font-size: 12px !important;
                    font-weight: normal !important;
                }
            }
            margin-bottom: 0px !important;
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
            border: none;
            border-radius: 1;
            padding: 4px 16px;
            color: #1d1d1d;
            font-size: 1.0em;
            font-weight: normal;
            cursor: pointer;
            background-color: white;
        }

    }
</style>
