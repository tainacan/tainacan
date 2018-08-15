<template>
    <div class="block">
        <b-taginput
                size="is-small"
                icon="magnify"
                :allow-new="allowNew"
                @add="emitAdd"
                @remove="emitRemove"
                v-model="selected"
                :data="labels"
                field="label"
                attached
                ellipsis
                :loading="isFetching"
                :class="{'has-selected': selected != undefined && selected != []}"
                autocomplete
                @typing="autoCompleteTerm"/>
    </div>
</template>
<script>

    import { mapActions, mapGetters } from 'vuex';

    export default {
        data(){
            return {
                selected: [],
                labels: [],
                termList: [],
                isFetching: false,
            }
        },
        props: {
            options: {
                type: Array
            },
            value: [ Number, String, Array ],
            allowNew: [ Boolean ],
            taxonomyId: Number,
        },
        created(){
            if(this.value && this.value.length > 0){
                this.selected = this.value;
            }
        },
        methods: {
            ...mapActions('taxonomy', [
                'fetchTerms'
            ]),
            ...mapGetters('taxonomy', [
                'getTerms'
            ]),
            autoCompleteTerm: _.debounce( function(value){
                this.termList = [];
                this.labels = [];
                this.isFetching = true;

                this.fetchTerms({ 
                    taxonomyId: this.taxonomyId,
                    fetchOnly: { 
                        fetch_only: {
                            0: 'name',
                            1: 'id'
                        }
                    },
                    search: { 
                        searchterm: value
                    },
                    all: true
                }).then((res) => {
                    this.termList = res.terms;
                    
                    for(let term of this.termList){
                        this.labels.push({label: term.name, value: term.id});
                    }
                    this.isFetching = false;
                }).catch((error) => {
                    this.isFetching = false;
                    throw error;
                });
            }, 300),
            selectedValues(){
                let selected = [];

                for( let term of this.value){
                    selected.push({label: term.label, value: term.value})
                }

                this.selected = selected;
            },
            emitAdd(){
                let val = this.selected;
                let results = [];

                if(val.length > 0){
                    for( let term of val ){
                        results.push( term.value );
                    }

                    this.$emit('input', results);
                    this.$emit('blur');
                }
            },
            emitRemove(){
                let val = this.selected;
                let results = [];

                for( let term of val ){
                    results.push( term.value );
                }

                this.$emit('input', results);
                this.$emit('blur');
            }
        }
    }
</script>