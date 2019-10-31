<template>
    <b-input
            :disabled="disabled"
            :id="metadatum.metadatum.metadata_type_object.component + '-' + metadatum.metadatum.slug"
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
            metadatum: Object,
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
