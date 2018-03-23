<template>
    <b-field :label="filter.name"
             :message="getErrorMessage"
             :type="filterTypeMessage">
        <div>
            <component
                    :id="filter.filter_type_object.component + '-' + filter.slug"
                    :is="filter.filter_type_object.component"
                    :filter="getFilter"
                    :query="query"
                    @input="listen( $event )"></component>
        </div>
    </b-field>
</template>

<script>
    import { eventFilterBus } from '../../js/event-bus-filters'
    import qs from 'qs';
    import { mapActions, mapGetters } from 'vuex';
    import router from '../../admin/js/router'

    export default {
        name: 'TainacanFiltersList',
        props: {
            filter: {},
            query: {}
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
                let errors = eventFilterBus.getErrors( this.filter.id );
                if ( errors) {
                    this.filterTypeMessage = 'is-danger';
                    for (let index in errors) {
                        msg += errors[index] + '\n';
                    }
                } else {
                    this.filterTypeMessage = '';
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
                this.setPage(1);
                eventFilterBus.$emit( 'input', ( event.field_id ) ?  event :  event.detail[0] );
                router.push({ query: {} });
                router.push({ query: this.getPostQuery() });
            }
        }
    }
</script>