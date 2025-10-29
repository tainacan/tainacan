<template>
    <b-field
            :ref="isMobileScreen ? ('filter-field-id-' + filter.id) : null"
            class="filter-item-forms"
            :style="{ columnSpan: filtersAsModal && filter.filter_type_object && filter.filter_type_object.component && (filter.filter_type_object.component == 'tainacan-filter-taxonomy-checkbox' || filter.filter_type_object.component == 'tainacan-filter-checkbox') ? 'all' : 'unset'}"
            @touchstart="setFilterFocus(filter.id)"
            @mousedown="setFilterFocus(filter.id)">
        <b-collapse
                v-if="!hideCollapses && displayFilter"
                v-model="singleCollapseOpen" 
                class="show"
                animation="filter-item">
            <template #trigger="props">
                <button
                        :id="'filter-label-id-' + filter.id"
                        :for="'filter-input-id-' + filter.id"
                        :aria-controls="'filter-input-id-' + filter.id"
                        :aria-expanded="singleCollapseOpen"
                        :aria-label="filter.name"
                        class="label">
                    <span class="icon">
                        <i 
                                :class="{
                                    'tainacan-icon-arrowdown' : props && props.open,
                                    'tainacan-icon-arrowright' : props && !props.open
                                }"
                                class="tainacan-icon tainacan-icon-1-25em" />
                    </span>
                    <span 
                            class="collapse-label">
                        {{ filter.name }}
                    </span>
                    <help-button
                            v-if="filter.description_bellow_name !== 'yes' && filter.description" 
                            :title="filter.name"
                            :message="filter.description" />
                </button>
            </template>
            <div :id="'filter-input-id-' + filter.id">
                <p 
                        v-if="filter.description_bellow_name === 'yes' && filter.description"
                        class="filter-description-help-info">
                    {{ filter.description }}
                </p>
                <component
                        :is="filter.filter_type_object ? filter.filter_type_object.component : null"
                        :filter="filter"
                        :query="query"
                        :is-using-elastic-search="isUsingElasticSearch"
                        :is-repository-level="isRepositoryLevel"
                        :is-loading-items="isLoadingItems"
                        :current-collection-id="$eventBusSearch.collectionId"
                        :filters-as-modal="filtersAsModal"
                        @input="onInput" 
                        @update-parent-collapse="onFilterUpdateParentCollapse" />
            </div>
        </b-collapse>
        <div 
                v-if="!hideCollapses && beginWithFilterCollapsed && !displayFilter"
                class="collapse show disabled-filter">
            <div class="collapse-trigger">
                <button
                        :for="'filter-input-id-' + filter.id"
                        class="label"
                        @click="displayFilter = true">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-1-25em" />
                    </span>
                    <span 
                            v-tooltip="{
                                delay: {
                                    show: 500,
                                    hide: 300,
                                },
                                content: $i18n.get('instruction_click_to_load_filter'),
                                html: false,
                                autoHide: false,
                                placement: 'top-start',
                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                            }"        
                            class="collapse-label">
                        {{ filter.name }}
                    </span>
                    <help-button
                            v-if="filter.description_bellow_name !== 'yes' && filter.description" 
                            :title="filter.name"
                            :message="filter.description" />
                </button>
            </div>
        </div>
        <template v-if="hideCollapses">
            <label 
                    :for="'filter-input-id-' + filter.id"
                    class="label">
                <span 
                        class="collapse-label">
                    {{ filter.name }}
                </span>
                <help-button
                        v-if="filter.description_bellow_name !== 'yes' && filter.description" 
                        :title="filter.name"
                        :message="filter.description" />
            </label>
            <div :id="'filter-input-id-' + filter.id">
                <p 
                        v-if="filter.description_bellow_name === 'yes' && filter.description"
                        class="filter-description-help-info">
                    {{ filter.description }}
                </p>
                <component
                        :is="filter.filter_type_object ? filter.filter_type_object.component : null"
                        :filter="filter"
                        :query="query"
                        :is-using-elastic-search="isUsingElasticSearch"
                        :is-repository-level="isRepositoryLevel"
                        :is-loading-items="isLoadingItems"
                        :current-collection-id="$eventBusSearch.collectionId"
                        :filters-as-modal="filtersAsModal"
                        @input="onInput" 
                        @update-parent-collapse="onFilterUpdateParentCollapse" />
            </div>
        </template>
    </b-field>
</template>

<script>
    import { defineAsyncComponent } from 'vue';

    export default {
        name: 'TainacanFilterItem',
         components: {
            TainacanFilterNumeric: defineAsyncComponent(() => import('./numeric/TainacanFilterNumeric.vue')),
            TainacanFilterDate: defineAsyncComponent(() => import('./date/TainacanFilterDate.vue')),
            TainacanFilterSelectbox: defineAsyncComponent(() => import('./selectbox/TainacanFilterSelectbox.vue')),
            TainacanFilterAutocomplete: defineAsyncComponent(() => import('./autocomplete/TainacanFilterAutocomplete.vue')),
            TainacanFilterCheckbox: defineAsyncComponent(() => import('./checkbox/TainacanFilterCheckbox.vue')),
            TainacanFilterTaginput: defineAsyncComponent(() => import('./taginput/TainacanFilterTaginput.vue')),
            TainacanFilterTaxonomyCheckbox: defineAsyncComponent(() => import('./taxonomy/TainacanFilterCheckbox.vue')),
            TainacanFilterTaxonomyTaginput: defineAsyncComponent(() => import('./taxonomy/TainacanFilterTaginput.vue')),
            TainacanFilterTaxonomySelectbox: defineAsyncComponent(() => import('./taxonomy/TainacanFilterSelectbox.vue')),
            TainacanFilterDateInterval: defineAsyncComponent(() => import('./date-interval/TainacanFilterDateInterval.vue')),
            TainacanFilterDatesIntersection: defineAsyncComponent(() => import('./dates-intersection/TainacanFilterDatesIntersection.vue')),
            TainacanFilterNumericInterval: defineAsyncComponent(() => import('./numeric-interval/TainacanFilterNumericInterval.vue')),
            TainacanFilterNumericListInterval: defineAsyncComponent(() => import('./numeric-list-interval/TainacanFilterNumericListInterval.vue')),
            TainacanFilterNumericsIntersection: defineAsyncComponent(() => import('./numerics-intersection/TainacanFilterNumericsIntersection.vue'))
        },
        props: {
            filter: Object,
            query: Object,
            isRepositoryLevel: Boolean,
            expandAll: true,
            isLoadingItems: true,
            filtersAsModal: Boolean,
            isMobileScreen: false,
            hideCollapses: false
        },
        data() {
            return {
                isUsingElasticSearch: tainacan_plugin.wp_elasticpress == "1" ? true : false,
                displayFilter: false,
                singleCollapseOpen: this.expandAll,
                focusedElement: false
            }
        },
        computed: {
            beginWithFilterCollapsed() {
                return this.filter && this.filter.begin_with_filter_collapsed && this.filter.begin_with_filter_collapsed === 'yes';
            }
        },
        watch: {
            expandAll() {
                this.singleCollapseOpen = this.expandAll;
                if ( this.expandAll )
                    this.displayFilter = true;
            },
            beginWithFilterCollapsed: {
                handler() {
                    this.displayFilter = !this.beginWithFilterCollapsed;
                },
                immediate: true
            }
        },
        methods: {
            onInput(inputEvent) {
                this.$eventBusSearchEmitter.emit('input', inputEvent);
            },
            onFilterUpdateParentCollapse(open) {
                const componentsThatShouldCollapseIfEmpty = ['tainacan-filter-taxonomy-checkbox', 'tainacan-filter-selectbox', 'tainacan-filter-checkbox'];
                if (componentsThatShouldCollapseIfEmpty.includes(this.filter.filter_type_object.component))
                    this.singleCollapseOpen = open;
            },
            setFilterFocus(filterId) {
                if (this.isMobileScreen) {
                    let fieldElement = this.$refs['filter-field-id-' + filterId] && this.$refs['filter-field-id-' + filterId]['$el'];
                    if (this.focusedElement !== filterId && fieldElement && (typeof fieldElement.scrollIntoView == 'function')) {
                        this.focusedElement = filterId;
                        fieldElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        })
                    }
                }
            }
        }
    }
</script>

<style lang="scss">

    .filter-item-forms {
        break-inside: avoid;

        &:not(:last-child) {
            margin-bottom: 0;
            padding-bottom: 0.25em;
        }

        .collapse-trigger {
            margin-left: -8px;
            button {
                background-color: inherit !important;
                color: inherit !important;
            }
            .icon {
                margin-right: 5px;
            }
            .collapse-label {
                display: inline;
                width: 100%;
                overflow-x: hidden;
                text-overflow: ellipsis;
                line-height: 1.4em;
            }
        }
        .disabled-filter {
            opacity: 0.75;
        }
        .collapse-content {
            margin-top: 12px;
        }
        .columns{
            margin-left: 0px;
            margin-right: 0px;
        }
        .column {
            padding: 0.75em 1px 0.75em 0 !important;
        }
        & > .label {
            display: block !important;
            width: 100%;
            overflow-x: hidden;
            text-overflow: ellipsis;
            line-height: 1.4em !important;
            border: none;
            background-color: transparent;
            color: var(--tainacan-label-color);
            text-align: left;
            outline: none;
            padding: 0 !important;
            margin: 0 0 8px 0;

            .tainacan-help-tooltip-trigger {
                font-size: 1.188em;

                .icon {
                    margin-right: 0px;
                    margin-left: 6px;
                }
            }
        }
        .collapse {
            .label {
                display: inline-flex;
                align-items: center;
                border: none;
                background-color: transparent;
                color: var(--tainacan-label-color);
                text-align: left;
                cursor: pointer;
                outline: none;
                padding: 0 !important;
                margin: 0;

                .tainacan-help-tooltip-trigger {
                    font-size: 1.188em;

                    .icon {
                        margin-right: 0px;
                        margin-left: 6px;
                    }
                }
            }
        }

        .label {
            font-weight: normal !important;
            font-size: 0.875em;
            width: 100%;
        }

        .filter-description-help-info {
            font-size: 0.75em;
            color: var(--tainacan-info-color);
            margin-top: -0.25em;
            margin-bottom: 0.75em;
        }

        .taginput-container {
            border-radius: var(--tainacan-input-border-radius, 2px) !important;
            box-shadow: none !important;
            transition: background-color 0.1s;
        }

        .taginput-container {
            .tags {
                display: none !important;
            }
        }

        .input {
            overflow: hidden;
            display: unset;
            white-space: nowrap;
            text-overflow: ellipsis;
            height: auto;
        }

        .control:not(.taginput) {
            .tags {
                .tag:not(a.is-delete) {
                    display: unset;
                    overflow: hidden;
                    white-space: nowrap;
                    text-overflow: ellipsis;
                    border-radius: 0;
                    width: 86%;
                }

                .is-delete {
                    border-radius: 0;
                }
            }
        }

        .tag {
            height: 2em !important;
        }
        
        .b-checkbox.checkbox  {
            font-weight: normal;
            font-size: 1em !important;
            margin-right: 2px;

            @media screen and (max-width: 768px) {
                font-size: 1.125em !important;

                .control-label {
                    padding-top: 0.55em;
                    padding-bottom: 0.55em;
                }
            }
        }

        .datepicker {
            .dropdown-menu {
                right: 0;
                min-width: calc(100% + 18px) !important;
            }
            @media screen and (min-width: 769px) {
                .dropdown-trigger input {
                    font-size: 0.75em !important;
                    line-height: 1.875em;
                }
                .datepicker-header {

                    .dropdown-menu {
                        max-width: 165px !important;
                    }
                    .pagination .pagination-list .control {
                        width: 50% !important; 

                        .select {
                            min-width: 100% !important;    
 
                            select {
                                padding-left: 1px !important;
                                min-width: 100% !important;
                            }
                            &:not(.is-loading)::after {
                                font-size: 1em;
                            }
                        }
                    }
                }
                .datepicker-cell {
                    padding: 0.15em 0.175em !important;
                }
            }
        }
    }
</style>