<template>
    <span 
            @mouseenter="loadMetadataElements"
            class="tainacan-help-tooltip-trigger">
        <v-tooltip 
                :popper-class="['tainacan-tooltip', 'tooltip', 'tainacan-helper-tooltip', 'is-danger']"
                delay="{
                    shown: 0,
                    hide: 400,
                }">
            <a class="help-button has-text-danger">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-alertcircle" />
                </span>
            </a>
            <template #popper>
                <p><strong>{{ $i18n.get('instruction_click_error_to_go_to_metadata') }}</strong></p>
                <ol>
                    <template v-for="(error, index) of formErrors">
                        <li 
                                v-if="error.errors.length"
                                :key="index">
                            <a 
                                    v-if="['thumbnail', 'attachments', 'document'].includes(error.metadatum_id)"
                                    @click="metadataElements[error.metadatum_id].scrollIntoView({ behavior: 'smooth', block: 'center' })">
                                {{ getErrorMessage(error.errors) }}
                            </a>
                            <a 
                                    v-else-if="metadataElements[error.metadatum_id + (error.parent_meta_id ? ('_parent_meta_id-' + error.parent_meta_id) : '')]"
                                    @click="metadataElements[error.metadatum_id + (error.parent_meta_id ? ('_parent_meta_id-' + error.parent_meta_id) : '')].scrollIntoView({ behavior: 'smooth', block: 'center' })">
                                {{ getErrorMessage(error.errors) }}
                            </a>                           
                            <p v-else>{{ getErrorMessage(error.errors) }}</p>
                        </li>
                    </template>
                </ol>
            </template>
        </v-tooltip>
    </span>
</template>

<script>
import { VTooltip } from 'floating-vue'; 
export default {
    name: 'HelpButton',
    components: [
        VTooltip
    ],
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
                if (typeof singleError != 'string') {
                    for (let index of Object.keys(singleError))
                        metadatumErrorMessage += singleError[index] + '\n';
                } else {
                    metadatumErrorMessage += singleError;
                }
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

    a.help-button .icon {
        i, i::before { font-size: 1.125em !important }
    }
    
</style>

