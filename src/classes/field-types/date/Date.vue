<template>
    <el-date-picker
             type="date"
              format="dd/MM/yyyy"
              value-format="dd/MM/yyyy"
              :value="dateValue"
              @blur="onBlur"
              @input="onInput($event)"></el-date-picker>
</template>

<script>
    import lang from 'element-ui/lib/locale/lang/pt-br'
    import locale from 'element-ui/lib/locale'

    locale.use(lang)

    export default {
        data() {
            return {
                dateValue: ''
            }
        },
        methods: {
            onBlur() {
                this.$emit('blur');
            },
            onInput($event) {
                this.dateValue = $event;
                this.$emit('input', this.dateValue);
            }
        }
    }
</script>
