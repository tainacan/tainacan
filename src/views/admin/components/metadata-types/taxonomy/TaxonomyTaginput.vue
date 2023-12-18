<template>
    <b-taginput
            :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
            v-model="selected"
            expanded
            :disabled="disabled"
            size="is-small"
            icon="magnify"
            :data="options"
            :maxtags="maxtags != undefined ? maxtags : (itemMetadatum.metadatum.multiple == 'yes' || allowNew === true ? (maxMultipleValues !== undefined ? maxMultipleValues : null) : '1')"
            field="label"
            :remove-on-keys="[]"            
            :dropdown-position="isLastMetadatum ? 'top' :'auto'"
            attached
            ellipsis
            :aria-close-label="$i18n.get('remove_value')"
            :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : $i18n.get('instruction_type_existing_term')"
            :loading="isFetching"
            :class="{ 'has-selected': selected != undefined && selected != [] }"
            autocomplete
            check-infinite-scroll
            :has-counter="false"
            :append-to-body="!itemMetadatum.item"
            :open-on-focus="false"
            @add="emitAdd"
            @remove="emitRemove"
            @typing="search"
            @infinite-scroll="searchMore">
        <template #default="props">
            <div class="media">
                <div class="media-content">
                    {{ props.option.label }}
                </div>
            </div>
        </template>
        <template 
                v-if="!isFetching && options.length <= 0 && searchName.length > 0"
                #empty>
            {{ $i18n.get('info_no_terms_found') }}
        </template>
        <template
                v-if="allowNew && !isFetching && searchName.length > 0"
                #footer>
                <a @click="$emit('showAddNewTerm', { name: searchName })">
                {{ $i18n.get('label_create_new_term') + ' "' + searchName + '"' }}
            </a>
        </template>
    </b-taginput>
</template>

<script>
    import { mapActions } from 'vuex';

    export default {
        props: {
            itemMetadatum: Object,
            value: [ Number, String, Array ],
            allowNew: Boolean,
            taxonomyId: Number,
            disabled: false,
            maxtags: '',
            isLastMetadatum: false
        },
        emits: [
            'input',
            'showAddNewTerm'
        ],
        data() {
            return {
                selected: [],
                options: [],
                isFetching: false,
                offset: 0,
                searchName: '',
                totalTerms: 0
            }
        },
        computed: {
            maxMultipleValues() {
                return (
                    this.itemMetadatum &&
                    this.itemMetadatum.metadatum &&
                    this.itemMetadatum.metadatum.cardinality &&
                    !isNaN(this.itemMetadatum.metadatum.cardinality) &&
                    this.itemMetadatum.metadatum.cardinality > 1
                ) ? this.itemMetadatum.metadatum.cardinality : undefined;
            },
        },
        watch: {
            value() {
                if ( this.value && this.value.length > 0 && this.value[0].label )
                    this.selected = JSON.parse(JSON.stringify(this.value));
            }
        },
        mounted() {
            if ( this.value && this.value.length > 0 && this.value[0].label )
                this.selected = JSON.parse(JSON.stringify(this.value));
        },
        methods: {
            ...mapActions('taxonomy', [
                'fetchTerms'
            ]),
            search: _.debounce( function(value) {                

                // String update
                if ( value != this.searchName ) {
                    this.searchName = value;
                    this.options = [];
                    this.offset = 0;
                } 
                
                // String cleared
                if ( !value.length ) {
                    this.searchName = value;
                    this.options = [];
                    this.offset = 0;
                    return;
                }

                // No need to load more
                if ( this.offset > 0 && this.options.length >= this.totalTerms )
                    return;

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
                    number: 12,
                    exclude: this.selected.map((aSelected) => aSelected.value )
                }).then((res) => {
                    
                    for (let term of res.terms)
                        this.options.push({ label: term.name, value: term.id });

                    this.offset += 12;
                    this.totalTerms = res.total;

                    this.isFetching = false;    
                }).catch((error) => {
                    this.isFetching = false;
                    throw error;
                });
            }, 500),
            searchMore: _.debounce(function () {
                this.search(this.searchName)
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