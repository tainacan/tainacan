<template>
    <b-field
            class="filter-item-forms"
            :message="getErrorMessage"
            :type="filterTypeMessage">
        <b-collapse
                class="show" 
                :open.sync="open"
                animation="filter-item">
            <label
                    v-tooltip="{
                                    content: filter.name,
                                    html: false,
                                    autoHide: false,
                                    placement: 'top-start'
                                }"
                    class="label"
                    slot="trigger"
                    slot-scope="props">
                <b-icon
                        :icon="props.open ? 'menu-down' : 'menu-right'"
                        />
                <span class="collapse-label">{{ filter.name }}</span>
            </label>

            <div>
                <component
                        :id="filter.filter_type_object.component + '-' + filter.slug"
                        :is="filter.filter_type_object.component"
                        :filter="filter"
                        :query="query"
                        :is-repository-level="isRepositoryLevel"
                        @input="listen( $event )"/>
            </div>
        </b-collapse>
    </b-field>
</template>

<script>
    import { mapActions } from 'vuex';

    export default {
        name: 'TainacanFilterItem',
        props: {
            filter: Object,
            query: Object,
            isRepositoryLevel: Boolean,
            open: true,
        },
        data(){
            return {
                inputs: [],
                filterTypeMessage:''
            }
        },
        computed: {
            getErrorMessage() {
                let msg = '';
                let errors = this.$eventBusSearch.getErrors( this.filter.id );
                if ( errors) {
                    this.setFilterTypeMessage('is-danger');
                    for (let index in errors) {
                        msg += errors[index] + '\n';
                    }
                } else {
                    this.setFilterTypeMessage('');
                }
                return msg;
            }
        },
        methods: {
            ...mapActions('search', [
                'setPage'
            ]),
            listen( inputEvent ){
                this.$eventBusSearch.$emit( 'input', ( inputEvent.metadatum_id ) ?  inputEvent :  inputEvent.detail[0] );
            },
            setFilterTypeMessage( message ){
                this.filterTypeMessage = message;
            }
        }
    }
</script>

<style lang="scss">
    @import "../../../src/admin/scss/_variables.scss";

    .filter-item-forms {

        .datepicker {

            .dropdown-item {
                background-color: white !important;
            }

            @media screen and (min-width: 1024px) {

                .datepicker-header {
                
                    .pagination-list {
                        .field.has-addons {
                            display: table-cell !important;
                            width: 78px !important;

                            .control {
                                height: 24px !important;
                                width: 74px !important;

                                select {
                                    padding-left: 1px !important;
                                }
                            }
                        }
                    }

                    .pagination-previous {
                        margin: 0;
                        height: 24px;
                        padding: 0;
                        font-size: 0.75rem; 
                    }

                    .pagination-next {
                        margin: 0;
                        height: 24px;
                        padding: 0;
                        font-size: 0.75rem;
                    }
                }

                .dropdown-item {
                    padding: 0.8em;
                }

                .dropdown-menu {
                    min-width: 100% !important;
                    max-width: 165px !important;
                }


                .datepicker-table {
                    margin-bottom: 0px;
                    
                    .datepicker-cell {
                        padding: 0.2rem 0.1rem !important;
                    }
                }
                
                .select {
                    select {
                        display: unset;
                        overflow: hidden;
                        white-space: nowrap;
                        text-overflow: ellipsis;
                    }
                }

                .dropdown-content {
                    max-width: 165px !important;
                    border-radius: 2px !important;
                    padding: 0px;
                }
            }
        }

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

    }
</style>