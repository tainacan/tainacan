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
            value: [ String, Object ]
        },
        created(){
            this.fetchTaxonomies().then( res => {
                if ( this.value ) {
                    this.taxonomy_id = this.value.taxonomy_id;
                }
            });

            if( this.value ) {
                this.allow_new_terms = this.value.allow_new_terms;
            }
        },
        data(){
            return {
                taxonomies: [],
                taxonomy_id: '',
                loading: true,
                allow_new_terms: 'yes'
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
                return ( this.allow_new_terms === 'yes' ) ? 'Yes' : 'No';
            },
            emitValues(){
                this.$emit('input',{
                    taxonomy_id: this.taxonomy_id,
                    allow_new_terms: this.allow_new_terms
                })
            }
        }
    }
</script>