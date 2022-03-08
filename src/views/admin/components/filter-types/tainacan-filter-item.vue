<template>
    <b-field
            class="filter-item-forms"
            :style="{ columnSpan: filtersAsModal && filter.filter_type_object && filter.filter_type_object.component && (filter.filter_type_object.component == 'tainacan-filter-taxonomy-checkbox' || filter.filter_type_object.component == 'tainacan-filter-checkbox') ? 'all' : 'unset'}">
        <b-collapse
                v-if="displayFilter"
                class="show" 
                :open.sync="open"
                animation="filter-item">
            <button
                    :for="'filter-input-id-' + filter.id"
                    :aria-controls="'filter-input-id-' + filter.id"
                    :aria-expanded="open"
                    v-tooltip="{
                        delay: {
                            shown: 500,
                            hide: 300,
                        },
                        content: filter.name,
                        html: false,
                        autoHide: false,
                        placement: 'top-start',
                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                    }"
                    :id="'filter-label-id-' + filter.id"
                    :aria-label="filter.name"
                    class="label"
                    slot="trigger"
                    slot-scope="props">
                <span class="icon">
                    <i 
                            :class="{
                                'tainacan-icon-arrowdown' : props.open,
                                'tainacan-icon-arrowright' : !props.open
                            }"
                            class="tainacan-icon tainacan-icon-1-25em"/>
                </span>
                <span class="collapse-label">{{ filter.name }}</span>
            </button>
            <div :id="'filter-input-id-' + filter.id">
                <component
                        :is="filter.filter_type_object ? filter.filter_type_object.component : null"
                        :filter="filter"
                        :query="query"
                        :is-using-elastic-search="isUsingElasticSearch"
                        :is-repository-level="isRepositoryLevel"
                        :is-loading-items.sync="isLoadingItems"
                        :current-collection-id="$eventBusSearch.collectionId"
                        @input="onInput"
                        @updateParentCollapse="onFilterUpdateParentCollapse" 
                        :filters-as-modal="filtersAsModal"/>
            </div>
        </b-collapse>
        <div 
                v-if="beginWithFilterCollapsed && !displayFilter"
                class="collapse show">
            <div class="collapse-trigger">
                <button
                        
                        :for="'filter-input-id-' + filter.id"
                        :aria-controls="'filter-input-id-' + filter.id"
                        v-tooltip="{
                            delay: {
                                shown: 500,
                                hide: 300,
                            },
                            content: $i18n.get('instruction_click_to_load_filter'),
                            html: false,
                            autoHide: false,
                            placement: 'top-start',
                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                        }"
                        @click="displayFilter = true"
                        class="label">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-add tainacan-icon-1em"/>
                    </span>
                    <span class="collapse-label">{{ filter.name }}</span>
                </button>
            </div>
        </div>
    </b-field>
</template>

<script>
    export default {
        name: 'TainacanFilterItem',
        props: {
            filter: Object,
            query: Object,
            isRepositoryLevel: Boolean,
            open: true,
            isLoadingItems: true,
            filtersAsModal: Boolean
        },
        data() {
            return {
                isUsingElasticSearch: tainacan_plugin.wp_elasticpress == "1" ? true : false,
                displayFilter: false
            }
        },
        computed: {
            beginWithFilterCollapsed() {
                return this.filter && this.filter.begin_with_filter_collapsed && this.filter.begin_with_filter_collapsed === 'yes';
            }
        },
        watch: {
            beginWithFilterCollapsed: {
                handler() {
                    this.displayFilter = !this.beginWithFilterCollapsed;
                },
                immediate: true
            }
        },
        methods: {
            onInput(inputEvent) {
                this.$eventBusSearch.$emit('input', inputEvent);
            },
            onFilterUpdateParentCollapse(open) {
                const componentsThatShouldCollapseIfEmpty = ['tainacan-filter-taxonomy-checkbox', 'tainacan-filter-selectbox', 'tainacan-filter-checkbox'];
                if (componentsThatShouldCollapseIfEmpty.includes(this.filter.filter_type_object.component))
                    this.open = open;
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
            }
        }

        .label {
            font-weight: normal !important;
            font-size: 0.875em;
            width: 100%;
        }

        .taginput-container {
            border-radius: 1px !important;
            box-shadow: none !important;
            transition: background-color 0.1s;
        }

        .taginput-container {
            border: none !important;
            &.is-focused, 
            &.is-focused:active, 
            &.is-focused:focus, 
            &.is-focusable,
            &.is-focusable:active 
            &.is-focusable:focus  {
                border: none !important;
                input:active, input:focus {
                    border: 1px solid var(--tainacan-input-border-color) !important;
                }
            }    
            input{
                border: 1px solid var(--tainacan-input-border-color) !important;
            }
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
                left: -18px;
                right: 0;
            }
            @media screen and (min-width: 768px) {
                .dropdown-trigger input {
                    font-size: 0.75em !important;
                    line-height: 1.75em;
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