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
                v-if="getComponent() !== 'tainacan-category-tag-input' && allowNew"
                :taxonomy_id="taxonomy"
                :field="field"
                :item_id="field.item.id"
                :value="valueComponent"
                :options="getOptions(0)"
                @newTerm="reload"/>
    </div>
</template>
<script>
    import { tainacan as axios } from '../../../js/axios/axios'
    import TainacanCategoryRadio from './CategoryRadio.vue'
    import TainacanCategoryCheckbox from './CategoryCheckbox.vue'
    import TainacanCategoryTagInput from './CategoryTaginput.vue'
    import TainacanCategorySelectbox from './CategorySelectbox.vue'
    import AddNewTerm from  './AddNewTerm.vue'

    export default {
        created(){
            let field_type_options = this.field.field.field_type_options;
            this.component = ( field_type_options && field_type_options.input_type )
                ? this.field.field.field_type_options.input_type : this.componentAttribute

            this.collectionId = this.field.field.collection_id;
            this.taxonomy = field_type_options.taxonomy_id;

            if( field_type_options && field_type_options.allow_new_terms ){
                this.allowNew = field_type_options.allow_new_terms === 'yes'
            }
            this.getTermsFromTaxonomy();
            this.getTermsId();
        },
        components: {
            TainacanCategoryRadio,
            TainacanCategoryCheckbox,
            TainacanCategoryTagInput,
            TainacanCategorySelectbox,
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
            field: {
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
                if( this.field.field
                    && this.field.field.field_type_options && this.field.field.field_type_options.input_type ){
                    return this.field.field.field_type_options.input_type;
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

              if( values.length > 0 && this.field.field){
                  this.valueComponent = (  this.field.field && this.field.field.multiple === 'no' ) ? values[0] : values
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