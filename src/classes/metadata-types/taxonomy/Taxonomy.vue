<template>
    <div>
        <component
                :disabled="disabled"
                :is="getComponent"
                :maxtags="maxtags"
                v-model="valueComponent"
                :allow-select-to-create="allowSelectToCreate"
                :allow-new="allowNew"
                :terms="terms"
                :taxonomy-id="taxonomy"
                :options="getOptions(0)"/>
        <a 
                class="add-new-term"
                v-if="(getComponent == 'tainacan-taxonomy-checkbox' || getComponent == 'tainacan-taxonomy-radio') && terms.length < totalTerms"
                @click="getTermsFromTaxonomy()">
            {{ $i18n.get('label_view_more') + ' (' + Number(totalTerms - terms.length) + ' ' + $i18n.get('terms') + ')' }}
        </a>
        <add-new-term
                class="add-new-term"
                v-if="allowNew"
                :component-type="getComponent"
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
                ? this.metadatum.metadatum.metadata_type_options.input_type : this.componentAttribute;

            this.collectionId = this.metadatum.metadatum.collection_id;
            this.taxonomy = metadata_type_options.taxonomy_id;

            if( metadata_type_options && metadata_type_options.allow_new_terms && this.metadatum.item ){
                this.allowNew = metadata_type_options.allow_new_terms == 'yes';
            }

            // This condition is temporary
            if(this.component != 'tainacan-taxonomy-tag-input' || this.forcedComponentType != 'tainacan-taxonomy-tag-input'){
                this.getTermsFromTaxonomy();
            }

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
                totalTerms: 0,
                allowNew: false,
                offset: 0,
                termsNumber: 40
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
            value: [ Number, String, Array, Object ],
            id: '',
            disabled: false,
            forcedComponentType: '',
            maxtags: '',
            allowSelectToCreate: false,
        },
        computed: {
            getComponent() {
                if(this.forcedComponentType){
                   return this.forcedComponentType;
                } else if( this.metadatum.metadatum
                    && this.metadatum.metadatum.metadata_type_options && this.metadatum.metadatum.metadata_type_options.input_type ){
                    return this.metadatum.metadatum.metadata_type_options.input_type;
                    
                }
            }
        },
        methods: {
            getTermsFromTaxonomy(){
                let endpoint = '/taxonomy/' + this.taxonomy + '/terms?hideempty=0&order=asc';

                if (this.getComponent == 'tainacan-taxonomy-checkbox' || this.getComponent == 'tainacan-taxonomy-radio')
                    endpoint = endpoint + '&number=' + this.termsNumber + '&offset=' + this.offset; 

                axios.get(endpoint)
                .then( res => {
                    if (this.getComponent == 'tainacan-taxonomy-checkbox' || this.getComponent == 'tainacan-taxonomy-radio') {
                        this.totalTerms = Number(res.headers['x-wp-total']);
                        this.offset += this.termsNumber;
                    }
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

              if( values.length > 0 && this.metadatum.metadatum && this.component != 'tainacan-taxonomy-tag-input'){
                  this.valueComponent = (  this.metadatum.metadatum && this.metadatum.metadatum.multiple === 'no' ) ? values[0] : values;
              } else if(values.length > 0 && this.metadatum.metadatum && this.component == 'tainacan-taxonomy-tag-input') {
                    let values = [];
                        
                    for(let term of this.value){
                        values.push({label: term.name, value: term.id});
                    }

                    this.valueComponent = values;
              }

            },
            onInput($event) {
                this.inputValue = $event;
                this.$emit('input', this.inputValue);
                this.$emit('blur');
            },
            reload( $event ) {
                if ($event.taxonomyId == this.taxonomy && $event.metadatumId == this.metadatum.metadatum.id) {
                    this.valueComponent = $event.values;
                    this.terms = [];
                    this.offset = 0;
                    this.getTermsFromTaxonomy();
                    this.getTermsId();
                }
            }
        }
    }
</script>

<style scoped>
    .add-new-term{
        margin-top: 15px;
        margin-bottom: 30px;
        font-size: 0.75rem;
    }
</style>