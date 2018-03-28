<template>
    <b-input
            :id="id"
            :value="inputValue"
            @blur="onBlur"
            @input="onInput($event)"/>
</template>

<script>
    export default {
        created(){
            if( this.value ){
                this.inputValue = this.value
            }
        },
        data() {
            return {
                inputValue: '',
            }
        },
        props: {
            field: {
                type: Object
            },
            value: [String, Number, Array],
            id: ''
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