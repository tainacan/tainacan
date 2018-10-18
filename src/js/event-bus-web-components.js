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
        this.$on('input', this.updateValue );
    },
    watch: {
        errors() {
            this.$emit('hasErrorsOnForm', this.errors.length > 0);
        }
    },
    methods : {
        registerComponent( name ){
            if (this.componentsTag.indexOf(name) < 0) {
                this.componentsTag.push( name );
            }
        },
        // listener(){
        //     const components = this.getAllComponents();
        //     for (let eventElement of components){
        //         eventElement.addEventListener('input', (event) => {

        //             if (event.detail && event.detail[0] ){
        //                 this.updateValue({
        //                     item_id: $(eventElement).attr("item_id"),
        //                     metadatum_id: $(eventElement).attr("metadatum_id"),
        //                     values: event.detail
        //                 })
        //             }
        //         });
        //     }
        // }, 
        updateValue(data){
            
            this.$emit('isUpdatingValue', true);

            if ( data.item_id ){

                if(data.values.length > 0 && data.values[0].value){
                    let val = [];
                    for(let i of data.values){
                        val.push(i.value);
                    }

                    data.values = val;
                }  

                let values = ( Array.isArray( data.values[0] ) ) ? data.values[0] : data.values ;
                const promisse = this.$store.dispatch('item/updateMetadata',
                    { item_id: data.item_id, metadatum_id: data.metadatum_id, values: values });

                promisse.then( () => {
                    this.$emit('isUpdatingValue', false);
                    let index = this.errors.findIndex( errorItem => errorItem.metadatum_id == data.metadatum_id );
                    if ( index >= 0){
                        this.errors.splice( index, 1);
                    }
                })
                .catch((error) => {
                    this.$emit('isUpdatingValue', false);
                    let index = this.errors.findIndex( errorItem => errorItem.metadatum_id == data.metadatum_id );
                    let messages = [];

                    for (let index in error) {
                        messages.push(error[index]);
                    }

                    if ( index >= 0){
                        Vue.set( this.errors, index, { metadatum_id: data.metadatum_id, errors: messages });
                    } else {
                        this.errors.push( { metadatum_id: data.metadatum_id, errors: messages } );
                    }
                });
            }
        },
        getErrors(metadatum_id) {
            let error = this.errors.find( errorItem => errorItem.metadatum_id == metadatum_id );
            return ( error ) ? error.errors : false
        },
        clearAllErrors(){
           this.errors = [];
        },
        setValues(){
            const metadatum = this.$store.getters['item/getMetadata'];
            if( metadatum ){
                for(let singleMetadata of metadatum){
                    const eventElement = this.getComponentById( singleMetadata.metadatum_id );
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
        getComponentById( metadatum_id ){
            for( let component of this.componentsTag ){
                const eventElements = document.getElementsByTagName( component );
                if( eventElements ) {
                    for (let eventElement of eventElements){
                        if( eventElement.metadatum_id === metadatum_id ){
                            return eventElement;
                        }
                    }
                }
            }
        }
    },
    beforeUpdate() {
        this.$off('input', this.updateValue );
    }

});
