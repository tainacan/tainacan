<template>
    <div>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-filter-date', 'comparators') }}<span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-filter-date', 'comparators')"
                        :message="$i18n.getHelperMessage('tainacan-filter-date', 'comparators')" />
            </label>
            <div>
                <b-checkbox
                        v-for="(comparatorObject, comparatorKey) in comparatorsObject"
                        :key="comparatorKey"
                        v-model="comparators"
                        :native-value="comparatorKey"
                        :disabled="comparators.indexOf(comparatorKey) >= 0 && comparators.length <= 1"
                        name="date_filter_options[comparators]"
                        @update:model-value="emitValues()">
                    <span v-html="comparatorObject.symbol + '&nbsp;' + comparatorObject.label" />
                </b-checkbox>
            </div>
        </b-field>
    </div>
</template>

<script>

    export default {
        props: {
            modelValue: Object
        },
        emits: [
            'update:model-value',
        ],
        data() {
            return {
                comparatorsObject: Object,
                comparators: Array
            }
        },
        created() {
            this.comparators = ( this.modelValue && this.modelValue.comparators ) ? this.modelValue.comparators : [ '=', '!=', '>', '>=', '<', '<=' ];
            this.comparatorsObject = {
                '=': {
                    symbol: '&#61;',
                    label: this.$i18n.get('is_equal_to'),
                    enabled: this.comparators.indexOf('=') < 0 ? 'no' : 'yes'
                },
                '!=': {
                    symbol: '&#8800;',
                    label: this.$i18n.get('is_not_equal_to'),
                    enabled: this.comparators.indexOf('!=') < 0 ? 'no' : 'yes'
                },
                '>': {
                    symbol: '&#62;',
                    label: this.$i18n.get('greater_than'),
                    enabled: this.comparators.indexOf('>') < 0 ? 'no' : 'yes'
                },
                '>=': {
                    symbol: '&#8805;',
                    label: this.$i18n.get('greater_than_or_equal_to'),
                    enabled: this.comparators.indexOf('>=') < 0 ? 'no' : 'yes'
                },
                '<': {
                    symbol: '&#60;',
                    label: this.$i18n.get('less_than'),
                    enabled: this.comparators.indexOf('<') < 0 ? 'no' : 'yes'
                },
                '<=': {
                    symbol: '&#8804;',
                    label: this.$i18n.get('less_than_or_equal_to'),
                    enabled: this.comparators.indexOf('<=') < 0 ? 'no' : 'yes'
                }
            };
        },
        methods: {
            emitValues() {
                this.$emit('update:model-value', { comparators: this.comparators });
            }
        }
    }
</script>