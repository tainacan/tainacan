<template>
    <section>
        <b-field label="Category">
            <b-select
                    name="field_type_options[taxonomy_id]"
                    placeholder="Select the taxonomy"
                    v-model="taxonomy_id"
                    @change.native="emitValues()"
                    :loading="loading">
                <option value="">Select...</option>
                <option
                        v-for="option in taxonomies"
                        :value="option.id"
                        :key="option.id">
                    {{ option.name }}
                </option>
            </b-select>
        </b-field>

        <b-field label="Input Type">

            <b-select
                    v-if="listInputType()"
                    name="field_type_options[component_type]"
                    placeholder="Select the input type for the category field"
                    v-model="input_type">
                <option :value="'tainacan-category-radio'">
                    Radio
                </option>
                <option :value="'tainacan-category-selectbox'">
                    Selectbox
                </option>
            </b-select>

            <b-select
                    name="field_type_options[input_type]"
                    placeholder="Select the input type for the category field"
                    v-model="input_type"
                    v-else>

                <option :value="'tainacan-category-checkbox'">
                    Checkbox
                </option>
                <option :value="'tainacan-category-tag-input'" >
                    Tag Input
                </option>
            </b-select>

        </b-field>

        <b-field label="Allow new Terms">
            <div class="block">
                <b-switch v-model="allow_new_terms"
                          type="is-info"
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
    import axios from '../../../js/axios/axios';

    export default {
        props: {
            value: [ String, Object, Array ],
            field: [ String, Object ]
        },
        created(){
            this.fetchTaxonomies().then( res => {
                if ( this.value ) {
                    this.taxonomy_id = this.value.taxonomy_id;
                }
            });

            if( this.value ) {
                this.allow_new_terms = ( this.value.allow_new_terms ) ? this.value.allow_new_terms : 'no';
                this.input_type = ( this.value.input_type ) ? this.value.input_type : this.listInputType();
            }
        },
        data(){
            return {
                taxonomies: [],
                taxonomy_id: '',
                loading: true,
                allow_new_terms: 'yes',
                input_type: 'tainacan-category-radio'
            }
        },
        methods: {
            listInputType(){
                if( this.field && this.field.multiple === 'no' ){
                    this.input_type =  'tainacan-category-radio';
                    return true;
                } else {
                    this.input_type =  'tainacan-category-checkbox';
                    return false;
                }
            },
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