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

export const updateItemDocument = ({ commit }, { item_id, document, document_type }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/items/' + item_id, {
            document: document,
            document_type: document_type
        }).then( res => {
            let item = res.data;

            commit('setItem', item);
            commit('setLastUpdated');
            resolve( res.data );
        }).catch( error => { 
            reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
        });

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

export const fetchAttachments = ({ commit }, { page, attachmentsPerPage, itemId, documentId }) => {
    commit('cleanAttachments');
    commit('setTotalAttachments', null);

    let endpoint = '/items/' + itemId + '/attachments?order=ASC&orderby=menu_order&perpage=' + attachmentsPerPage + '&paged=' + page;

    if (documentId && !isNaN(documentId))
        endpoint += '&exclude=' + documentId;

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

export const updateThumbnail = ({ commit }, { itemId, thumbnailId }) => {
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

export const submitItemSubmission = ({ commit }, { itemSubmission, itemSubmissionMetadata }) => {
    return new Promise((resolve, reject) => {

        for (let key of Object.keys(itemSubmission)) {
            if (['document', 'attachments', 'thumbnail'].includes(key) )
                delete itemSubmission[key];
        }

        axios.tainacan.post('/collection/' + itemSubmission.collection_id + '/items/submission', {...itemSubmission, metadata: itemSubmissionMetadata } )
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
            headers: { 'content-type': 'multipart/form-data' }
        }
        
        const formData = new FormData();

        for (let key of Object.keys(itemSubmission)) {
            if (['document', 'attachments', 'thumbnail'].includes(key) )
                formData.append(key, itemSubmission[key]);
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