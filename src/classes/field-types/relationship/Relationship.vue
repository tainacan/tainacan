<template>
    <div>
        <b-taginput
                v-if="this.searchFields.length === 0"
                :id="id"
                v-model="selected"
                :data="options"
                autocomplete
                :loading="loading"
                field="label"
                @typing="search"/>
        <div
            v-else>
            <div
                v-if="itemsFound.length === 0"
                class="box">
                <b-field
                        v-for="(field,index) in searchFields"
                        :key="index"
                        :label="field.name">
                    <component
                            @input="setQuery( $event, field )"
                            :is="getComponentSearch( field )"/>
                </b-field>
                <button
                        type="button"

                        @click="doSearch"
                        class="button">
                    <b-icon icon="magnify" />
                    <span> {{ $i18n.get('search') }}</span>
                </button>
            </div>

            <list-found-items
                    class="box"
                    @input="listenSelectedItems"
                    @clearSearch="clearSearch"
                    :items="itemsFound"
                    v-else/>
        </div>

    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios'
    import ListFoundItems from './search-components/list-items.vue';
    import qs from 'qs';

    export default {
        created(){
            let collectionId = ( this.field && this.field.field.field_type_options.collection_id ) ? this.field.field.field_type_options.collection_id : this.collection_id;
            if( this.field.value ){
                let query = qs.stringify({ postin: ( Array.isArray( this.field.value ) ) ? this.field.value : [ this.field.value ]  });

                axios.get('/collection/'+collectionId+'/items?' + query)
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
        components: {
            ListFoundItems
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
                    this.loading = true;
                    this.options = [];
                    let collectionId = ( this.field && this.field.field.field_type_options.collection_id ) ? this.field.field.field_type_options.collection_id : this.collection_id;
                    axios.get('/collection/'+collectionId+'/items?search=' + query)
                    .then( res => {
                        this.loading = false;
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
            getComponentSearch( field ){
                return field.field_type_object.component;
            },
            setQuery( event, field ){
                if( field.field_type.indexOf( 'Core_Title' ) >= 0 ){
                    this.queryObject['search'] = event;
                } else if( field.field_type.indexOf( 'Core_Description' ) >= 0 ){
                    this.queryObject['search'] = event;
                }
            },
            doSearch(){
                let collectionId = ( this.field && this.field.field.field_type_options.collection_id ) ? this.field.field.field_type_options.collection_id : this.collection_id;
                axios.get('/collection/' + collectionId + '/items?' + qs.stringify( this.queryObject ))
                    .then(res => {
                        let items = res.data;
                        if( items.length > 0 ){
                            this.itemsFound = items;
                        }
                    })
                    .catch(error => {
                        this.$console.log( error )
                    });
            },
            clearSearch(){
                this.itemsFound = [];
            },
            listenSelectedItems( event ){
                let results = [];

                if( event && event.length > 0 ){
                    for( let item of event ){
                        results.push( item.id );
                    }
                }

                this.onInput( results );
            }
        }
    }
</script>