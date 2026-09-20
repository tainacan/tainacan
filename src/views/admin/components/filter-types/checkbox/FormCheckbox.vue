<template>
    <b-field :addons="false">
        <label class="label is-inline">
            {{ $i18n.getHelperTitle('tainacan-filter-checkbox', 'max_view_more_pages') }}
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-filter-checkbox', 'max_view_more_pages')"
                    :message="$i18n.getHelperMessage('tainacan-filter-checkbox', 'max_view_more_pages')" />
        </label>
        <b-numberinput
                v-model="maxViewMorePages"
                name="max_view_more_pages"
                step="1"
                min="0"
                controls-position="compact"
                controls-alignment="right"
                expanded
                @update:model-value="emitValues()" />
    </b-field>
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
                maxViewMorePages: 0
            }
        },
        created() {
            const raw = this.modelValue && this.modelValue.max_view_more_pages !== undefined
                ? this.modelValue.max_view_more_pages
                : 0;
            const parsed = parseInt(raw, 10);
            this.maxViewMorePages = isNaN(parsed) || parsed < 0 ? 0 : parsed;
        },
        methods: {
            emitValues() {
                const parsed = parseInt(this.maxViewMorePages, 10);
                this.$emit('update:model-value', {
                    max_view_more_pages: isNaN(parsed) || parsed < 0 ? 0 : parsed
                });
            }
        }
    }
</script>
