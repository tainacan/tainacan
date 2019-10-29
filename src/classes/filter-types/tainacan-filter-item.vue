<template>
    <b-field
            class="filter-item-forms">
        <b-collapse
                class="show" 
                :open.sync="open"
                animation="filter-item">
            <button
                    :for="'filter-input-id-' + filter.id"
                    :aria-controls="'filter-input-id-' + filter.id"
                    :aria-expanded="open"
                    v-tooltip="{
                        delay: {
                            show: 500,
                            hide: 300,
                        },
                        content: filter.name,
                        html: false,
                        autoHide: false,
                        placement: 'top-start'
                    }"
                    :id="'filter-label-id-' + filter.id"
                    :aria-label="filter.name"
                    class="label"
                    slot="trigger"
                    slot-scope="props">
                <span class="icon">
                    <i 
                            :class="{ 'tainacan-icon-arrowdown' : props.open, 'tainacan-icon-arrowright' : !props.open }"
                            class="tainacan-icon tainacan-icon-20px"/>
                </span>
                <span class="collapse-label">{{ filter.name }}</span>
            </button>
            <div :id="'filter-input-id-' + filter.id">
                <component
                        :is="filter.filter_type_object.component"
                        :filter="filter"
                        :query="query"
                        :is-repository-level="isRepositoryLevel"
                        :is-loading-items.sync="isLoadingItems"
                        @input="onInput"
                        @sendValuesToTags="onSendValuesToTags"/>
            </div>
        </b-collapse>
    </b-field>
</template>

<script>
    export default {
        name: 'TainacanFilterItem',
        props: {
            filter: Object,
            query: Object,
            isRepositoryLevel: Boolean,
            open: true
        },
        data() {
            return {
                isLoadingItems: Boolean,
                isUsingElasticSearch: tainacan_plugin.wp_elasticpress == "1" ? true : false
            }
        },
        mounted() {
            if (this.isUsingElasticSearch) {
                this.$eventBusSearch.$on('isLoadingItems', isLoadingItems => {
                    this.isLoadingOptions = isLoadingItems;
                });
            }
        },
        methods: {
            onInput(inputEvent) {
                this.$eventBusSearch.$emit('input', inputEvent);
            },
            onSendValuesToTags($event) {
                this.$eventBusSearch.$emit('sendValuesToTags', { 
                    filterId: this.filter.id,
                    label: $event.label,
                    value: $event.value,
                    taxonomy: $event.taxonomy,
                    metadatumId: this.filter.metadatum_id
                });
            }
        },    
        beforeDestroy() {
            if (this.isUsingElasticSearch)
                this.$eventBusSearch.$off('isLoadingItems');
        }
    }
</script>

<style lang="scss">
    @import "../../../src/admin/scss/_variables.scss";

    .filter-item-forms {

        .collapse-trigger {
            margin-left: -5px;
            .icon {
                margin-right: 12px;
            }
            .collapse-label {
                display: inline-block;
                width: 100%;
                overflow-x: hidden;
                text-overflow: ellipsis;
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
            padding: 0.75rem 1px 0.75rem 0 !important;
        }

        .collapse {
            .label {
                display: inline-flex;
                align-items: center;
                border: none;
                background-color: transparent;
                text-align: left;
                cursor: pointer;
                outline: none;
                padding: 0 !important;
            }
        }

        .label {
            font-weight: normal !important;
            font-size: 0.875rem;
            width: 100%;
        }

        .taginput-container {
            font-size: 0.875rem;
            border-radius: 1px !important;
            box-shadow: none !important;
            transition: background-color 0.1s;
            height: 2.25em !important;
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
                    border: 1px solid $gray4 !important;
                }
            }    
            input{
                border: 1px solid $gray2 !important;
            }
            .control.has-icons-left .icon {
                top: 3px !important;
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
            height: 30px;
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
            font-size: 0.75rem;
            margin-right: 2px;
        }

        .datepicker {
            @media screen and (min-width: 768px) {

                .datepicker-header {

                    .dropdown-menu {
                        max-width: 165px !important;
                    }
                    .pagination .pagination-list .control {
                        width: 77px !important; 

                        .select {
                            min-width: 100% !important;    

                            select {
                                padding-left: 1px !important;
                                font-size: 0.75rem !important;
                                height: 24px !important;
                                min-width: 100% !important;
                            }
                            &:not(.is-loading)::after {
                                margin-top: -13px !important;
                            }
                        }
                    }
                }
                .datepicker-cell {
                    padding: 0.15rem 0.175rem !important;
                }
            }
        }
    }
</style>