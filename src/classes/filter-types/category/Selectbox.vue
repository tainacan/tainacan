<template>
    <div class="block">
        <b-select
                :id = "id"
                :loading = "isLoading"
                v-model = "selected"
                @input = "onSelect()"
                size="is-small"
                expanded>
            <option value="">{{ $i18n.get('label_selectbox_init') }}...</option>
            <option
                    v-for=" (option, index) in options"
                    :key="index"
                    :label="option.name"
                    :value="option.id">{{ option.name }}</option>
        </b-select>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id ;
            this.type = ( this.filter_type ) ? this.filter_type : this.filter.metadatum.metadata_type;
            this.loadOptions();
        },
        data(){
            return {
                isLoading: false,
                options: [],
                collection: '',
                metadatum: '',
                selected: '',
                taxonomy: ''
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes metadatum id and type
            },
            metadatum_id: [Number], // not required, but overrides the filter metadatum id if is set
            collection_id: [Number], // not required, but overrides the filter metadatum id if is set
            filter_type: [String],  // not required, but overrides the filter metadatum type if is set
            id: '',
            query: {
                type: Object // concentrate all attributes metadatum id and type
            }
        },
        watch: {
            selected: function(val){
                this.selected = val;
                this.onSelect();
            }
        },
        methods: {
            getValuesCategory( taxonomy ){
                return axios.get('/taxonomy/' + taxonomy + '/terms?hideempty=0' ).then( res => {
                    for (let item of res.data) {
                        this.taxonomy = item.taxonomy;
                        this.options.push(item);
                    }
                })
                    .catch(error => {
                        this.$console.log(error);
                    });
            },
            loadOptions(){
                let promise = null;
                this.isLoading = true;

                axios.get('/collection/'+ this.collection +'/metadata/' + this.metadatum)
                    .then( res => {
                        let metadatum = res.data;
                        promise = this.getValuesCategory( metadatum.metadata_type_options.taxonomy_id );

                        promise.then( () => {
                            this.isLoading = false;
                            this.selectedValues();
                        })
                            .catch( error => {
                                this.$console.log('error select', error );
                                this.isLoading = false;
                            });
                    })
                    .catch(error => {
                        this.$console.log(error);
                    });
            },
            getOptions( parent, level = 0 ){ // retrieve only ids
                let result = [];
                if ( this.options ){
                    for( let term of this.options ){
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
            selectedValues(){
                if ( !this.query || !this.query.taxquery || !Array.isArray( this.query.taxquery ) )
                    return false;

                let index = this.query.taxquery.findIndex(newMetadatum => newMetadatum.taxonomy === this.taxonomy );
                if ( index >= 0){
                    let metadata = this.query.taxquery[ index ];
                    this.selected = metadata.terms;
                } else {
                    return false;
                }
            },
            onSelect(){
                this.$emit('input', {
                    filter: 'selectbox',
                    compare: 'IN',
                    taxonomy: this.taxonomy,
                    metadatum_id: this.metadatum,
                    collection_id: this.collection,
                    terms: this.selected
                });
            }
        }
    }
</script>
