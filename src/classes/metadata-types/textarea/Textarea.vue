<template>
    <b-input
            :disabled="disabled"
            :class="{'has-content': inputValue !== undefined && inputValue !== ''}"
            :id="id"
            size="is-small"
            type="textarea"
            :value="inputValue"
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
            disabled: false
        },
        methods: {
            onInput($event) {
                this.inputValue = $event;
                this.$emit('input', this.inputValue);
            }
        }
    }
</script>
