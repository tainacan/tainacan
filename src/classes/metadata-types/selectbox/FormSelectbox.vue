<template>
    <section
            :listen="setError">
        <b-field 
                :addons="false"
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
                    :class="{'has-selected': options != undefined && options != []}"
                    :placeholder="$i18n.get('new') + ', ...'"/>
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
                options: []
            }
        },
        created(){
            if( this.value ) {
                this.options = ( this.value.options ) ? this.value.options.split('\n') : [];
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
        methods: {
            clear(){
                this.optionType = '';
                this.optionMessage = '';
            },
            emitValues(){
                this.$emit('input',{
                    options: ( this.options.length > 0 ) ? this.options.join('\n') : ''
                })
            },
            setErrorsAttributes( type, message ){
                this.optionType = type;
                this.optionMessage = message;
            }
        }
    }
</script>

<style scoped>
    section{
        margin-bottom: 10px;
    }
</style>