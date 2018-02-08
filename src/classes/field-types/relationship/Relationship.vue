<template>
    <div>
        <b-autocomplete
                v-model="selected"
                :data="options"
                @input="search"
                :loading="loading"
                field="label"
                @select="option => setResults(option) ">
        </b-autocomplete>
    </div>
</template>

<script>
    import debounce from 'lodash/debounce'
    import axios from '../../../js/axios/axios'

    export default {
        data(){
            return {
                results:'',
                selected:'',
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
            }
        },
        methods: {
            setResults(option){
                if(!option)
                    return;
                this.results = option.value;
                this.onChecked()
            },
            onChecked() {
                this.$emit('blur');
                this.onInput(this.results)
            },
            onInput($event) {
                this.inputValue = $event;
                this.$emit('input', this.inputValue);
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