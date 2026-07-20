<template>
    <section>
        <b-field 
                :addons="false"
                :listen="setError"
                :type="optionType"
                :message="optionMessage">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-selectbox', 'options') }}<span :class="optionType">&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-selectbox', 'options')"
                        :message="$i18n.getHelperMessage('tainacan-selectbox', 'options')" />
            </label>

            <b-taginput
                    v-model="options"
                    v-a11y-autocomplete
                    attached
                    :confirm-keys="optionsSeparator"
                    :on-paste-separators="optionsSeparator"
                    :remove-on-keys="[]"
                    :aria-close-label="$i18n.get('remove_value')"
                    class="tainacan-selectbox-metadata-type--taginput"
                    :class="{'has-selected': options != undefined && options != []}"
                    :placeholder="$i18n.get('new') + ', ...'"
                    @update:model-value="emitValues()"
                    @focus="clear()" />
            <div class="separator-options">
                <label class="label is-inline">{{ $i18n.getHelperTitle('tainacan-selectbox', 'options_separator') }}</label>
                <b-checkbox
                        v-for="separator of ['Enter', 'Tab', ',', ';', '|']"
                        :key="separator"
                        v-model="optionsSeparator"
                        name="metadata_type_selectbox[options_separator]"
                        :native-value="separator"
                        :disabled="separator == 'Enter'"
                        @update:model-value="emitValues()">
                    <kbd class="tainacan-kbd">{{ separator }}</kbd>
                </b-checkbox>
            </div>
        </b-field>

        <b-field :addons="false">
            <label class="label">
                {{ $i18n.get('label_input_type') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-selectbox', 'input_type')"
                        :message="$i18n.getHelperMessage('tainacan-selectbox', 'input_type')" />
            </label>
            <b-select
                    v-if="listInputType"
                    v-model="input_type"
                    name="metadata_type_options[input_type]"
                    expanded
                    @update:model-value="emitValues()">
                <option
                        v-for="(option, index) in single_types"
                        :key="index"
                        :value="index">
                    {{ option }}
                </option>
            </b-select>

            <b-select
                    v-else
                    v-model="input_type"
                    name="metadata_type_options[input_type]"
                    expanded
                    @update:model-value="emitValues()">
                <option
                        v-for="(option, index) in multiple_types"
                        :key="index"
                        :value="index">
                    {{ option }}
                </option>
            </b-select>
        </b-field>
    </section>
</template>

<script>
    export default {
        props: {
            value: [ String, Object, Array ],
            metadatum: [ String, Object ],
            errors: [ String, Object, Array ]
        },
        emits: ['update:value'],
        data() {
            return {
                optionType: '',
                optionMessage: '',
                options: [],
                optionsSeparator: [",", "Tab", "Enter"],
                input_type: 'tainacan-selectbox',
                single_types: {},
                multiple_types: {}
            }
        },
        computed: {
            listInputType() {
                if ( this.metadatum && this.metadatum.multiple === 'no' ) {
                    let types = Object.keys( this.single_types );
                    let hasValue = this.value && this.value.input_type && types.indexOf( this.value.input_type ) >= 0;
                    if (hasValue)
                        this.setInputType(this.value.input_type)
                    else {
                        this.setInputType('tainacan-selectbox');
                        this.emitValues();
                    }

                    return true;
                } else {
                    let types = Object.keys( this.multiple_types );
                    let hasValue = this.value && this.value.input_type && types.indexOf( this.value.input_type ) >= 0;
                    if (hasValue)
                        this.setInputType(this.value.input_type)
                    else
                        this.setInputType('tainacan-selectbox');
                        
                    return false;
                }
            },
            setError(){
                if( this.errors && this.errors.options !== '' ){
                    this.setErrorsAttributes( 'is-danger', this.errors.options )
                } else {
                    this.setErrorsAttributes( '', '' )
                }
                return true;
            }
        },
        watch: {
            input_type:{
                handler(val, oldValue) {
                    if (val != oldValue) {
                        this.emitValues();
                    }
                }
            }
        },
        created(){
            this.single_types['tainacan-selectbox'] = this.$i18n.get('label_input_type_selectbox');
            this.single_types['tainacan-selectbox-radio'] = this.$i18n.get('label_input_type_radio');
            this.single_types['tainacan-selectbox-radio-button'] = this.$i18n.get('label_input_type_selection_buttons');

            this.multiple_types['tainacan-selectbox'] = this.$i18n.get('label_input_type_selectbox');
            this.multiple_types['tainacan-selectbox-checkbox'] = this.$i18n.get('label_input_type_checkbox');
            this.multiple_types['tainacan-selectbox-checkbox-button'] = this.$i18n.get('label_input_type_selection_buttons');

            if ( this.value ) {
                this.options = ( this.value.options ) ? this.value.options.split('\n') : [];
                this.optionsSeparator = ( this.value.options_separator ) ? JSON.parse(this.value.options_separator) : [",", "Tab", "Enter"];

                if (this.metadatum && this.metadatum.multiple === 'no') {
                    let types = Object.keys( this.single_types );
                    let hasValue = this.value && this.value.input_type && types.indexOf( this.value.input_type ) >= 0;
                    this.setInputType( ( hasValue ) ? this.value.input_type : 'tainacan-selectbox' );
                } else {
                    let types = Object.keys( this.multiple_types );
                    let hasValue = this.value && this.value.input_type && types.indexOf( this.value.input_type ) >= 0;
                    this.setInputType( ( hasValue ) ? this.value.input_type : 'tainacan-selectbox' );
                }
            }
        },
        methods: {
            setInputType( input ) {
                this.input_type = input;
            },
            clear(){
                this.optionType = '';
                this.optionMessage = '';
            },
            emitValues() {
                this.$emit('update:value', {
                    options: ( this.options.length > 0 ) ? this.options.join('\n') : '',
                    options_separator: JSON.stringify(this.optionsSeparator),
                    input_type: this.input_type
                })
            },
            setErrorsAttributes( type, message ){
                this.optionType = type;
                this.optionMessage = message;
            }
        }
    }
</script>

<style lang="scss" scoped>

    section {
        margin-bottom: 10px;
    }
    .tainacan-help-tooltip-trigger {
        font-size: 1.25em;
    }
    .separator-options {
        display: flex;
        flex-wrap: wrap;
        padding: 4px 10px 1px;
        background: #f9f9f9;
        border: 1px solid var(--tainacan-gray1, #f2f2f2);
        border-bottom-right-radius: 2px;
        border-bottom-left-radius: 2px;
        .b-checkbox {
            width: auto;
            margin-right: 0.75em;
        }
        &>label {
            opacity: 0.875;
            font-size: 0.75em;
            margin-right: 1em;
            display: block;
            width: 100%;
        }
    }
    .tainacan-selectbox-metadata-type--taginput {
        :deep(.tag),
        :deep(.tags) {
            white-space: normal !important;
        }
    }
</style>
