<template>
    <section
            v-if="isReady"
            :listen="setError">
        <b-field 
                :addons="false"
                :type="taxonomyType"
                :message="taxonomyMessage">
            <label class="label is-inline">
                {{ $i18n.get('label_select_category') }}<span :class="taxonomyType" >&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-category', 'taxonomy_id')"
                        :message="$i18n.getHelperMessage('tainacan-category', 'taxonomy_id')"/>
            </label>
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

        <b-field :addons="false">
            <label class="label">
                {{ $i18n.get('label_select_category_input_type') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-category', 'input_type')"
                        :message="$i18n.getHelperMessage('tainacan-category', 'input_type')"/>
            </label>
            <b-select
                    v-if="listInputType"
                    name="field_type_options[component_type]"
                    placeholder="Select the input type for the category field"
                    @input="emitValues()"
                    v-model="input_type">
                <option
                        v-for="(option, index) in single_types"
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
                        v-for="(option, index) in multiple_types"
                        :value="index"
                        :key="index">
                    {{ option }}
                </option>
            </b-select>

        </b-field>

        <b-field :addons="false">
            <label class="label">
                {{ $i18n.get('label_category_allow_new_terms') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-category', 'allow_new_terms')"
                        :message="$i18n.getHelperMessage('tainacan-category', 'allow_new_terms')"/>
            </label>
            <div class="block">
                <b-checkbox 
                        v-model="allow_new_terms"
                        @input="emitValues()"
                        true-value="yes"
                        false-value="no">
                    {{ labelNewTerms() }}
                </b-checkbox>
            </div>
        </b-field>

    </section>
</template>

<script>
    import { tainacan as axios }  from '../../../js/axios/axios';
    import BCheckbox from "../../../../node_modules/buefy/src/components/checkbox/Checkbox.vue";

    export default {
        components: {BCheckbox},
        props: {
            value: [ String, Object, Array ],
            field: [ String, Object ],
            errors: [ String, Object, Array ]
        },
        created(){
            this.fetchTaxonomies().then(() => {
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
                    this.setInputType( ( hasValue ) ? this.value.input_type : 'tainacan-category-radio' );
                    return true;
                } else {
                    let types = Object.keys( this.multiple_types );
                    let hasValue = this.value && this.value.input_type && types.indexOf( this.value.input_type ) >= 0;
                    this.setInputType( ( hasValue ) ? this.value.input_type : 'tainacan-category-checkbox' );
                    return false;
                }
            },
            setError(){
                if( this.errors && this.errors.taxonomy_id !== '' ){
                    this.setErrorsAttributes( 'is-danger', this.errors.taxonomy_id );
                } else {
                    this.setErrorsAttributes( '', '' );
                }
                return true;
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
            setInputType( input ){
                this.input_type = input;
            },
            setErrorsAttributes( type, message ){
                this.taxonomyType = type;
                this.taxonomyMessage = message;
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
                        this.$console.log(error);
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