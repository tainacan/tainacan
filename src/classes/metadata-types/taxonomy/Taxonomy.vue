<template>
    <div>
        <component
                :is="getComponent()"
                v-model="valueComponent"
                :allow-new="allowNew"
                :terms="terms"
                :options="getOptions(0)"/>
        <add-new-term
                class="add-new-term"
                v-if="getComponent() !== 'tainacan-taxonomy-tag-input' && allowNew"
                :taxonomy_id="taxonomy"
                :metadatum="metadatum"
                :item_id="metadatum.item.id"
                :value="valueComponent"
                :options="getOptions(0)"
                @newTerm="reload"/>
    </div>
</template>
<script>
    import { tainacan as axios } from '../../../js/axios/axios'
    import TainacanTaxonomyRadio from './TaxonomyRadio.vue'
    import TainacanTaxonomyCheckbox from './TaxonomyCheckbox.vue'
    import TainacanTaxonomyTagInput from './TaxonomyTaginput.vue'
    import TainacanTaxonomySelectbox from './TaxonomySelectbox.vue'
    import AddNewTerm from  './AddNewTerm.vue'

    export default {
        created(){
            let metadata_type_options = this.metadatum.metadatum.metadata_type_options;
            this.component = ( metadata_type_options && metadata_type_options.input_type )
                ? this.metadatum.metadatum.metadata_type_options.input_type : this.componentAttribute

            this.collectionId = this.metadatum.metadatum.collection_id;
            this.taxonomy = metadata_type_options.taxonomy_id;

            if( metadata_type_options && metadata_type_options.allow_new_terms ){
                this.allowNew = metadata_type_options.allow_new_terms === 'yes'
            }
            this.getTermsFromTaxonomy();
            this.getTermsId();
        },
        components: {
            TainacanTaxonomyRadio,
            TainacanTaxonomyCheckbox,
            TainacanTaxonomyTagInput,
            TainacanTaxonomySelectbox,
            AddNewTerm
        },
        data(){
            return {
                valueComponent: null,
                component: '',
                collectionId: '',
                taxonomy: '',
                terms:[], // object with names
                allowNew: false
            }
        },
        watch: {
            valueComponent( val ){
                this.valueComponent = val;
                this.$emit('input', val);
                this.$emit('blur');
            }
        },
        props: {
            metadatum: {
                type: Object
            },
            componentAttribute: {
                type: String
            },
            value: [ Number, String, Array,Object ],
            id: ''
        },
        methods: {
            getComponent(){
                if( this.metadatum.metadatum
                    && this.metadatum.metadatum.metadata_type_options && this.metadatum.metadatum.metadata_type_options.input_type ){
                    return this.metadatum.metadatum.metadata_type_options.input_type;
                }
            },
            getTermsFromTaxonomy(){
                axios.get('/taxonomy/' + this.taxonomy + '/terms?hideempty=0&order=asc' ).then( res => {
                    for (let item of res.data) {
                        this.terms.push( item );
                    }
                })
                .catch(error => {
                    this.$console.log(error);
                });
            },
            getOptions( parent, level = 0 ){ // retrieve only ids
                let result = [];
                if ( this.terms ){
                    for( let term of this.terms ){
                        if( term.parent == parent ){
                            term['level'] = level;
                            result.push( term );
                            const levelTerm =  level + 1;
                            const children =  this.getOptions( term.id, levelTerm);
                            result = result.concat( children );
                        }
                    }
                }
                return result;
            },
            getTermsId(){
              let values = [];
              if( this.value && this.value.length > 0){
                  for( let term of this.value ){
                      if( term && term.id)
                        values.push(term.id);
                  }
              }

              if( values.length > 0 && this.metadatum.metadatum){
                  this.valueComponent = (  this.metadatum.metadatum && this.metadatum.metadatum.multiple === 'no' ) ? values[0] : values
              }

            },
            onInput($event) {
                this.inputValue = $event;
                this.$emit('input', this.inputValue);
                this.$emit('blur');
            },
            reload( val ){
                this.valueComponent = val;

                this.terms = [];
                this.getTermsFromTaxonomy();
                this.getTermsId();
            }
        }
    }
</script>

<style scoped>
    .add-new-term{
        margin-top: 15px;
        margin-bottom: 30px;
    }
</style>