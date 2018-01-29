import Vue from 'vue';
import store from './store/store'

export const eventBus = new Vue({
    store,
    data: {
        componentsTag: [],
        errors : store.getters['item/getError']
    },
    created(){
        if( wp_settings.components ){
            this.componentsTag = wp_settings.components;
        }
        this.$on('input', data => this.updateValue(data) );
    },
    methods : {
        registerComponent( name ){
            if (this.componentsTag.indexOf(name) < 0) {
                this.componentsTag.push( name );
            }
        },
        listener(){
            const components = this.getAllComponents();
            for (let eventElement of components){
                eventElement.addEventListener('changeValues', (event) => {
                    console.log(event,'event')
                    if ( event.detail[0] ){
                        const promisse = this.$store.dispatch('item/updateMetadata', event.detail[0] );
                        promisse.then( response => {
                            eventElement.errorsMsg = JSON.stringify( [] );
                            eventElement.value = response.value;
                        }, error => {
                            const metadata = this.errors.find(error => error.metadata_id === event.detail[0].metadata_id );
                            eventElement.errorsMsg = JSON.stringify( metadata.error );
                            eventElement.value = event.detail[0].values;
                        });
                    }
                });
            }
        },
        updateValue(data){
            if ( data.item_id ){
                const promisse = this.$store.dispatch('item/updateMetadata', data );
                promisse.then( response => {
                    eventElement.errorsMsg = JSON.stringify( [] );
                    eventElement.value = response.value;
                }, error => {
                    const metadata = this.errors.find(error => error.metadata_id === data.metadata_id );
                    eventElement.errorsMsg = JSON.stringify( metadata.error );
                    eventElement.value = data.values;
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
                console.log(eventElements,eventElements.length);
                if( eventElements ) {
                    for (let eventElement of eventElements){
                        components.push( eventElement );
                    }
                }
            }
            let elements = document.querySelectorAll('[web-component="true"]');
            if( elements ) {
                for (let eventElement of elements){
                    components.push( eventElement );
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