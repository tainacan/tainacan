<template>
    <section>
        <b-field :addons="false">
            <label 
                    id="metadatum-form-min-label"
                    class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-date', 'min') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-date', 'min')"
                        :message="$i18n.getHelperMessage('tainacan-date', 'min')" />
            </label>
            <b-datepicker
                    v-model="min"
                    aria-labelledby="metadatum-form-min-label"
                    :placeholder="$i18n.get('instruction_select_a_date')"
                    editable
                    position="is-top-left"
                    :trap-focus="false"
                    :date-formatter="(date) => dateFormatter(date)"
                    :date-parser="(date) => dateParser(date)"
                    icon="calendar-today"
                    :years-range="uiConfig.datepicker.yearsRange"
                    :day-names="[
                        $i18n.get('datepicker_short_sunday'),
                        $i18n.get('datepicker_short_monday'),
                        $i18n.get('datepicker_short_tuesday'),
                        $i18n.get('datepicker_short_wednesday'),
                        $i18n.get('datepicker_short_thursday'),
                        $i18n.get('datepicker_short_friday'),
                        $i18n.get('datepicker_short_saturday'),
                    ]"
                    :month-names="[
                        $i18n.get('datepicker_month_january'),
                        $i18n.get('datepicker_month_february'),
                        $i18n.get('datepicker_month_march'),
                        $i18n.get('datepicker_month_april'),
                        $i18n.get('datepicker_month_may'),
                        $i18n.get('datepicker_month_june'),
                        $i18n.get('datepicker_month_july'),
                        $i18n.get('datepicker_month_august'),
                        $i18n.get('datepicker_month_september'),
                        $i18n.get('datepicker_month_october'),
                        $i18n.get('datepicker_month_november'),
                        $i18n.get('datepicker_month_december')
                    ]"
                    @update:model-value="onUpdateMin" />
        </b-field>
        <b-field :addons="false">
            <label 
                    id="metadatum-form-max-label"
                    class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-date', 'max') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-date', 'max')"
                        :message="$i18n.getHelperMessage('tainacan-date', 'max')" />
            </label>
            <b-datepicker
                    v-model="max"
                    aria-labelledby="metadatum-form-max-label"
                    :placeholder="$i18n.get('instruction_select_a_date')"
                    editable
                    position="is-top-left"
                    :trap-focus="false"
                    :date-formatter="(date) => dateFormatter(date)"
                    :date-parser="(date) => dateParser(date)"
                    icon="calendar-today"
                    :years-range="uiConfig.datepicker.yearsRange"
                    :day-names="[
                        $i18n.get('datepicker_short_sunday'),
                        $i18n.get('datepicker_short_monday'),
                        $i18n.get('datepicker_short_tuesday'),
                        $i18n.get('datepicker_short_wednesday'),
                        $i18n.get('datepicker_short_thursday'),
                        $i18n.get('datepicker_short_friday'),
                        $i18n.get('datepicker_short_saturday'),
                    ]"
                    :month-names="[
                        $i18n.get('datepicker_month_january'),
                        $i18n.get('datepicker_month_february'),
                        $i18n.get('datepicker_month_march'),
                        $i18n.get('datepicker_month_april'),
                        $i18n.get('datepicker_month_may'),
                        $i18n.get('datepicker_month_june'),
                        $i18n.get('datepicker_month_july'),
                        $i18n.get('datepicker_month_august'),
                        $i18n.get('datepicker_month_september'),
                        $i18n.get('datepicker_month_october'),
                        $i18n.get('datepicker_month_november'),
                        $i18n.get('datepicker_month_december')
                    ]"
                    @update:model-value="onUpdateMax" />
        </b-field>
    </section>
</template>


<script>
    import { dateInter } from "../../../js/mixins";
    import moment from 'moment';
    import UIConfig from '../../../js/ui-config';

    export default {
        name: 'FormDate',
        mixins: [ dateInter ],
        props: {
            metadatum: Object,
            value: [ String, Object, Array ]
        },
        emits: ['update:value'],
        data() {
            return {
                min: undefined,
                max: undefined,
                uiConfig: UIConfig,
                isTouched: false
            }
        },
        created() {
            this.min = this.value && this.value.min ? new Date(this.value.min) : null;
            this.max = this.value && this.value.max ? new Date(this.value.max) : null;
        },
        methods: {
            onUpdateMin(value) {
                this.$emit('update:value', { min: value, max: this.max });
            },
            onUpdateMax(value) {
                this.$emit('update:value', { min: this.min, max: value });
            },
            dateFormatter(dateObject){ 
                return moment(dateObject, moment.ISO_8601).format(this.dateFormat);
            },
            dateParser(dateString){ 
                return moment(dateString, this.dateFormat).toDate(); 
            },
        }
    }
</script>

<style scoped>
    section{
        margin-bottom: 10px;
    }
    .tainacan-help-tooltip-trigger {
        font-size: 1.25em;
    }
</style>