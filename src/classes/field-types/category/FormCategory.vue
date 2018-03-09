<template>
    <section
            v-if="isReady"
            :listen="setError">
        <b-field :label="$i18n.get('label_select_category')"
                 :type="taxonomyType"
                 :message="taxonomyMessage"
        >
            <b-select
                    name="field_type_options[taxonomy_id]"
                    placeholder="Select the taxonomy"
                    v-model="taxonomy_id"
                    @input="emitValues()"
                    @focus="clear"
                    :loading="loading">
                <option value="">{{ $i18n.get('label_selectbox_init') }}...</option>
                <option
                        v-for="option in taxonomies"
                        :value="option.id"
                        :key="option.id">
                    {{ option.name }}
                </option>
            </b-select>
        </b-field>

        <b-field :label="$i18n.get('label_select_category_input_type')">

            <b-select
                    v-if="listInputType"
                    name="field_type_options[component_type]"
                    placeholder="Select the input type for the category field"
                    @input="emitValues()"
                    v-model="input_type">
                <option
                        v-for="option,index in single_types"
                        :value="index"
                        :key="index">
                    {{ option }}
                </option>
            </b-select>

            <b-select
                    name="field_type_options[input_type]"
                    placeholder="Select the input type for the category field"
                    v-model="input_type"
                    @input="emitValues()"
                    v-else>

                <option
                        v-for="option,index in multiple_types"
                        :value="index"
                        :key="index">
                    {{ option }}
                </option>
            </b-select>

        </b-field>

        <b-field :label="$i18n.get('label_category_allow_new_terms')">
            <div class="block">
                <b-switch v-model="allow_new_terms"
                          type="is-primary"
                          @input="emitValues()"
                          true-value="yes"
                          false-value="no">
                    {{ labelNewTerms()  }}
                </b-switch>
            </div>
        </b-field>

    </section>
</template>

<script>
    import { tainacan as axios }  from '../../../js/axios/axios';

    export default {
        props: {
            value: [ String, Object, Array ],
            field: [ String, Object ],
            errors: [ String, Object, Array ]
        },
        created(){
            this.fetchTaxonomies().then( res => {
                if ( this.value ) {
                    this.taxonomy_id = this.value.taxonomy_id;
                }
            });

            if( this.value ) {
                this.allow_new_terms = ( this.value.allow_new_terms ) ? this.value.allow_new_terms : 'no';
            }

            this.single_types['tainacan-category-radio'] = 'Radio';
            this.single_types['tainacan-category-selectbox'] = 'Selectbox';
            this.multiple_types['tainacan-category-tag-input'] = 'Tag Input';
            this.multiple_types['tainacan-category-checkbox'] = 'Checkbox';

            this.isReady = true;
        },
        computed: {
            listInputType(){
                if( this.field && this.field.multiple === 'no' ){
                    let types = Object.keys( this.single_types );
                    let hasValue = this.value && this.value.input_type && types.indexOf( this.value.input_type ) >= 0;
                    this.input_type =  ( hasValue ) ? this.value.input_type : 'tainacan-category-radio';
                    return true;
                } else {
                    let types = Object.keys( this.multiple_types );
                    let hasValue = this.value && this.value.input_type && types.indexOf( this.value.input_type ) >= 0;
                    this.input_type =  ( hasValue ) ? this.value.input_type : 'tainacan-category-checkbox';
                    return false;
                }
            },
            setError(){
                if( this.errors && this.errors.taxonomy_id !== '' ){
                    this.taxonomyType = 'is-warning';
                    this.taxonomyMessage = this.errors.taxonomy_id;
                } else {
                    this.taxonomyType = '';
                    this.taxonomyMessage = '';
                }
            }
        },
        data(){
            return {
                isReady: false,
                taxonomies: [],
                taxonomy_id: '',
                loading: true,
                allow_new_terms: 'yes',
                input_type: 'tainacan-category-radio',
                multiple_types: {},
                single_types: {},
                taxonomyType:'',
                taxonomyMessage: ''
            }
        },
        methods: {
            fetchTaxonomies(){
                return axios.get('/taxonomies')
                    .then(res => {
                        let taxonomies = res.data;
                        this.loading = false;

                        if( taxonomies ){
                            this.taxonomies = taxonomies;
                        } else {
                            this.taxonomies = [];
                        }
                    })
                    .catch(error => {
                        console.log(error);
                        reject(error);
                    });
            },
            labelNewTerms(){
                return ( this.allow_new_terms === 'yes' ) ? this.$i18n.get('label_yes') : this.$i18n.get('label_no');
            },
            clear(){
                this.taxonomyType = '';
                this.taxonomyMessage = '';
            },
            emitValues(){
                this.$emit('input',{
                    taxonomy_id: this.taxonomy_id,
                    input_type: this.input_type,
                    allow_new_terms: this.allow_new_terms
                })
            }
        }
    }
</script>