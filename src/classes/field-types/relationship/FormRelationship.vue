<template>
    <section>
        <b-field
                :label="$i18n.get('label_collection_related')"
                :listen="setError"
                :type="collectionType"
                :message="collectionMessage">
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
                class="loading-spinner"></div>
            <b-field
                    v-if="hasFields"
                    :label="$i18n.get('label_fields_for_search')">
                <div class="block">
                    <div
                        v-for="option in fields"
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


        <b-field :label="$i18n.get('label_allow_repeated_items')">
            <div class="block">
                <b-switch v-model="modelRepeated"
                          type="is-primary"
                          @input="emitValues()"
                          true-value="yes"
                          false-value="no">
                    {{ labelRepeated()  }}
                </b-switch>
            </div>
        </b-field>
    </section>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';
    import Vue from 'vue';

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
                }
            },
            modelSearch( value ){
                this.modelSearch = value;
                this.emitValues();
            }
        },
        created(){
           this.fetchCollections().then( data => {
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
                    this.collectionType = 'is-warning';
                    this.collectionMessage = this.errors.collection_id;
                } else {
                    this.collectionType = '';
                    this.collectionMessage = '';
                }
            },
        },
        methods:{
            fetchCollections(){
                return axios.get('/collections')
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
                        console.log(error);
                        reject(error);
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
                    })
                    .catch((error) => {
                        this.hasFields = false;
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
                })
            }
        }
    }
</script>