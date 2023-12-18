<template>
    <section> 
        <b-field
                :addons="false"
                :label="$i18n.getHelperTitle('tainacan-text', 'display_suggestions')">
                &nbsp;
            <b-switch
                    size="is-small" 
                    :model-value="displaySuggestions"
                    :true-value="'yes'"
                    :false-value="'no'"
                    @update:model-value="onUpdateDisplaySuggestions" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-text', 'display_suggestions')"
                    :message="$i18n.getHelperMessage('tainacan-text', 'display_suggestions')" />
        </b-field>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-text', 'mask') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-text', 'mask')"
                        :message="$i18n.getHelperMessage('tainacan-text', 'mask')" />
            </label>
            <b-input
                    :value="displaySuggestions === 'yes' ? '' : mask"
                    :disabled="displaySuggestions === 'yes'"
                    name="mask"
                    @input="onUpdateMask" />
        </b-field>
    </section>
</template>

<script>
    export default {
        props: {
            value: [ String, Object, Array ]
        },
        emits: ['input'],
        data() {
            return {
                displaySuggestions: String,
                mask: String
            }
        },
        created() {
            this.displaySuggestions = this.value && this.value.display_suggestions ? this.value.display_suggestions : 'no';
            this.mask = this.value && this.value.mask ? this.value.mask : '';
        },
        methods: {
            onUpdateDisplaySuggestions(value) {
                this.displaySuggestions = value;
                this.$emit('input', { display_suggestions: value, mask: value == 'yes' ? '' : this.mask });
            },
            onUpdateMask(value) {
                this.mask = value;
                this.$emit('input', { display_suggestions: this.displaySuggestions, mask: value });
            }
        }
    }
</script>

<style scoped>
    section{
        margin-bottom: 10px;
    }
    .tainacan-help-tooltip-trigger {
        font-size: 1em;
    }
</style>