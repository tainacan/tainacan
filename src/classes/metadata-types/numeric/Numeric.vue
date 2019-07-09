<template>
    <b-input
            :disabled="disabled"
            :class="{'has-content': inputValue !== undefined && inputValue !== ''}"
            :id="id"
            lang="en"
            type="number"
            :value="inputValue"
            step="0.01"
            @blur="onBlur"
            @change="onBlur"
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
                inputValue: ''
            }
        },
        props: {
            id: '',
            metadatum: {
                type: Object
            },
            value: [String, Number, Array],
            disabled: false,
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
