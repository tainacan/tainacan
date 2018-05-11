<template>
    <section>
        <b-field
                :addons="false"
                :listen="setError"
                :type="collectionType"
                :message="collectionMessage">
            <label class="label is-inline">
                {{ $i18n.get('label_collection_related') }}<span :class="collectionType" >&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-relationship', 'collection_id')"
                        :message="$i18n.getHelperMessage('tainacan-relationship', 'collection_id')"/>
            </label>
            <b-select
                    name="field_type_relationship[collection_id]"
                    placeholder="Select the collection to fetch items"
                    v-model="collection"
                    @change.native="emitValues()"
                    @focus="clear()"
                    :loading="loading">
                <option value="">Select...</option>
                <option
                        v-for="option in collections"
                        :value="option.id"
                        :key="option.id">
                    {{ option.name }}
                </option>
            </b-select>
        </b-field>

        <transition name="fade">
            <div
                v-if="loadingFields"
                class="loading-spinner"/>
            <b-field
                    v-if="hasFields"
                    :addons="false">
                <label class="label">
                    {{ $i18n.get('label_fields_for_search') }}
                    <help-button
                            :title="$i18n.getHelperTitle('tainacan-relationship', 'search')"
                            :message="$i18n.getHelperMessage('tainacan-relationship', 'search')"/>
                </label>
                <div class="block">
                    <div
                        v-for="(option, index) in fields"
                        :key="index"
                        class="field">
                        <b-checkbox
                                name="field_type_relationship[search][]"
                                v-model="modelSearch"
                                :native-value="option.id">
                            {{ option.name }}
                        </b-checkbox>
                    </div>
                </div>
            </b-field>

        </transition>


        <b-field :addons="false">
            <label class="label">
                {{ $i18n.get('label_allow_repeated_items') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-relationship', 'repeated')"
                        :message="$i18n.getHelperMessage('tainacan-relationship', 'repeated')"/>
            </label>
            <div class="block">
                <b-checkbox
                        v-model="modelRepeated"
                        @input="emitValues()"
                        true-value="yes"
                        false-value="no">
                    {{ labelRepeated() }}
                </b-checkbox>
            </div>
        </b-field>
    </section>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';

    export default {
        props: {
            search: [ String ],
            collection_id: [ Number ],
            repeated: [ String ],
            value: [ String, Object, Array ],
            field: [ String, Object ],
            errors: [ String, Object, Array ]
        },
        data(){
            return {
                icon: '',
                collections:[],
                fields: [],
                loading: true,
                collection: '',
                hasFields: false,
                loadingFields: false,
                modelRepeated: 'yes',
                modelSearch:[],
                collectionType: '',
                collectionMessage: ''
            }
        },
        watch:{
            collection( value ){
                this.collection = value;
                if( value && value !== '' ) {
                    this.fetchFieldsFromCollection(value);
                } else {
                    this.fields = [];
                    this.hasFields = false;
                    this.modelSearch = [];

                    this.emitValues();
                }
            },
            modelSearch( value ){
                this.modelSearch = value;
                this.emitValues();
            }
        },
        created(){
           this.fetchCollections().then(() => {
               if( this.collection_id && this.collection_id !== '' ){
                   this.collection = this.collection_id;
               } else if ( this.value ) {
                   this.collection = this.value.collection_id;
               }
           });

           if( this.repeated ){
               this.modelRepeated = this.repeated;
           } else if( this.value ) {
               this.modelRepeated = this.value.repeated;
           }
        },
        computed: {
            setError(){
                if( this.errors && this.errors.collection_id !== '' ){
                    this.setErrorsAttributes( 'is-danger', this.errors.collection_id );
                } else {
                    this.setErrorsAttributes( '', '' );
                }
                return true;
            },
        },
        methods:{
            setErrorsAttributes( type, message ){
                this.collectionType = type;
                this.collectionType = message;
            },
            fetchCollections(){
                return axios.get('/collections?nopaging=1')
                    .then(res => {
                        let collections = res.data;
                        this.loading = false;

                        if( collections ){
                            this.collections = collections;
                        } else {
                            this.collections = [];
                        }
                    })
                    .catch(error => {
                        this.$console.log(error);
                    });
            },
            fetchFieldsFromCollection( value ){
                this.loadingFields = true;
                this.hasFields = false;

                axios.get('/collection/' + value + '/fields/')
                    .then((res) => {
                        this.loadingFields = false;
                        let fields = res.data;

                        if( fields.length > 0 ){
                            this.fields = [];

                            for( let field of fields ){
                               if( field.field_type !== "Tainacan\\Field_Types\\Relationship"){
                                   this.fields.push( field );
                                   this.hasFields = true;
                                   this.checkFields()
                               }
                            }

                        } else {
                            this.fields = [];
                            this.hasFields = false;
                            this.$toast.open({
                                duration: 4000,
                                message: this.$i18n.get('info_warning_no_fields_found'),
                                position: 'is-bottom',
                                type: 'is-danger'
                            })
                        }

                        this.emitValues();
                    })
                    .catch(() => {
                        this.hasFields = false;
                        this.emitValues();
                    });

            },
            checkFields(){
                if( this.value && this.value.search.length > 0 ){
                    this.modelSearch = this.value.search;
                } else {
                    try {
                        const json = JSON.parse( this.search );
                        this.modelSearch = json;
                    } catch(e){
                        this.modelSearch = [];
                    }
                }
            },
            labelRepeated(){
                return ( this.modelRepeated === 'yes' ) ? this.$i18n.get('label_yes') : this.$i18n.get('label_no');
            },
            clear(){
                this.collectionType = '';
                this.collectionMessage = '';
            },
            emitValues(){
                this.$emit('input',{
                    collection_id: this.collection,
                    search: this.modelSearch,
                    repeated:  this.modelRepeated
                });
            }
        }
    }
</script>
