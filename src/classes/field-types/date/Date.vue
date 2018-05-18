<template>
    <div>
        <div
                :class="{'has-content': dateValue !== undefined && dateValue !== ''}"
                class="control is-inline">
            <input
                    class="input"
                    type="date"
                    v-mask="dateMask"
                    v-model="dateValue"
                    @blur="onBlur"
                    @input="onInput">
            <!--<b-collapse-->
                    <!--position="is-bottom-right">-->
                <!--<b-icon-->
                        <!--icon="calendar-today"-->
                        <!--size="is-small"-->
                        <!--slot="trigger" />-->

                <!--<div class="field">-->
                    <!--<b-datepicker-->
                            <!--:class="{'has-content': dateValue !== undefined && dateValue !== ''}"-->
                            <!--:id="id"-->
                            <!--v-model="dateValue"-->
                            <!--@blur="onBlur"-->
                            <!--:readonly="false"-->
                            <!--inline-->
                            <!--@input="onInput($event)"-->
                            <!--:placeholder="datePlaceHolder"/>-->
                <!--</div>-->
            <!--</b-collapse>-->
        </div>
    </div>
</template>

<script>
    import { dateInter } from "../../../admin/js/mixins";

    export default {
        mixins: [ dateInter ],
        created(){
            if( this.value ){
                this.dateValue = new Date(this.value.replace(/-/g, '/')).toLocaleDateString();
            }
        },
        data() {
            return {
                dateValue: '',
                datePlaceHolder: new Date().toLocaleDateString(),
                dateMask: this.getDateLocaleFormat(),
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
                let dateISO = '';

                if($event && $event instanceof Date) {
                    dateISO = this.dateValue.toISOString().split('T')[0]
                } else if($event.target.value && $event.target.value.length === this.dateMask.length) {
                    dateISO = new Date($event.target.value).toISOString().split('T')[0];
                }

                this.$emit('input', dateISO);
                this.$emit('blur');
            }
        }
    }
</script>
