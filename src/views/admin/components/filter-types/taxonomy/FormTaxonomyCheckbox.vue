<template>
    <div>
        <b-field
                :addons="false"
                :type="fieldMessage('max_view_more_pages') ? 'is-danger' : ''"
                :message="fieldMessage('max_view_more_pages')">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-filter-taxonomy-checkbox', 'max_view_more_pages') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-filter-taxonomy-checkbox', 'max_view_more_pages')"
                        :message="$i18n.getHelperMessage('tainacan-filter-taxonomy-checkbox', 'max_view_more_pages')" />
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
        <b-field
                :addons="false"
                :type="fieldMessage('show_hierarchy_path') ? 'is-danger' : ''"
                :message="fieldMessage('show_hierarchy_path')">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-filter-taxonomy-checkbox', 'show_hierarchy_path') }}
                <b-switch
                        v-model="showHierarchyPath"
                        name="show_hierarchy_path"
                        size="is-small"
                        true-value="yes"
                        false-value="no"
                        @update:model-value="emitValues()" />
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-filter-taxonomy-checkbox', 'show_hierarchy_path')"
                        :message="$i18n.getHelperMessage('tainacan-filter-taxonomy-checkbox', 'show_hierarchy_path')" />
            </label>
        </b-field>
    </div>
</template>

<script>
    export default {
        props: {
            modelValue: Object,
            errors: [ String, Object, Array ]
        },
        emits: [
            'update:model-value',
        ],
        data() {
            return {
                maxViewMorePages: 0,
                showHierarchyPath: 'no'
            }
        },
        created() {
            const raw = this.modelValue && this.modelValue.max_view_more_pages !== undefined
                ? this.modelValue.max_view_more_pages
                : 0;
            const parsed = parseInt(raw, 10);
            this.maxViewMorePages = isNaN(parsed) || parsed < 0 ? 0 : parsed;
            this.showHierarchyPath = this.modelValue && this.modelValue.show_hierarchy_path === 'yes' ? 'yes' : 'no';
        },
        methods: {
            fieldMessage(key) {
                const message = this.errors && this.errors[key];
                return typeof message === 'string' ? message : '';
            },
            emitValues() {
                const parsed = parseInt(this.maxViewMorePages, 10);
                this.$emit('update:model-value', {
                    max_view_more_pages: isNaN(parsed) || parsed < 0 ? 0 : parsed,
                    show_hierarchy_path: this.showHierarchyPath === 'yes' ? 'yes' : 'no'
                });
            }
        }
    }
</script>
