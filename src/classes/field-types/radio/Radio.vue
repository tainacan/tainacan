<template>
    <div>
        <el-radio
                v-for="option,index in getOptions"
                :key="index"
                v-model="checked"
                @change="onChecked(option)"
                :label="option"
                border>{{ option }}</el-radio>
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
            }
        },
        computed: {
            getOptions(){
                if (this.field) {
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