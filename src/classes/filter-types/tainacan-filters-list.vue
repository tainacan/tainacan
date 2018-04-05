<template>
    <b-field 
            :label="filter.name"
            :message="getErrorMessage"
            :type="filterTypeMessage">
        <div>
            <component
                    :id="filter.filter_type_object.component + '-' + filter.slug"
                    :is="filter.filter_type_object.component"
                    :filter="getFilter"
                    :query="query"
                    @input="listen( $event )"/>
        </div>
    </b-field>
</template>

<script>
    import { eventSearchBus } from '../../js/event-search-bus'
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'TainacanFiltersList',
        props: {
            filter: Object,
            query: Object
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
                let errors = eventSearchBus.getErrors( this.filter.id );
                if ( errors) {
                    this.setFilterTypeMessage('is-danger');
                    for (let index in errors) {
                        msg += errors[index] + '\n';
                    }
                } else {
                    this.setFilterTypeMessage('');
                }
                return msg;
            },
            getFilter(){
                return this.filter;
            }

        },
        methods: {
            ...mapActions('search', [
                'setPage'
            ]),
            ...mapGetters('search', [
                'getPostQuery'
            ]),
            listen( event ){
                eventSearchBus.$emit( 'input', ( event.field_id ) ?  event :  event.detail[0] );
            },
            setFilterTypeMessage( message ){
                this.filterTypeMessage = message;
            }
        }
    }
</script>