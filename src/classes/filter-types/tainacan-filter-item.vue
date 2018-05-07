<template>
    <b-field
            id="filter-item-forms"
            :message="getErrorMessage"
            :type="filterTypeMessage">
        <b-collapse :open="open">
            <label
                    slot="trigger"
                    slot-scope="props">
                <b-icon
                        :icon="props.open ? 'menu-down' : 'menu-right'"
                        size="is-small" />
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
            display: inline-block;
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

        .autocomplete {
            .dropdown-content {
                position: fixed !important;
            }
        }

        .b-checkbox.checkbox  {
            font-weight: normal;
            font-size: 12px;
        }

    }
</style>