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
        getValuesPlainText(field_id) {
            return axios.get('/collection/' + this.collection + '/fields/' + field_id + '?fetch=all_field_values')
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
                    console.log(error);
                });
        },
        getValuesRelationship(collectionTarget, search) {
            return axios.get('/collection/' + collectionTarget + '/items?search=' + search)
                .then(res => {
                    if (res.data.length > 0) {
                        for (let item of res.data) {
                            this.options.push({label: item.title, value: item.id, img: item.featured_image });
                        }
                    }
                })
                .catch(error => {
                    console.log(error);
                });
        }
    }
}