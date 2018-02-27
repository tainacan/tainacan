import Vue from 'vue';

export const eventFilterBus = new Vue({
    data: {
        componentsTag: [],
        errors : [],
        query: {}
    },
    created(){
        this.$on('input', data => this.search(data) );
    },
    methods: {
        search( ){
           console.log( data );
        },

        /* Dev interfaces methods */

        registerComponent( name ){
            if (this.componentsTag.indexOf(name) < 0) {
                this.componentsTag.push( name );
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
        listener(){
            const components = this.getAllComponents();
            for (let eventElement of components){
                eventElement.addEventListener('input', (event) => {
                    console.log( event.detail, 'dev' );
                });
            }
        },
    }
});