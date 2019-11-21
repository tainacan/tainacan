<template>
    <div class="block">
        <b-taginput
                expanded
                :disabled="disabled"
                :id="metadatumComponentId"
                size="is-small"
                icon="magnify"
                :allow-new="false"
                :maxtags="maxtags"
                @add="emitAdd"
                @remove="emitRemove"
                v-model="selected"
                :data="labels"
                field="label"
                attached
                ellipsis
                :aria-close-label="$i18n.get('remove_value')"
                :placeholder="$i18n.get('instruction_type_existing_term')"
                :loading="isFetching"
                :class="{'has-selected': selected != undefined && selected != []}"
                autocomplete
                @typing="autoCompleteTerm">
            <template slot-scope="props">
                <div class="media">
                    <div class="media-content">
                        {{ props.option.label }}
                    </div>
                </div>
            </template>
            <template 
                    v-if="!isFetching"
                    slot="empty">
                {{ $i18n.get('info_no_terms_found') }}
            </template>
        </b-taginput>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';

    export default {
        data(){
            return {
                selected: [],
                labels: [],
                isFetching: false,
            }
        },
        watch: {
          selected(){
              if (this.allowSelectToCreate && this.selected[0]) {
                  this.selected[0].label.includes(`(${this.$i18n.get('select_to_create')})`);
                  this.selected[0].label = this.selected[0].label.split('(')[0];
              }
          }
        },
        props: {
            metadatumComponentId: '',
            value: [ Number, String, Array ],
            allowNew: true,
            taxonomyId: Number,
            disabled: false,
            allowSelectToCreate: false,
            maxtags: '',
        },
        created(){
            if (this.value && this.value.length > 0){
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
            autoCompleteTerm: _.debounce( function(value) {
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
                    all: true,
                    order: 'asc',
                    offset: 0,
                    number: 12
                }).then((res) => {
                    
                    for (let term of res.terms)
                        this.labels.push({label: term.name, value: term.id});

                    if (res.terms.length <= 0 && this.allowSelectToCreate)
                        this.labels.push({label: `${value} (${this.$i18n.get('select_to_create')})`, value: value})

                    this.isFetching = false;    
                }).catch((error) => {
                    this.isFetching = false;
                    throw error;
                });
            }, 500),
            updateSelectedValues(){
                let selected = [];

                for( let term of this.value)
                    selected.push({label: term.label, value: term.value})

                this.selected = selected;
            },
            emitAdd(){
                let val = this.selected;
                let results = [];

                if (val.length > 0) {
                    for (let term of val)
                        results.push( term.value );
                
                    this.$emit('input', results);
                }
            },
            emitRemove(){
                let val = this.selected;
                let results = [];

                for (let term of val)
                    results.push(term.value);

                this.$emit('input', results);
            }
        }
    }
</script>