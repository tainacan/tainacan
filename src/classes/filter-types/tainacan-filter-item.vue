<template>
    <b-field
            :message="getErrorMessage"
            :type="filterTypeMessage">
        <b-collapse :open="opened">
            <label slot="trigger">
                <b-icon
                        icon="menu-down"
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
            opened: false,
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