import axios from '../../../axios';

// Actions related to Item's metadata
export const updateItemMetadatum = ({ commit }, { item_id, metadatum_id, values, parent_meta_id }) => {
    let body = { values: values }

    if (parent_meta_id != undefined && parent_meta_id != null && parent_meta_id != false)
        body['parent_meta_id'] = parent_meta_id;
        
    return new Promise((resolve, reject) => {
        axios.tainacan.patch(`/item/${item_id}/metadata/${metadatum_id}`, body)
            .then( res => {
                let itemMetadatum = res.data;
                commit('setSingleMetadatum', itemMetadatum);
                commit('setLastUpdated');
                resolve(itemMetadatum)
            })
            .catch( error => {
                reject({
                    error: error.response.data.errors,
                    error_message: error.response.data.error_message,
                    item_metadata: error.response.data.item_metadata
                });
            })
    });
};

export const fetchItemMetadata = ({ commit }, item_id) => {
    commit('cleanItemMetadata');
    return new Promise((resolve, reject) => {
        axios.tainacan.get('/item/' + item_id + '/metadata')
        .then(res => {
            let itemMetadata = res.data;
            commit('setItemMetadata', itemMetadata);
            resolve( itemMetadata );
        })
        .catch(error => {
            reject( error );
        });
    });
};


// Actions related to Item's metadata
export const fetchCompoundFirstParentMetaId = ({ commit }, { item_id, metadatum_id }) => {
   
    return new Promise((resolve, reject) => {
        axios.tainacan.patch(`/item/${item_id}/metadata/${metadatum_id}`, { value: [] })
            .then( res => {
                const parentMetaId = res.data.parent_meta_id;
                resolve(parentMetaId);
            })
            .catch( error => {
                reject({
                    error: error.response.data.errors,
                    error_message: error.response.data.error_message,
                    item_metadata: error.response.data.item_metadata
                });
            })
    });
};


export const deleteItemMetadataGroup = ({ commit }, { item_id, metadatum_id, parent_meta_id }) => {
        
    return new Promise((resolve) => {
        axios.tainacan.delete(`/item/${item_id}/metadata/${metadatum_id}`, { data: { parent_meta_id: parent_meta_id } })
            .then( (res) => {
                commit('deleteChildItemMetadata', { parentMetadatumId: metadatum_id, parentMetaId: parent_meta_id });
                commit('setLastUpdated');
                resolve({ itemMetadataRemoved: res.data.item_metadata_removed, parentMetaId: parent_meta_id });
            });
    });
};

export const cleanItemMetadata = ({ commit }) => {
    commit('cleanItemMetadata');
};

export const cleanLastUpdated = ({ commit }) => {
    commit('cleanLastUpdated');
};

export const setLastUpdated = ({ commit}, value) => {
    commit('setLastUpdated', value);
};

// Actions directly related to Item
export const fetchItem = ({ commit }, { itemId, contextEdit, fetchOnly } ) => {
    commit('cleanItem');

    let endpoint = '/items/'+ itemId + '?'; 

    if (contextEdit)
        endpoint += '&context=edit';

    if (fetchOnly != undefined)
        endpoint += '&fetch_only=' + fetchOnly;

    const source = axios.CancelToken.source();
        
    return Object({ 
        request: new Promise((resolve, reject) => {
            axios.tainacan.get(endpoint,{
                cancelToken: source.token
            })
                .then(res => {
                    let item = res.data;
                    commit('setItem', item);
                    resolve( res.data );
                })
                .catch((thrown) => {
                    if (axios.isCancel(thrown)) {
                        console.log('Request canceled: ', thrown.message);
                    } else {
                        reject(thrown);
                    }
                }); 
        }),
        source: source
    });
};

export const replaceItem = ({ commit }, item) => {
    commit('setItem', item);
};

export const fetchItemTitle = ({ commit }, id) => {
    commit('cleanItemTitle');
    return new Promise((resolve, reject) =>{ 
        axios.tainacan.get('/items/' + id + '?fetch_only=title')
        .then(res => {
            let itemTitle = res.data;
            commit('setItemTitle', itemTitle.title);
            resolve( itemTitle.title );
        })
        .catch(error => {
            reject(error);
        })
    });
};

export const sendItem = ( { commit }, item) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.post('/collection/'+ item.collection_id + '/items/', item)
            .then( res => {
                commit('setItem', res.data);
                commit('setLastUpdated');
                resolve( res.data );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};
 
export const updateItem = ({ commit }, item) => {

    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/items/' + item.id, item)
            .then( res => {
                commit('setItem', res.data);
                commit('setLastUpdated');
                resolve( res.data );
            }).catch( error => { 
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });

    }); 
};
 
export const duplicateItem = ({ commit }, { collectionId, itemId, copies }) => {

    return new Promise((resolve, reject) => {
        axios.tainacan.post('/collection/' + collectionId + '/items/' + itemId + '/duplicate', { copies: new Number(copies) })
            .then( res => {
                resolve( res.data.items );
            }).catch( error => { 
                reject(error);
            });

    }); 
};

export const updateItemDocument = ({ commit }, { item_id, document, document_type, document_options }) => {
    let params = {
        document: document,
        document_type: document_type,
    }
    if (document_options)
        params['document_options'] = document_options;
        
    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/items/' + item_id, params).then( res => {
            let item = res.data;

            commit('setItem', item);
            commit('setLastUpdated');
            resolve( res.data );
        }).catch( error => { 
            reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
        });

    }); 
};

export const fetchOnlyRelatedItems = ({ commit }, { itemId, contextEdit } ) => {

    let endpoint = '/items/'+ itemId + '?'; 

    if (contextEdit)
        endpoint += '&context=edit';

    endpoint += '&fetch_only=related_items'

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
            .then(res => {
                let relatedItems = res.data && res.data.related_items ? res.data.related_items : [];
                commit('setOnlyRelatedItemsToItem', {itemId: itemId, relatedItems: relatedItems });
                resolve( relatedItems );
            })
            .catch((thrown) => reject(thrown)); 
    });
};

// Attachments =======================================
export const sendAttachment = ( { commit }, { item_id, file }) => {
    commit('cleanAttachment');
    return new Promise(( resolve, reject ) => {
        axios.wp.post('/media/?post=' + item_id, file, {
            headers: { 'Content-Disposition': 'attachment; filename=' + file.name },
        })
            .then( res => {
                let attachment = res.data;
                commit('setSingleAttachment', attachment);
                commit('setLastUpdated');
                resolve( attachment );
            })
            .catch(error => {
                reject( error.response );
            });
    });
};

export const removeAttachmentFromItem = ( { commit }, attachmentId) => {
    commit('cleanAttachment');
    return new Promise(( resolve, reject ) => {
        axios.wp.patch('/media/' + attachmentId, {
            post: 0
        })
            .then( res => {
                let attachment = res.data;
                commit('removeAttatchmentFromItem', attachmentId);
                commit('setLastUpdated');
                resolve( attachment );
            })
            .catch(error => {
                reject( error.response );
            });
    });
};

export const deletePermanentlyAttachment = ( { commit }, attachmentId) => {
    return new Promise(( resolve, reject ) => {
        axios.wp.delete('/media/' + attachmentId + '?force=true')
            .then( res => {
                let attachment = res.data;
                resolve( attachment );
            })
            .catch(error => {
                reject( error.response );
            });
    });
};

export const fetchAttachments = ({ commit }, { page, attachmentsPerPage, itemId, excludeDocumentId, excludeThumbnailId }) => {
    let endpoint = '/items/' + itemId + '/attachments?order=ASC&orderby=menu_order&perpage=' + attachmentsPerPage + '&paged=' + page;

    if (excludeDocumentId && !isNaN(excludeDocumentId) && excludeThumbnailId && !isNaN(excludeThumbnailId))
        endpoint += '&exclude=' + excludeDocumentId + ',' + excludeThumbnailId;
    else if (excludeDocumentId && !isNaN(excludeDocumentId))
        endpoint += '&exclude=' + excludeDocumentId;
    else if (excludeThumbnailId && !isNaN(excludeThumbnailId))
        endpoint += '&exclude=' + excludeThumbnailId;

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
        .then(res => {
            let attachments = res.data;
            let total =  res.headers['x-wp-total'];

            commit('setAttachments', attachments);
            commit('setTotalAttachments', total);

            resolve( {
                attachments: attachments,
                total: total
            });
        })
        .catch(error => {
            reject( error );
        });
    });
};

export const updateThumbnail = ({ commit }, { itemId, thumbnailId, thumbnailAlt }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/items/' + itemId, {
            _thumbnail_id: thumbnailId
        }).then( res => {
            let item = res.data
            commit('setItem', item);
            commit('setLastUpdated');
            resolve( item );
        }).catch( error => { 
            reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
        });

    }); 
};

export const updateThumbnailAlt = ({ commit }, { thumbnailId, thumbnailAlt }) => {
    return new Promise((resolve, reject) => {
        axios.wp.patch('/media/' + thumbnailId + '?force=true', {
            alt_text: thumbnailAlt
        }).then( res => {
            let thumbnail = res.data;
            commit('setLastUpdated');
            resolve( thumbnail );
        }).catch( error => { 
            reject( error );
        });

    }); 
};

// Item Submission ======================================================
export const clearItemSubmission = ({ commit }) => {
    commit('clearItemSubmission');
}

export const setItemSubmission = ({ commit }, value) => {
    commit('setItemSubmission', value);
}

export const setItemSubmissionMetadata = ({ commit }, value) => {
    commit('setItemSubmissionMetadata', value);
}

export const updateItemSubmission = ({ commit }, { key, value }) => {
    commit('updateItemSubmission', { key: key, value: value });
}

export const updateItemSubmissionMetadatum = ({ commit }, { metadatum_id, values, child_group_index, parent_id }) => {
    commit('updateItemSubmissionMetadatum', { metadatum_id: metadatum_id, values: values, child_group_index: child_group_index, parent_id: parent_id });
}

export const deleteGroupFromItemSubmissionMetadatum = ({ commit }, { metadatum_id, child_group_index }) => {
    commit('deleteGroupFromItemSubmissionMetadatum', { metadatum_id: metadatum_id, child_group_index: child_group_index });
}

export const submitItemSubmission = ({ commit }, { itemSubmission, itemSubmissionMetadata, captchaResponse }) => {
    return new Promise((resolve, reject) => {

        let item = JSON.parse(JSON.stringify(itemSubmission)); // Use a copy as the next request will need document, attchment and thumbnail

        for (let key of Object.keys(item)) {
            if (['attachments', 'thumbnail'].includes(key) )
                delete item[key];
            else if (key === 'document' && itemSubmission.document_type === 'attachment' )
                delete item[key];
        }

        if (captchaResponse)
            item['g-recaptcha-response'] = captchaResponse;

        axios.tainacan.post('/collection/' + itemSubmission.collection_id + '/items/submission', {...item, metadata: itemSubmissionMetadata } )
            .then( res => {
                resolve( res.data.id );
            }).catch( error => { 
                reject({
                    errors: error.response.data.errors,
                    error_message: error.response.data.error_message
                });
            });
    }); 
}

export const finishItemSubmission = ({ commit }, { itemSubmission, fakeItemId }) => {
    return new Promise((resolve, reject) => {
        let config = {
            headers: { 'Content-Type': 'multipart/form-data' }
        }
        const formData = new FormData();

        for (let key of Object.keys(itemSubmission)) {
            if (key === 'thumbnail' || (key === 'document' && itemSubmission.document_type === 'attachment') )
                formData.append(key, itemSubmission[key]);
            else if (key === 'attachments') {
                for (let i = 0; i < itemSubmission[key].length; i++)
                    formData.append(key + '[' + i + ']', itemSubmission[key][i]);
            }
        }
        axios.tainacan.post('/collection/' + itemSubmission.collection_id + '/items/submission/' + fakeItemId + '/finish', formData, config )
            .then( res => {
                resolve( res.data );
            }).catch( error => {
                reject({
                    errors: error.response.data.errors,
                    error_message: error.response.data.error_message
                });
            });
    }); 
}