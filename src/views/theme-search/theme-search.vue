<template>
<div>
    <theme-items-page
            class="theme-items-list"
            :enabled-view-modes="$root.enabledViewModes" 
            :default-view-mode="$root.defaultViewMode"
            :collection-id="$root.collectionId"
            :term-id="$root.termId ? $root.termId : null" 
            :taxonomy="$root.taxonomy ? $root.taxonomy : null"/>
</div>
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

    .theme-items-list {
        position: relative;
        -webkit-overflow-scrolling: touch;

        .tainacan-icon {
            opacity: 0; // Will make it 1 once window.load is done; 
        }

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
        .dropdown .dropdown-trigger .button .icon, 
        .autocomplete .dropdown-trigger .button .icon {
            align-items: center;
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
        
        .metadata-alert {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 6px $page-side-padding;
            border-radius: 3px;
            padding: 4px 12px;
            color: $yellow2;
            background: $yellow1;
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
                    color:$yellow2;
                    font-weight: bold;
                    border: none;
                    cursor: pointer;
                }
            }
        }

    }
</style>
