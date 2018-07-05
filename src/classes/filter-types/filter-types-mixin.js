import { tainacan as axios } from '../../js/axios/axios';

export const filter_type_mixin = {
    data () {
        return {
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png'
        }
    },
    props: {
        filter: {
            type: Object // concentrate all attributes metadatum id and type
        },
        metadatum_id: [Number], // not required, but overrides the filter metadatum id if is set
        collection_id: [Number], // not required, but overrides the filter metadatum id if is set
        filter_type: [String],  // not required, but overrides the filter metadatum type if is set
        id: '',
        query: {}
    },
    methods: {
        getValuesPlainText(metadatumId, search, isRepositoryLevel) {

            let url = '/collection/' + this.collection + '/metadata/' + metadatumId + '?fetch=all_metadatum_values&nopaging=1';

            if(isRepositoryLevel){
                url = '/metadata/' + metadatumId + '?fetch=all_metadatum_values&nopaging=1';
            }

            if( search ){
                url += "&search=" + search;
            }

            return axios.get(url)
                .then(res => {
                    if (res.data && res.data[0]) {
                        for (let metadata of res.data[0]) {
                            let index = this.options.findIndex(itemMetadata => itemMetadata.value === metadata.mvalue);
                            if (index < 0 && metadata.mvalue !== '') {
                                this.options.push({label: metadata.mvalue, value: metadata.mvalue})
                            }

                        }
                    }
                })
                .catch(error => {
                    this.$console.error(error);
                });
        },
        getValuesRelationship(collectionTarget, search) {
            let url = '/collection/' + collectionTarget + '/items?';

            if( search ){
                url += "search=" + search;
            }

            return axios.get(url + '&nopaging=1&fetch_only[0]=thumbnail&fetch_only[1]=title&fetch_only[2]=id')
                .then(res => {
                    if (res.data.length > 0) {
                        for (let item of res.data) {
                            this.options.push({label: item.title, value: item.id, img: ( item.thumbnail.thumb ? item.thumbnail.thumb : this.thumbPlaceholderPath ) });
                        }
                    }
                })
                .catch(error => {
                    this.$console.error(error);
                });
        }
    }
}