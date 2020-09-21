<template>
    <item-submission-form 
            :collection-id="$root.collectionId"
            class="tainacan-item-submission-form"
            :hide-file-modal-button="$root.hideFileModalButton ? $root.hideFileModalButton : false"
            :hide-text-modal-button="$root.hideTextModalButton ? $root.hideTextModalButton : false"
            :hide-link-modal-button="$root.hideLinkModalButton ? $root.hideLinkModalButton : false"
            :hide-thumbnail-section="$root.hideThumbnailSection ? $root.hideThumbnailSection : false"
            :hide-attachments-section="$root.hideAttachmentsSection ? $root.hideAttachmentsSection : false"
            :hide-collapses="$root.hideCollapses ? $root.hideCollapses : false"
            :enabled-metadata="$root.enabledMetadata ? $root.enabledMetadata : []"
            :sent-form-heading="$root.sentFormHeading"
            :sent-form-message="$root.sentFormMessage" />
</template>

<script>
export default {
    name: "ItemSubmission"
}
</script>

<style lang="scss">

    // TAINACAN Variables
    @import "../admin/scss/_variables.scss";

    // Bulma imports
    @import "./scss/item-submission-basics.sass";

    // Buefy imports
    @import "../../../node_modules/buefy/src/scss/utils/_all.scss";
    @import "../../../node_modules/buefy/src/scss/components/_datepicker.scss";
    @import "../../../node_modules/buefy/src/scss/components/_checkbox.scss";
    @import "../../../node_modules/buefy/src/scss/components/_radio.scss";
    @import "../../../node_modules/buefy/src/scss/components/_switch.scss";
    @import "../../../node_modules/buefy/src/scss/components/_upload.scss";
    @import "../../../node_modules/buefy/src/scss/components/_tag.scss";
    @import "../../../node_modules/buefy/src/scss/components/_loading.scss";
    @import "../../../node_modules/buefy/src/scss/components/_dropdown.scss";
    @import "../../../node_modules/buefy/src/scss/components/_modal.scss";
    @import "../../../node_modules/buefy/src/scss/components/_dialog.scss";
    @import "../../../node_modules/buefy/src/scss/components/_notices.scss";
    @import "../../../node_modules/buefy/src/scss/components/_numberinput.scss";

    // Tainacan imports
    @import "../admin/scss/_tainacan-form.scss";
    @import "../admin/scss/_modals.scss";
    @import "../admin/scss/_buttons.scss"; 
    @import "../admin/scss/_inputs.scss";
    @import "../admin/scss/_checkboxes.scss";
    @import "../admin/scss/_radios.scss";
    @import "../admin/scss/_switches.scss";
    @import "../admin/scss/_pagination.scss";
    @import "../admin/scss/_tags.scss";
    @import "../admin/scss/_notices.scss";
    @import "../admin/scss/_tabs.scss";
    @import "../admin/scss/_selects.scss";
    @import "../admin/scss/_dropdown-and-autocomplete.scss";
    @import "../admin/scss/_tooltips.scss";
    @import "../admin/scss/_control.scss";
    @import "../admin/scss/_custom_variables.scss";

    .tainacan-item-submission-form { 
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
</style>