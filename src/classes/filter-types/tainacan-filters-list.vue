<template>
    <b-field :label="filter.name"
             :message="getErrorMessage"
             :type="filterTypeMessage">
        <div>
            <component
                    :id="filter.filter_type_object.component + '-' + filter.slug"
                    :is="filter.filter_type_object.component"
                    :filter="getFilter"
                    @input="listen( $event )"></component>
        </div>
    </b-field>
</template>

<script>
    import { eventFilterBus } from '../../js/event-bus-filters'

    export default {
        name: 'TainacanFiltersList',
        props: {
            filter: {}
        },
        created(){
            console.log( this.filter );
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
            listen( event ){
                eventFilterBus.$emit( 'input', ( event.detail ) ? event.detail[0] : event );
            }
        }
    }
</script>