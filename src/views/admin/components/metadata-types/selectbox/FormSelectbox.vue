<template>
    <section>
        <!-- <b-field
                :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-selectbox', 'options_separator') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-selectbox', 'options_separator')"
                        :message="$i18n.getHelperMessage('tainacan-selectbox', 'options_separator')"/>
            </label>
            
        </b-field> -->
        <b-field 
                :addons="false"
                :listen="setError"
                :type="optionType"
                :message="optionMessage">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-selectbox', 'options') }}<span :class="optionType" >&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-selectbox', 'options')"
                        :message="$i18n.getHelperMessage('tainacan-selectbox', 'options')"/>
            </label>

            <b-taginput
                    v-model="options"
                    @input="emitValues()"
                    @focus="clear()"
                    attached
                    :confirm-keys="optionsSeparator"
                    :on-paste-separators="optionsSeparator"
                    :remove-on-keys="[]"
                    :aria-close-label="$i18n.get('remove_value')"
                    class="tainacan-selectbox-metadata-type--taginput"
                    :class="{'has-selected': options != undefined && options != []}"
                    :placeholder="$i18n.get('new') + ', ...'" />
            <div class="separator-options">
                <label class="label is-inline">{{ $i18n.getHelperTitle('tainacan-selectbox', 'options_separator') }}</label>
                <b-checkbox
                        v-for="separator of ['Enter', 'Tab', ',', ';', '|']"
                        :key="separator"
                        name="metadata_type_selectbox[options_separator]"
                        @input="emitValues()"
                        v-model="optionsSeparator"
                        :native-value="separator"
                        :disabled="separator == 'Enter'">
                    <kbd>{{ separator }}</kbd>
                </b-checkbox>
            </div>
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
        data() {
            return {
                optionType: '',
                optionMessage: '',
                options: [],
                optionsSeparator: [",", "Tab", "Enter"]
            }
        },
        computed: {
            setError(){
                if( this.errors && this.errors.options !== '' ){
                    this.setErrorsAttributes( 'is-danger', this.errors.options )
                } else {
                    this.setErrorsAttributes( '', '' )
                }
                return true;
            }
        },
        created(){
            if ( this.value ) {
                this.options = ( this.value.options ) ? this.value.options.split('\n') : [];
                this.optionsSeparator = ( this.value.options_separator ) ? JSON.parse(this.value.options_separator) : [",", "Tab", "Enter"];
            }
        },
        methods: {
            clear(){
                this.optionType = '';
                this.optionMessage = '';
            },
            emitValues() {
                this.$emit('input', {
                    options: ( this.options.length > 0 ) ? this.options.join('\n') : '',
                    options_separator: JSON.stringify(this.optionsSeparator)
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
    .tainacan-selectbox-metadata-type--taginput /deep/ {
        .tag,
        .tags {
            white-space: normal !important;
            min-height: calc(2em - 1px) !important;
            height: auto !important;
        }
        .tag.is-delete {
            min-width: calc(2em - 1px) !important;
        }
    }
</style>