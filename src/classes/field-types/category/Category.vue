<template>
    <component
            :is="getComponent()"
            :options="terms"></component>
</template>
<script>
    import { tainacan as axios } from '../../../js/axios/axios'
    import TainacanCategoryRadio from './CategoryRadio.vue'

    export default {
        created(){
            this.component = ( this.field.field
                && this.field.field.field_type_options && this.field.field.field_type_options.input_type )
                ? this.field.field.field_type_options.input_type : this.componentAttribute

            this.collectionId = this.field.field.collection_id;
            this.taxonomy = this.field.field.field_type_options.taxonomy_id;
            this.getTermsFromTaxonomy();
        },
        components: {
            TainacanCategoryRadio
        },
        data(){
            return {
                component: '',
                collectionId: '',
                taxonomy: '',
                terms:[]
            }
        },
        props: {
            field: {
                type: Object
            },
            componentAttribute: {
                type: String
            },
            id: ''
        },
        methods: {
            getComponent(){
                if( this.field.field
                    && this.field.field.field_type_options && this.field.field.field_type_options.input_type ){
                    return this.field.field.field_type_options.input_type;
                }
            },
            getTermsFromTaxonomy(){
                axios.get('/taxonomy/' + this.taxonomy + '/terms?hideempty=0' ).then( res => {
                    for (let item of res.data) {
                        this.terms.push( item );
                    }
                })
                .catch(error => {
                    console.log(error);
                });
            }
        }
    }
</script>