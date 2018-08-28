<template>
    <div class="block">
        <b-taginput
                :disabled="disabled"
                size="is-small"
                icon="magnify"
                :allow-new="allowNew"
                :maxtags="!allowNew ? 1 : ''"
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
            allowNew: true,
            taxonomyId: Number,
            disabled: false,
            allowSelectToCreate: false,
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

                    if(this.termList.length <= 0 && this.allowSelectToCreate){
                        this.labels.push({label: `${value} (${this.$i18n.get('select_to_create')})`, value: value})
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