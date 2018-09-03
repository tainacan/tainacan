<template>
    <div>
        <div 
                v-for="(option, index) in options"
                :key="index">
            <b-radio
                    :disabled="disabled"
                    :id="id"
                    :style="{ paddingLeft: (option.level * 30) + 'px' }"
                    :key="index"
                    v-model="checked"
                    @input="onChecked(option)"
                    :native-value="option.id"
                    border>
                {{ option.name }}
            </b-radio>
            <br>
        </div>
    </div>
</template>

<script>

    export default {
        data(){
            return {
                checked: ( this.value ) ? this.value : ''
            }
        },
        watch: {
            value( val ){
                this.checked = val;
            }
        },
        props: {
            options: {
                type: Array
            },
            value: [ Number, String, Array ],
            disabled: false,
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