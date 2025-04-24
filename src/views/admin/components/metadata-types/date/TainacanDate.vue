<template>
    <div class="control is-clearfix">
        <input
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                v-imask="{ mask: dateMask, skipInvalid: false }"
                :value="dateValue"
                :disabled="disabled"
                class="input"
                :class="isInvalidDate ? 'is-danger' : ''"
                type="text"
                :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : dateFormat.toLowerCase()"
                @input="onInput"
                @blur="onBlur"
                @focus="onMobileSpecialFocus">
        <p
                v-if="isInvalidDate"
                style="font-size: 0.75em;"
                class="has-text-danger is-italic">
            {{ $i18n.get('info_error_invalid_date') }}
        </p>
        <p
                v-if="isSmallerThanMin"
                style="font-size: 0.75em;"
                class="has-text-danger is-italic">
            {{ $i18n.getWithVariables('info_error_date_smaller_than_min_%s', [ getMinDateString ] ) }}
        </p>
        <p
                v-if="isGreaterThanMax"
                style="font-size: 0.75em;"
                class="has-text-danger is-italic">
            {{ $i18n.getWithVariables('info_error_date_greater_than_max_%s', [ getMaxDateString ] ) }}
        </p>
    </div>
</template>

<script>
    import { dateInter } from "../../../js/mixins";
    import moment from 'moment';
    import { IMaskDirective } from 'vue-imask';

    export default {
        directives: {
            imask: IMaskDirective
        },
        mixins: [ dateInter ],
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: false,
        },
        emits: [
            'update:value',
            'blur',
            'mobile-special-focus'
        ],
        data() {
            return {
                dateValue: '',
                isInvalidDate: false,
                isSmallerThanMin: false,
                isGreaterThanMax: false,
            }
        },
        computed: {
            getMin() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.min !== null && this.itemMetadatum.metadatum.metadata_type_options.min !== undefined && this.itemMetadatum.metadatum.metadata_type_options.min !== '')
                    return new Date(this.itemMetadatum.metadatum.metadata_type_options.min);
                else
                    return undefined;
            },
            getMax() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.max !== null && this.itemMetadatum.metadatum.metadata_type_options.max !== undefined && this.itemMetadatum.metadatum.metadata_type_options.max !== '')
                    return new Date(this.itemMetadatum.metadatum.metadata_type_options.max);
                else
                    return undefined;
            },
            getMinDateString() {
                return moment(this.getMin).format(this.dateFormat);
            },
            getMaxDateString() {
                return moment(this.getMax).format(this.dateFormat);
            }
        },
        watch: {
            value(newValue) {
                this.dateValue = newValue ? this.parseDateToNavigatorLanguage(newValue) : '';
            }
        },
        created() {
            this.dateValue = this.value ? this.parseDateToNavigatorLanguage(this.value) : '';
        },
        methods: {
            onInput: _.debounce(function ($event) {

                // Empty dates don't need to be validated, they remove the metadata
                if ( $event.target.value == '' ) {
                    this.$emit('update:value', ''); 

                    this.isInvalidDate = false;
                    this.isSmallerThanMin = false;
                    this.isGreaterThanMax = false;

                    return;
                }

                let dateISO = '';
                
                if ($event && $event instanceof Date)
                    dateISO = moment(this.dateValue, this.dateFormat).toISOString(true) ? moment(this.dateValue, this.dateFormat).toISOString(true).split('T')[0] : false;
                else if ($event.target.value && $event.target.value.length === this.dateFormat.length)
                    dateISO = moment($event.target.value, this.dateFormat).toISOString(true) ? moment($event.target.value, this.dateFormat).toISOString(true).split('T')[0] : false;
                
                // Checks if the date is valid
                if ( dateISO == false ) {
                    
                    this.dateValue = $event.target.value; // Keep wrong version in the input so user can fix it

                    this.isInvalidDate = true;
                    this.isSmallerThanMin = false;
                    this.isGreaterThanMax = false;
                
                    return;
                }
                
                // Checks if the date is smaller than the minimum date
                if ( this.getMin && moment(dateISO).isBefore(moment(this.getMin, this.dateFormat)) ) {

                    this.dateValue = $event.target.value; // Keep wrong version in the input so user can fix it

                    this.isInvalidDate = false;
                    this.isSmallerThanMin = true;
                    this.isGreaterThanMax = false;

                    return;
                }

                // Checks if the date is greater than the maximum date
                if ( this.getMax && moment(dateISO).isAfter(moment(this.getMax, this.dateFormat)) ) {

                    this.dateValue = $event.target.value; // Keep wrong version in the input so user can fix it

                    this.isInvalidDate = false;
                    this.isSmallerThanMin = false;
                    this.isGreaterThanMax = true;

                    return;
                }
                
                // Date is valid and within the range
                this.$emit('update:value', dateISO);
                    
                this.isInvalidDate = false;
                this.isSmallerThanMin = false;
                this.isGreaterThanMax = false;

            }, 750),
            onBlur() {
                this.$emit('blur');
            },
            onMobileSpecialFocus() {
                this.$emit('mobile-special-focus');
            }
        }
    }
</script>

<style scoped>
    .input:placeholder-shown.is-danger {
        background-color: var(--tainacan-input-background-color);
        border: 1px solid var(--tainacan-input-border-color);
    }
</style>