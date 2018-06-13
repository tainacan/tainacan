<template>
    <div class="block">
        <b-taginput
                size="is-small"
                icon="magnify"
                :allow-new="allowNew"
                @input="emitChange"
                v-model="selected"
                :data="labels"
                field="label"
                attached
                :class="{'has-selected': selected != undefined && selected != []}"
                autocomplete
                @typing="search"/>
    </div>
</template>
<script>
    export default {
        data(){
            return {
                selected: [],
                labels: []
            }
        },
        watch: {
            terms(){
                this.selectedValues();
            }
        },
        props: {
            terms:  [ Number, String, Array ],
            options: {
                type: Array
            },
            value: [ Number, String, Array ],
            allowNew: [ Boolean ]
        },
        methods: {
            search( query ){
                if( this.terms && this.terms.length > 0 ){
                    let result = this.terms.filter( ( item ) => {
                        let name = item.name.toLowerCase();
                        let q = query.toLowerCase();
                        return ( name.indexOf(q) >= 0 )
                    });
                    this.labels = [];
                    for( let term of result){
                        this.labels.push({label: term.name, value: term.id})
                    }
                }
            },
            selectedValues(){
                if( this.value && this.value.length > 0 && this.selected.length === 0){
                    let result = this.terms.filter( ( item ) => {
                        let id = item.id;
                        return ( this.value.indexOf( id ) >= 0 )
                    });

                    let selected = [];
                    for( let term of result){
                        selected.push({label: term.name, value: term.id})
                    }
                    this.selected = selected;
                }
            },
            emitChange(){
                let val = this.selected;
                let results = [];

                for( let term of val ){
                    if( term.value ){
                        results.push( term.value );
                    } else {
                        results.push( term );
                    }
                }

                this.$emit('input', results);
                this.$emit('blur');
            }
        }
    }
</script>