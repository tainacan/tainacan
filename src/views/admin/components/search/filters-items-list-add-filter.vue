<template>
    <div
            v-if="hasHiddenFilters"
            class="add-filter-container">
        <b-dropdown
                v-a11y-dropdown
                :scrollable="true"
                :max-height="280"
                :mobile-modal="false"
                aria-role="menu"
                trap-focus
                position="is-top-right">
            <template #trigger="{ active }">
                <button
                        type="button"
                        :aria-expanded="active"
                        class="button is-white add-filter-button">
                    <span
                            aria-hidden="true"
                            class="icon">
                        <i class="gray-icon tainacan-icon tainacan-icon-1-125em tainacan-icon-add" />
                    </span>
                    <span class="add-filter-button__text">
                        {{ $i18n.get('label_add_filters') }}
                    </span>
                </button>
            </template>
            <template
                    v-for="(group, groupIndex) in hiddenFilterGroups"
                    :key="'hidden-filter-group-' + groupIndex">
                <template v-if="group.filters.length > 0">
                    <b-dropdown-item
                            v-if="hasMultipleGroups && group.label"
                            custom
                            :focusable="false"
                            :class="['add-filter-group-label']">
                        {{ group.label }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-for="hiddenFilter in group.filters"
                            :key="hiddenFilter.id"
                            aria-role="menuitem"
                            @click="$emit('add-filter', hiddenFilter)"
                            @keydown.enter.prevent="$emit('add-filter', hiddenFilter)"
                            @keydown.space.prevent="$emit('add-filter', hiddenFilter)">
                        {{ hiddenFilter.name }}
                    </b-dropdown-item>
                </template>
            </template>
        </b-dropdown>
    </div>
</template>

<script>
    export default {
        name: 'FiltersItemsListAddFilter',
        props: {
            hiddenFilterGroups: {
                type: Array,
                default: () => []
            }
        },
        emits: [
            'add-filter'
        ],
        computed: {
            hasHiddenFilters() {
                return this.hiddenFilterGroups.some((group) => group.filters && group.filters.length > 0);
            },
            hasMultipleGroups() {
                return this.hiddenFilterGroups.filter((group) => group.filters && group.filters.length > 0).length > 1;
            }
        }
    }
</script>

<style scoped>
    .add-filter-container {
        margin-top: 1em;
    }
    .dropdown .add-filter-button {
        border: none !important;
    }
    .add-filter-button__text {
        margin-inline-end: auto;
    }
    :deep(.dropdown-item.add-filter-group-label),
    :deep(.dropdown-item.add-filter-group-label:hover),
    :deep(.dropdown-item.add-filter-group-label:focus) {
        font-weight: 500;
        --tainacan-secondary: var(--tainacan-info-color);
        --tainacan-dropdownmenu-item-background-hover: var(--tainacan-input-background-color);
    }
</style>
