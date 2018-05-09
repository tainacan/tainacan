import { tainacan as axios } from '../../js/axios/axios';

export const filter_type_mixin = {
    props: {
        filter: {
            type: Object // concentrate all attributes field id and type
        },
        field_id: [Number], // not required, but overrides the filter field id if is set
        collection_id: [Number], // not required, but overrides the filter field id if is set
        filter_type: [String],  // not required, but overrides the filter field type if is set
        id: '',
        query: {}
    },
    methods: {
        getValuesPlainText(fieldId, search) {
            let url = '/collection/' + this.collection + '/fields/' + fieldId + '?fetch=all_field_values&nopaging=1';
            
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

            return axios.get( url + '&nopaging=1')
                .then(res => {
                    if (res.data.length > 0) {
                        for (let item of res.data) {
                            this.options.push({label: item.title, value: item.id, img: item.thumbnail });
                        }
                    }
                })
                .catch(error => {
                    this.$console.error(error);
                });
        }
    }
}