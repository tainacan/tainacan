<template>
    <b-field
            id="filter-item-forms"
            :message="getErrorMessage"
            :type="filterTypeMessage">
        <b-collapse
                class="show" 
                :open="open">
            <label
                    class="label"
                    slot="trigger"
                    slot-scope="props">
                <b-icon
                        :icon="props.open ? 'menu-down' : 'menu-right'"
                        />
                {{ filter.name }}
            </label>

            <div>
                <component
                        :id="filter.filter_type_object.component + '-' + filter.slug"
                        :is="filter.filter_type_object.component"
                        :filter="filter"
                        :query="query"
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
            open: false,
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
            listen( event ){
                this.$eventBusSearch.$emit( 'input', ( event.field_id ) ?  event :  event.detail[0] );
            },
            setFilterTypeMessage( message ){
                this.filterTypeMessage = message;
            }
        }
    }
</script>

<style lang="scss">
    @import "../../../src/admin/scss/_variables.scss";

    #filter-item-forms {

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
                        font-size: 12px; 
                    }

                    .pagination-next {
                        margin: 0;
                        height: 24px;
                        padding: 0;
                        font-size: 12px;
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
        }
        .collapse-content {
            margin-top: 12px;
        }

        .column {
            padding: 0.75rem 1px 0.75rem 0 !important;
        }

        .select {
            padding-top: 0px !important;
            select {
                font-size: 14px;
                border: none;
                border-radius: 1px !important;
                font-weight: normal;
                height: 30px !important;
                padding: 2px 25px 2px 15px !important;
                margin-top: 0px !important;
                margin-bottom: 0px !important;
                background-color: white;
                color: black;
                &:focus > option:checked, &:focus > option:hover {
                    background-color: $primary-lighter !important;
                }
            }
            &:not(.is-multiple)::after {
                content: "\F35D" !important;
                font: normal normal normal 24px/1 "Material Design Icons" !important;
                border: none !important;
                transform: none;
                margin-top: -0.6em;
                right: 0.95em;
                color: $primary;
            }
        }

        .label {
            font-weight: normal;
            font-size: 14px;
            display: inline-flex;
        }

        .input, .textarea, .taginput-container {

            font-size: 14px;
            border: none !important;
            border-radius: 1px !important;
            background-color: white;
            color: $tainacan-input-color;
            box-shadow: none !important;
            transition: background-color 0.1s;
            height: 2.25em !important;

            &:focus, &:active {
                box-shadow: none !important;
                border: none !important;
            }
        }

        .taginput-container {
            display: table-cell;
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

        .autocomplete {
            .dropdown-content {
                position: fixed !important;
            }
        }

        .b-checkbox.checkbox  {
            font-weight: normal;
            font-size: 12px;
            margin-right: 2px;
        }

    }
</style>