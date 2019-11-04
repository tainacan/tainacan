<template>
    <div :class="{ 'is-flex': metadatum.metadatum.multiple != 'yes' || maxtags != undefined }">
        <b-taginput
                expanded
                :disabled="disabled"
                :id="metadatum.metadatum.metadata_type_object.component + '-' + metadatum.metadatum.slug"
                :value="selected"
                size="is-small"
                icon="magnify"
                @input="onInput"
                :data="options"
                :maxtags="maxtags != undefined ? maxtags : (metadatum.metadatum.multiple == 'yes' || allowNew === true ? 100 : 1)"
                autocomplete
                attached
                :placeholder="$i18n.get('instruction_type_existing_term')"
                :loading="isLoading"
                :aria-close-label="$i18n.get('remove_value')"
                :class="{'has-selected': selected != undefined && selected != []}"
                field="label"
                @typing="(query) => { options = []; search(query); }">
            <template slot-scope="props">
                <div class="media">
                    <div 
                            v-if="props.option.img"
                            class="media-left">
                        <img 
                                width="28"
                                :src="props.option.img">
                    </div>
                    <div class="media-content">
                        {{ props.option.label }}
                    </div>
                </div>
            </template>
            <template 
                    v-if="!isLoading"
                    slot="empty">
                {{ $i18n.get('info_no_item_found') }}
            </template>
        </b-taginput>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios'
    import qs from 'qs';

    export default {
        created() {
            this.collectionId = ( this.metadatum && this.metadatum.metadatum.metadata_type_options && this.metadatum.metadatum.metadata_type_options.collection_id ) ? this.metadatum.metadatum.metadata_type_options.collection_id : '';
            if (this.metadatum.value && (Array.isArray( this.metadatum.value ) ? this.metadatum.value.length > 0 : true )) {
                let query = qs.stringify({ postin: ( Array.isArray( this.metadatum.value ) ) ? this.metadatum.value : [ this.metadatum.value ]  });
                query += this.metadatum.metadatum.metadata_type_options.search ? '&fetch_only_meta=' + this.metadatum.metadatum.metadata_type_options.search : '';
                axios.get('/collection/' + this.collectionId + '/items?' + query + '&nopaging=1&fetch_only=title,thumbnail')
                    .then( res => {
                        if (res.data.items) {
                            for (let item of res.data.items)
                                this.selected.push({
                                    label: this.getItemLabel(item),
                                    value: item.id,
                                    img: item.thumbnail && item.thumbnail['tainacan-small'] && item.thumbnail['tainacan-small'][0] ? item.thumbnail['tainacan-small'][0] : ''
                                });
                        }
                    })
                    .catch(error => {
                        this.$console.log(error);
                    });
            }
        },
        data() {
            return {
                results:'',
                selected:[],
                options: [],
                isLoading: false,
                collectionId: '',
                inputValue: null,
                queryObject: {},
                itemsFound: []
            }
        },
        props: {
            metadatum: Object,
            maxtags: undefined,
            disabled: false,
            allowNew: true,
        },
        methods: {
            setResults(option){
                if(!option)
                    return;
                this.results = option.value;
            },
            onInput(newSelected) {
                this.selected = newSelected;
                this.$emit('input', newSelected.map((item) => item.value));
            },
            search: _.debounce(function(query) {
                if ( this.selected.length > 0  && this.metadatum.metadatum.multiple === 'no')
                    return '';

                if (query !== '') {
                    this.isLoading = true;

                    axios.get('/collection/' + this.collectionId + '/items?' + this.getQueryString(query))
                        .then( res => {
                            this.isLoading = false;
                            this.options = [];

                            if (res.data.items) {
                                for (let item of res.data.items)
                                    this.options.push({
                                        label: this.getItemLabel(item),
                                        value: item.id,
                                        img: item.thumbnail && item.thumbnail['tainacan-small'] && item.thumbnail['tainacan-small'][0] ? item.thumbnail['tainacan-small'][0] : ''
                                    })
                            }
                        })
                        .catch(error => {
                            this.$console.log(error);
                        });
                }

            }, 500),
            getItemLabel(item) {
                let label = '';
                for (let m in item.metadata) {
                    if (item.metadata[m].id == this.metadatum.metadatum.metadata_type_options.search)
                        label = item.metadata[m].value_as_string;
                }
                if (label != '' && label != item.title && item.title != '')
                    label += ' (' + item.title + ')';
                else if (label == '')
                    label = item.title;
                
                return label;
            },
            getQueryString( search ) {
                let query = [];

                if (this.metadatum.metadatum.metadata_type_options &&
                    this.metadatum.metadatum.metadata_type_options.search)
                {
                    query['metaquery'] = [];
                    
                    query['metaquery'][0] = {
                        key: this.metadatum.metadatum.metadata_type_options.search,
                        value: search,
                        compare: 'LIKE'
                    }
                    
                } else {
                    query['search'] = search;
                }
                query['fetch_only'] = 'title,thumbnail';
                query['fetch_only_meta'] = this.metadatum.metadatum.metadata_type_options.search;

                return qs.stringify(query);
            }
        }
    }
</script>

<style>
    .help.counter {
        display: none;
    }
    div.is-flex {
        justify-content: flex-start;
    }
</style>
