<template>
    <section>
        <b-field
                label="Collection related"
                :type="type"
                :message="message">
            <b-select
                    name="field_type_relationship[collection_id]"
                    placeholder="Select the collection to fetch items"
                    v-model="collection"
                    @change.native="emitValues()"
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
                    label="Field for search">
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


        <b-field label="Allow repeated items">
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
    import axios from '../../../js/axios/axios';

    export default {
        props: {
            search: [ String ],
            collection_id: [ Number ],
            repeated: [ String ],
            value: [ String, Object ],
            field: [ String, Object ],
        },
        data(){
            return {
                invalid: true,
                message: '',
                type: '',
                collections:[],
                fields: [],
                loading: true,
                collection: '',
                hasFields: false,
                loadingFields: false,
                modelRepeated: 'yes',
                modelSearch:[]
            }
        },
        watch:{
            collection( value ){
                this.collection = value;
                if( value && value !== '' ) {
                    this.message = '';
                    this.type = '';
                    this.invalid = false;
                    this.fetchFieldsFromCollection(value);
                } else {
                    this.invalid = true;
                    this.type = 'is-danger';
                    this.message = 'The field Collection related is required';
                    this.fields = [];
                    this.hasFields = false;
                    this.modelSearch = []
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
               } else if ( this.value && this.value.collection_id ) {
                   this.collection = this.value.collection_id;
               }
           });

           if( this.repeated ){
               this.modelRepeated = this.repeated;
           } else if( this.value ) {
               this.modelRepeated = this.value.repeated;
           }
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
                                message: `No fields found in this collection`,
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
                return ( this.modelRepeated === 'yes' ) ? 'Yes' : 'No';
            },
            emitValues(){
                this.$emit('input',{
                    collection_id: this.collection,
                    search: this.modelSearch,
                    repeated:  this.modelRepeated,
                    invalid: this.invalid
                })
            }
        }
    }
</script>