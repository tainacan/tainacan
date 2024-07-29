<template>
    <section>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-text', 'maxlength') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-text', 'maxlength')"
                        :message="$i18n.getHelperMessage('tainacan-text', 'maxlength')" />
            </label>
            <b-numberinput
                    v-model="maxlength"
                    name="maxlength"
                    step="1"
                    min="0"
                    @update:model-value="onUpdateMaxlength" />
        </b-field> 
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
                    :model-value="displaySuggestions === 'yes' ? '' : mask"
                    :disabled="displaySuggestions === 'yes'"
                    name="mask"
                    @update:model-value="onUpdateMask" />
        </b-field>
    </section>
</template>

<script>
    export default {
        props: {
            value: [ String, Object, Array ]
        },
        emits: ['update:value'],
        data() {
            return {
                displaySuggestions: String,
                mask: String,
                maxlength: [Number, null]
            }
        },
        created() {
            this.displaySuggestions = this.value && this.value.display_suggestions ? this.value.display_suggestions : 'no';
            this.mask = this.value && this.value.mask ? this.value.mask : '';
            this.maxlength = this.value && this.value.maxlength ? Number(this.value.maxlength) : null;
        },
        methods: {
            onUpdateDisplaySuggestions(value) {
                this.displaySuggestions = value;
                this.$emit('update:value', { display_suggestions: value, mask: value == 'yes' ? '' : this.mask, maxlength: this.maxlength });
            },
            onUpdateMask(value) {
                this.mask = value;
                this.$emit('update:value', { display_suggestions: this.displaySuggestions, mask: value, maxlength: this.maxlength });
            },
            onUpdateMaxlength(value) {
                if (value == 0) value = null;

                this.$emit('update:value', { maxlength: value, display_suggestions: this.displaySuggestions, mask: this.mask });
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