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
        listen(){
            const components = this.getAllComponents();
            for (let eventElement of components){
                eventElement.addEventListener('changeValue', (event) => {
                    if ( event.detail[0] ){
                        const promisse = this.$store.dispatch('item/sendMetadata', event.detail[0] );
                        
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
        getErrors(){
            const components = this.getAllComponents();
            for (let eventElement of components){
                for(let error of this.errors){
                    eventElement.errorsMsg =  JSON.stringify( error.error );
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