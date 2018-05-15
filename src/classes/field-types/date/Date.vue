<template>
    <b-datepicker
            :class="{'has-content': dateValue != undefined && dateValue != ''}"
            :id="id"
            v-model="dateValue"
            @blur="onBlur"
            :readonly="false"
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
                dateValue: ''
            }
        },
        props: {
            id: '',
            field: {
                type: Object
            },
            value: [String, Number, Array],
        },
        methods: {
            onBlur() {
                this.$emit('blur');
            },
            onInput($event) {
                this.dateValue = $event;
                let date_init = this.dateValue.getUTCFullYear() + '-' +
                    ('00' + (this.dateValue.getUTCMonth() + 1)).slice(-2) + '-' +
                    ('00' + this.dateValue.getUTCDate()).slice(-2);
                this.$emit('input', date_init);
                this.$emit('blur');
            }
        }
    }
</script>
