<template>
    <el-input :value="inputValue" 
    @blur="onBlur"
    @input="onInput($event)"></el-input>
</template>

<script>
    export default {
        data() {
            return {
                inputValue: ''
            }
        },
        methods: {
            onBlur() {
                this.$emit('blur');
            },
            onInput($event) {
                this.inputValue = $event;
                this.$emit('input', this.inputValue);
            }
        }
    }
</script>

<style scoped>
    .multiple-inputs {
        display: flex;
    }
</style>
