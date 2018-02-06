<template>
    <div>
        <b-radio
                v-for="option,index in getOptions"
                :key="index"
                v-model="checked"
                @input="onChecked(option)"
                :label="option"
                :native-value="option"
                border>{{ option }}</b-radio>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                checked:''
            }
        },
        props: {
            field: {
                type: Object
            },
            options: {
                type: String
            }
        },
        computed: {
            getOptions(){
                if ( this.options && this.options !== '' ){
                    return this.options.split("\n");
                }
                else if (this.field) {
                    const fields = this.field.field.field_type_options.options;
                    return ( fields ) ? fields.split("\n") : [];
                }
                return [];
            }
        },
        methods: {
            onChecked(option) {
                this.$emit('blur');
                this.onInput(this.checked)
            },
            onInput($event) {
                this.inputValue = $event;
                this.$emit('input', this.inputValue);
            }
        }
    }
</script>