<template>
    <div class="block">
        <div
            v-for="option,index in getOptions"
            class="field">
            <b-checkbox
                    :id="id"
                    v-model="checked"
                    :native-value="option"
            >{{ option }}</b-checkbox>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                checked:[]
            }
        },
        created(){
            if(this.value instanceof Array)
                this.checked = this.value;
        },
        props: {
            field: {
                type: Object
            },
            options: {
                type: String
            },
            value: [String, Number, Array],
            id: ''
        },
        watch: {
            checked: function(val){
                this.checked = val;
                this.onChecked();
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
            onChecked() {
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