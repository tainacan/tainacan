<template>
    <div>
        <el-select v-model="checked" @change="onChecked()">
            <el-option
                    v-for="option,index in getOptions"
                    :key="index"
                    v-model="checked"
                    :label="option"
                    :value="item.value"
                    border>{{ option }}</el-option>
        </el-select>
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