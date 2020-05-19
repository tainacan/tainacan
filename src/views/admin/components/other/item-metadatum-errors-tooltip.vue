<template>
    <span 
            @mouseenter="loadMetadataElements"
            class="help-wrapper">
        <a class="help-button has-text-danger">
            <span class="icon is-small">
                <i class="tainacan-icon tainacan-icon-alertcircle" />
            </span>
        </a>
        <div class="help-tooltip">
            <div class="help-tooltip-header">
                <h5>{{ $i18n.get('instruction_click_error_to_go_to_metadata') }}</h5>
            </div>
            <div class="help-tooltip-body">
                <ol>
                    <li 
                            v-for="(error, index) of formErrors"
                            :key="index">
                        <a 
                                v-if="metadataElements[error.metadatum_id + (error.parent_meta_id ? ('_parent_meta_id-' + error.parent_meta_id) : '')]"
                                @click="metadataElements[error.metadatum_id + (error.parent_meta_id ? ('_parent_meta_id-' + error.parent_meta_id) : '')].scrollIntoView({ behavior: 'smooth', block: 'center' })">
                            {{ getErrorMessage(error.errors) }}
                        </a>
                        <p v-else>{{ getErrorMessage(error.errors) }}</p>
                    </li>
                </ol>
            </div>
        </div> 
    </span>
</template>

<script>
export default {
    name: 'HelpButton',
    props: {
        formErrors: Array
    },
    data() {
        return {
            metadataElements: {}
        } 
    },
    methods: {
        getErrorMessage(errors) {
            let metadatumErrorMessage = '';
            for (let singleError of errors) { 
                for (let index of Object.keys(singleError))
                    metadatumErrorMessage += singleError[index] + '\n';
            }
            return metadatumErrorMessage;
        },
        loadMetadataElements() {
            this.metadataElements = {};
            this.formErrors.map((error) => {
                this.metadataElements[error.metadatum_id + (error.parent_meta_id ? ('_parent_meta_id-' + error.parent_meta_id) : '')] = document.getElementById('tainacan-item-metadatum_id-' + error.metadatum_id + (error.parent_meta_id ? ('_parent_meta_id-' + error.parent_meta_id) : ''));
            });
        }
    }
}
</script>

<style scoped lang="scss">

    .help-wrapper {
        position: absolute;
        font-size: 1.25em;
    }

    a.help-button .icon {
        i, i::before { font-size: 1em !important }
    }

    .help-wrapper:hover .help-tooltip {
        margin-bottom: 12px;
        margin-left: -37px;
        visibility: visible;
        opacity: 1; 
    }
    .help-tooltip {
        z-index: 99999999999999999999;
        color: var(--tainacan-red2);
        background-color: var(--tainacan-red1);
        border: none;
        display: block;
        border-radius: 5px;
        min-width: 280px;
        max-width: 100%;
        transition: margin-bottom 0.2s ease, opacity 0.3s ease;
        position: absolute;
        bottom: calc(100% - 6px);
        left: 0%;            
        margin-bottom: -27px;
        visibility: hidden;
        opacity: 0;

        .help-tooltip-header {
            padding: 0.8em 0.8em 0em 0.8em;

            h5 {
                font-size: 0.875em;
                font-weight: bold;
            }
        }

        .help-tooltip-body {
            padding: 0.5em 1.0em 1.0em 1.0em;
            max-height: 100% !important;

            p, a {
                font-size: 0.875em !important;
                font-weight: normal !important;
                white-space: normal !important;
                overflow: visible !important;
            }
            a {
                color: var(--tainacan-red2);
                text-decoration: underline;
            }
            ol, ul {
                margin: 4px 4px;
                padding-left: 16px;
            }
        }
        &:before {
            content: "";
            display: block;
            position: absolute;
            left: 30px;
            width: 0;
            height: 0;
            border-style: solid;
        }
        &:before {
            border-color: var(--tainacan-red1) transparent transparent transparent;
            border-right-width: 14px;
            border-top-width: 12px;
            border-left-width: 14px;
            bottom: -15px;
        }
    }
    
</style>

