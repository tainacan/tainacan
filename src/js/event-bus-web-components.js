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
                eventElement.addEventListener('input', (event) => {
                    if (event.detail && event.detail[0] ){
                        const promisse = this.$store.dispatch('item/updateMetadata',
                            { item_id: $(eventElement).attr("item_id"), field_id: $(eventElement).attr("field_id"), values: event.detail });

                        promisse.then( response => {
                            // eventElement.errorsMsg = JSON.stringify( [] );
                            // eventElement.value = response.value;
                            $(eventElement).val(response.value);
                        }, error => {
                            const field = this.errors.find(error => error.field_id === event.detail[0].field_id );
                            // eventElement.errorsMsg = JSON.stringify( field.error );
                            // eventElement.value = event.detail[0].values;
                        });
                    }
                });
            }
        },
        updateValue(data){
            if ( data.item_id ){
                const promisse = this.$store.dispatch('item/updateMetadata',
                    { item_id: data.item_id, field_id: data.field_id, values: data.values });
                promisse.then( response => {
                    data.instance.message = JSON.stringify( [] );
                    data.instance.value = response.value;
                }, error => {
                    console.log(error);
                    // const field = this.errors.find(error => error.field_id === data.field_id );
                    // eventElement.errorsMsg = JSON.stringify( field.error );
                    // eventElement.value = data.values;
                });
            }
        },
        setValues(){
            const field = this.$store.getters['item/getMetadata'];
            if( field ){
                for(let singleMetadata of field){
                    const eventElement = this.getComponentById( singleMetadata.field_id );
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
            let elements = document.querySelectorAll('[web-component="true"]');
            if( elements ) {
                for (let eventElement of elements){
                    components.push( eventElement );
                }
            }
            return components;
        },
        getComponentById( field_id ){
            for( let component of this.componentsTag ){
                const eventElements = document.getElementsByTagName( component );
                if( eventElements ) {
                    for (let eventElement of eventElements){
                        if( eventElement.field_id === field_id ){
                            return eventElement;
                        }
                    }
                }
            }
        }
    }

});