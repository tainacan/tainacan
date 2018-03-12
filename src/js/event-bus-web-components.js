import Vue from 'vue';
import store from './store/store'

export const eventBus = new Vue({
    store,
    data: {
        componentsTag: [],
        errors : []
    },
    created(){
        if( tainacan_plugin.components ){
            this.componentsTag = tainacan_plugin.components;
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
                        this.updateValue({ 
                            item_id: $(eventElement).attr("item_id"), 
                            field_id: $(eventElement).attr("field_id"), 
                            values: event.detail
                        })                    
                    }
                });
            }
        },
        updateValue(data){
            if ( data.item_id ){
                let values = ( Array.isArray( data.values[0] ) ) ? data.values[0] : data.values ;
                const promisse = this.$store.dispatch('item/updateMetadata',
                    { item_id: data.item_id, field_id: data.field_id, values: values });
                promisse.then( response => {
                    let index = this.errors.findIndex( errorItem => errorItem.field_id === data.field_id );
                    if ( index >= 0){
                        this.errors.splice( index, 1);
                    }
                }, error => {
                    let index = this.errors.findIndex( errorItem => errorItem.field_id === data.field_id );
                    let messages = null;

                    for (let index in error) {
                        messages = error[index]
                    }

                    if ( index >= 0){
                        Vue.set( this.errors, index, { field_id: data.field_id, errors: messages });
                    }else{
                        this.errors.push( { field_id: data.field_id, errors: messages } );
                    }
                });
            }
        },
        getErrors(field_id){
            let error = this.errors.find( errorItem => errorItem.field_id === field_id );
            return ( error ) ? error.errors : false
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
        },
    }

});