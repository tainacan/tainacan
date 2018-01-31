<template>
    <el-input-number
              controls-position="right"
              :value="inputValue"
              @blur="onBlur"
              @change="onBlur"
              @input="onInput($event)"></el-input-number>
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
