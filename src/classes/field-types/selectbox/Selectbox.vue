<template>
    <div>
        <b-select v-model="selected" @input="onChecked()">
            <option
                    v-for="option,index in getOptions"
                    :key="index"
                    :label="option"
                    :value="option"
                    border>{{ option }}</option>
        </b-select>
    </div>
</template>

<script>

    export default {
        data(){
            return {
                selected:''
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
                else if ( this.field && this.field.field.field_type_options.options ) {
                    const fields = this.field.field.field_type_options.options;
                    return ( fields ) ? fields.split("\n") : [];
                }
                return [];
            }
        },
        methods: {
            onChecked() {
                this.$emit('blur');
                this.onInput(this.selected)
            },
            onInput($event) {
                this.$emit('input', $event);
            }
        }
    }
</script>