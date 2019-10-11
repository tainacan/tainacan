<template>
    <div>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-filter-date', 'type') }}<span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-filter-date', 'type')"
                        :message="$i18n.getHelperMessage('tainacan-filter-date', 'type')"/>
            </label>
            <b-select
                    name="type_options"
                    v-model="type"
                    @input="onUpdateType">
                <option value="day">{{ $i18n.get('label_day') }}</option>
                <option value="month">{{ $i18n.get('label_month') }}</option>
                <option value="year">{{ $i18n.get('label_year') }}</option>
            </b-select>
        </b-field>
    </div>
</template>

<script>

    export default {
        props: {
            filter: {
                type: Object
            },
            value: [String, Number, Array],
            disabled: false,
        },
        data() {
            return {
                type: [String]
            }
        },
        methods: {
            onUpdateType(type) {
                this.$emit('input', { type: type });
            },
        }
    }
</script>