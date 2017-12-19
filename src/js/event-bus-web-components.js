import Vue from 'vue';
import store from './store/store'

export const eventBus = new Vue({
    store,
    data: {
        componentsTag: [],
        errors : store.getters['item/getError']
    },
    methods : {
        registerComponent( name ){
            this.componentsTag.push( name );
        },
        listener(){
            const components = this.getAllComponents();
            for (let eventElement of components){
                eventElement.addEventListener('changeValue', (event) => {
                    if ( event.detail[0] ){
                        const promisse = this.$store.dispatch('item/sendMetadata', event.detail[0] );
                        promisse.then( response => {
                            const parsedResponse = JSON.parse( response );
                            eventElement.errorsMsg = JSON.stringify( [] );
                            eventElement.value = parsedResponse.value;
                        }, error => {
                            const metadata = this.errors.find(error => error.metadata_id === event.detail[0].metadata_id );
                            eventElement.errorsMsg = JSON.stringify( metadata.error );
                            eventElement.value = event.detail[0].values;
                        });
                    }
                });
            }
        },
        setValues(){
            const metadata = this.$store.getters['item/getMetadata'];
            if( metadata ){
                for(let singleMetadata of metadata){
                    const eventElement = this.getComponentById( singleMetadata.metadata_id );
                    eventElement.value =  singleMetadata.values;
                }
            }
        },
        getAllComponents(){
            const components = [];
            for( let component of this.componentsTag ){
                const eventElements = document.getElementsByTagName( component );
                if( eventElements ) {
                    for (let eventElement of eventElements){
                        components.push( eventElement );
                    }
                }
            }
            return components;
        },
        getComponentById( metadata_id ){
            for( let component of this.componentsTag ){
                const eventElements = document.getElementsByTagName( component );
                if( eventElements ) {
                    for (let eventElement of eventElements){
                        if( eventElement.metadata_id === metadata_id ){
                            return eventElement;
                        }
                    }
                }
            }
        }
    }

});