<template>
    <section
            :listen="setError">
        <b-field :addons="false"
                 :type="optionType"
                 :message="optionMessage"
        >
            <label class="label">
                {{ $i18n.get('label_options') }}<span :class="optionType" >&nbsp;*&nbsp;</span>
                <a class="help-button"><b-icon size="is-small" icon="help-circle-outline"></b-icon></a>
            </label>
            <b-taginput
                    v-model="options"
                    @input="emitValues()"
                    @focus="clear()"
                    icon="label"
                    :placeholder="$i18n.get('new')">
            </b-taginput>
        </b-field>
    </section>
</template>

<script>
    export default {
        props: {
            value: [ String, Object, Array ],
            field: [ String, Object ],
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
                    this.optionType = 'is-danger';
                    this.optionMessage = this.errors.options;
                } else {
                    this.optionType = '';
                    this.optionMessage = '';
                }
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
            }
        }
    }
</script>

<style scoped>
    section{
        margin-bottom: 10px;
    }
</style>