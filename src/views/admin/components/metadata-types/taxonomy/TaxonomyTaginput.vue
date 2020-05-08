<template>
    <div class="block">
        <b-taginput
                expanded
                :disabled="disabled"
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
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
                :class="{ 'has-selected': selected != undefined && selected != [] }"
                autocomplete
                @typing="loadTerms"
                check-infinite-scroll
                @infinite-scroll="loadMoreTerms">
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
        props: {
            itemMetadatum: Object,
            value: [ Number, String, Array ],
            allowNew: Boolean,
            taxonomyId: Number,
            disabled: false,
            allowSelectToCreate: false,
            maxtags: '',
        },
        data() {
            return {
                selected: [],
                labels: [],
                isFetching: false,
                offset: 0,
                searchName: '',
                totalTerms: 0
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
            loadTerms: _.debounce( function(value) {                

                // String update
                if (value != this.searchName) {
                    this.searchName = value;
                    this.labels = [];
                    this.offset = 0;
                } 
                
                // String cleared
                if (!value.length) {
                    this.searchName = value;
                    this.labels = [];
                    this.offset = 0;
                }

                // No need to load more
                if (this.offset > 0 && this.labels.length >= this.totalTerms)
                    return

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
                        searchterm: this.searchName
                    },
                    all: true,
                    order: 'asc',
                    offset: this.offset,
                    number: 12
                }).then((res) => {
                    
                    for (let term of res.terms)
                        this.labels.push({ label: term.name, value: term.id });

                    if (res.terms.length <= 0 && this.allowSelectToCreate)
                        this.labels.push({ label: `${value} (${this.$i18n.get('select_to_create')})`, value: value })

                    this.offset += 12;
                    this.totalTerms = res.total;

                    this.isFetching = false;    
                }).catch((error) => {
                    this.isFetching = false;
                    throw error;
                });
            }, 500),
            loadMoreTerms: _.debounce(function () {
                this.loadTerms(this.searchName)
            }, 250),
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