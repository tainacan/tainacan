<template>
    <div :class="{ 'is-flex': metadatum.metadatum.multiple != 'yes' || maxtags != undefined }">
        <b-taginput
                :disabled="disabled"
                :id="id"
                v-model="selected"
                :data="options"
                :maxtags="maxtags != undefined ? maxtags : (metadatum.metadatum.multiple == 'yes' || allowNew === true ? 100 : 1)"
                autocomplete
                attached
                :loading="loading"
                :class="{'has-selected': selected != undefined && selected != []}"
                field="label"
                @typing="search"/>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios'
    import qs from 'qs';

    export default {
        created(){
            let collectionId = ( this.metadatum && this.metadatum.metadatum.metadata_type_options.collection_id ) ? this.metadatum.metadatum.metadata_type_options.collection_id : this.collection_id;
            if( this.metadatum.value && (Array.isArray( this.metadatum.value ) ? this.metadatum.value.length > 0 : true )){
                let query = qs.stringify({ postin: ( Array.isArray( this.metadatum.value ) ) ? this.metadatum.value : [ this.metadatum.value ]  });
                axios.get('/collection/'+collectionId+'/items?' + query + '&nopaging=1')
                    .then( res => {
                        for (let item of res.data) {
                            this.selected.push({ label: item.title, value: item.id, img: '' });
                        }
                    })
                    .catch(error => {
                        this.$console.log(error);
                    });
            }

            if( this.metadatum.metadatum.metadata_type_options &&
                this.metadatum.metadatum.metadata_type_options.search &&
                this.metadatum.metadatum.metadata_type_options.search.length > 0){
                axios.get('/collection/'+ collectionId +'/metadata?context=edit')
                    .then( res => {
                        for (let item of res.data) {
                            if( this.metadatum.metadatum.metadata_type_options.search.indexOf( item.id ) >= 0 )
                                this.searchMetadata.push( item );
                        }
                    })
                    .catch(error => {
                        this.$console.log(error);
                    });
            }
        },
        data(){
            return {
                results:'',
                selected:[],
                options: [],
                loading: false,
                collectionId: 0,
                inputValue: null,
                searchMetadata: [],
                queryObject: {},
                itemsFound: []
            }
        },
        props: {
            metadatum: {
                type: Object
            },
            collection_id: {
                type: Number
            },
            id: '',
            maxtags: undefined,
            disabled: false,
            allowNew: true,
        },
        watch: {
            selected( value ){
                this.selected = value;
                let values = [];
                if( this.selected.length > 0 ){
                    for(let val of this.selected){
                        values.push( val.value );
                    }
                }
                this.onInput( values );
            }
        },
        methods: {
            setResults(option){
                if(!option)
                    return;
                this.results = option.value;
            },
            onInput( $event ) {
                this.$emit('input', $event);
                this.$emit('blur');
            },
            search(query){
                if( this.selected.length > 0  && this.metadatum.metadatum.multiple === 'no'){
                    return '';
                }

                if (query !== '') {
                    this.loading = true;
                    this.options = [];
                    
                    let metaquery = this.mountQuery( query );
                    let collectionId = ( this.metadatum && this.metadatum.metadatum.metadata_type_options.collection_id ) ? this.metadatum.metadatum.metadata_type_options.collection_id : this.collection_id;
                    
                    axios.get('/collection/'+collectionId+'/items?' + qs.stringify( metaquery ))
                    .then( res => {
                        this.loading = false;
                        this.options = [];
                        let result = res.data;
                        for (let item of result) {
                            this.options.push({ label: item.title, value: item.id })
                        }
                    })
                    .catch(error => {
                        this.$console.log(error);
                    });
                } else {
                    this.options = [];
                }
            },
            mountQuery( search ){
                let query = []
                if( this.searchMetadata.length > 0){
                    query['metaquery'] = [];
                    const metaquery = query['metaquery'];
                    metaquery['relation'] = 'OR'
                    for( let index in this.searchMetadata ){
                        metaquery[index] = {
                            key: this.searchMetadata[index].id,
                            value: search
                        }
                    }

                    query['metaquery'] = metaquery;
                } else {
                    query['search'] = search;
                }
                return query;
            }
        }
    }
</script>

<style>
    .help.counter {
        display: none;
    }
    div.is-flex {
        justify-content: flex-start;
    }
</style>
