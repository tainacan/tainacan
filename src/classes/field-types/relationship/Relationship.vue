<template>
    <div>
        <b-taginput
                :id="id"
                v-model="selected"
                :data="options"
                autocomplete
                :loading="loading"
                field="label"
                @typing="search">
        </b-taginput>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios'
    import qs from 'qs';

    export default {
        created(){
            if( this.field.value ){
                let collectionId = ( this.field && this.field.field.field_type_options.collection_id ) ? this.field.field.field_type_options.collection_id : this.collection_id;
                let query = qs.stringify({ postin: ( Array.isArray( this.field.value ) ) ? this.field.value : [ this.field.value ]  });

                axios.get('/collection/'+collectionId+'/items?' + query)
                    .then( res => {
                        for (let item of res.data) {
                            this.selected.push({ label: item.title, value: item.id, img: '' });
                        }
                    })
                    .catch(error => {
                        console.log(error);
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
                inputValue: null
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
                if (query !== '') {
                    this.loading = true;
                    this.options = [];
                    let collectionId = ( this.field && this.field.field.field_type_options.collection_id ) ? this.field.field.field_type_options.collection_id : this.collection_id;
                    axios.get('/collection/'+collectionId+'/items')
                    .then( res => {
                        let result = [];
                        this.loading = false;
                        result = res.data.filter(item => {
                            return item.title.toLowerCase()
                                .indexOf(query.toLowerCase()) > -1;
                        });

                        for (let item of result) {
                            this.options.push({ label: item.title, value: item.id })
                        }
                    })
                    .catch(error => {
                        console.log(error);
                    });
                } else {
                    this.options = [];
                }
            }
        }
    }
</script>