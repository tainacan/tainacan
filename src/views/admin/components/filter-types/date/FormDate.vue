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
                        name="metadata_type_relationship[display_related_item_metadata]"
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
                        label: this.$i18n.get('after'),
                        enabled: this.comparators.indexOf('>') < 0 ? 'no' : 'yes'
                    },
                    '>=': {
                        symbol: '&#8805;',
                        label: this.$i18n.get('after_or_on_day'),
                        enabled: this.comparators.indexOf('>=') < 0 ? 'no' : 'yes'
                    },
                    '<': {
                        symbol: '&#60;',
                        label: this.$i18n.get('before'),
                        enabled: this.comparators.indexOf('<') < 0 ? 'no' : 'yes'
                    },
                    '<=': {
                        symbol: '&#8804;',
                        label: this.$i18n.get('before_or_on_day'),
                        enabled: this.comparators.indexOf('<=') < 0 ? 'no' : 'yes'
                    }
                };
        },
        methods: {
            emitValues() {
                console.log(this.comparators)
                this.$emit('update:model-value', { comparators: this.comparators });
            }
        }
    }
</script>