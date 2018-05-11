<template>
    <div>
        <b-taginput
                :id="id"
                v-model="selected"
                :data="options"
                :maxtags="field.field.multiple === 'yes' ? 100 : 1"
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
            let collectionId = ( this.field && this.field.field.field_type_options.collection_id ) ? this.field.field.field_type_options.collection_id : this.collection_id;
            if( this.field.value ){
                let query = qs.stringify({ postin: ( Array.isArray( this.field.value ) ) ? this.field.value : [ this.field.value ]  });

                axios.get('/collection/'+collectionId+'/items?' + query + '?nopaging=1')
                    .then( res => {
                        for (let item of res.data) {
                            this.selected.push({ label: item.title, value: item.id, img: '' });
                        }
                    })
                    .catch(error => {
                        this.$console.log(error);
                    });
            }

            if( this.field.field.field_type_options
                    && this.field.field.field_type_options.search.length > 0){
                axios.get('/collection/'+ collectionId +'/fields?context=edit')
                    .then( res => {
                        for (let item of res.data) {
                            if( this.field.field.field_type_options.search.indexOf( item.id ) >= 0 )
                                this.searchFields.push( item );
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
                searchFields: [],
                queryObject: {},
                itemsFound: []
            }
        },
        props: {
            field: {
                type: Object
            },
            collection_id: {
                type: Number
            },
            id: ''
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
                if( this.selected.length > 0  && this.field.field.multiple === 'no'){
                    return '';
                }

                if (query !== '') {
                    let metaquery = this.mountQuery( query );
                    this.loading = true;
                    this.options = [];
                    let collectionId = ( this.field && this.field.field.field_type_options.collection_id ) ? this.field.field.field_type_options.collection_id : this.collection_id;
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
                if( this.searchFields.length > 0){
                    query['metaquery'] = [];
                    const metaquery = query['metaquery'];
                    metaquery['relation'] = 'OR'
                    for( let index in this.searchFields ){
                        metaquery[index] = {
                            key: this.searchFields[index].id,
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
</style>
