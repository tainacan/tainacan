import axios from '../../../axios';

// Actions related to Item's metadata
/**
 * Updates one item metadatum value on the API and syncs local item metadata state.
 * Dispatches `item/updateItemMetadatum`.
 * @returns {Promise<Object>} Resolves with the updated item metadatum; rejects with API validation details.
 */
export const updateItemMetadatum = ({ commit }, { item_id, metadatum_id, values, parent_meta_id }) => {
    let body = { values: values }

    if (parent_meta_id != undefined && parent_meta_id != null && parent_meta_id != false)
        body['parent_meta_id'] = parent_meta_id;

    return new Promise((resolve, reject) => {
        axios.tainacanApi.put(`/item/${item_id}/metadata/${metadatum_id}`, body)
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

/**
 * Fetches all metadata values for an item and replaces cached item metadata.
 * Dispatches `item/fetchItemMetadata`.
 * @returns {Promise<Array>} Resolves with the item metadata array; rejects with the request error.
 */
export const fetchItemMetadata = ({ commit }, item_id) => {
    commit('cleanItemMetadata');
    return new Promise((resolve, reject) => {
        axios.tainacanApi.get('/item/' + item_id + '/metadata')
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

/**
 * Fetches one metadatum entry and updates it when the response is valid.
 * Dispatches `item/fetchItemMetadatum`.
 * @returns {Promise<Object|null>} Resolves with the updated metadatum or null when no metadatum payload is returned; rejects on request error.
 */
export const fetchItemMetadatum = ({ commit }, { item_id, metadatum_id }) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.get('/item/' + item_id + '/metadata/' + metadatum_id)
            .then(res => {
                const updatedItemMetadatum = res.data;
                if (updatedItemMetadatum && updatedItemMetadatum.metadatum) {
                    commit('setSingleMetadatum', updatedItemMetadatum);
                    resolve(updatedItemMetadatum);
                } else {
                    resolve(null);
                }
            })
            .catch(error => reject(error));
    });
};


// Actions related to Item's metadata
/**
 * Creates a temporary empty compound entry to retrieve the first parent_meta_id.
 * Dispatches `item/fetchCompoundFirstParentMetaId`.
 * @returns {Promise<number|string>} Resolves with the generated parent_meta_id; rejects with API validation details.
 */
export const fetchCompoundFirstParentMetaId = ({ commit }, { item_id, metadatum_id }) => {
   
    return new Promise((resolve, reject) => {
        axios.tainacanApi.put(`/item/${item_id}/metadata/${metadatum_id}`, { values: [] })
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


/**
 * Deletes one child metadata group from a compound metadatum.
 * Dispatches `item/deleteItemMetadataGroup`.
 * @returns {Promise<Object>} Resolves with removed metadata info and parentMetaId.
 */
export const deleteItemMetadataGroup = ({ commit }, { item_id, metadatum_id, parent_meta_id }) => {
        
    return new Promise((resolve) => {
        axios.tainacanApi.delete(`/item/${item_id}/metadata/${metadatum_id}`, { data: { parent_meta_id: parent_meta_id } })
            .then( (res) => {
                commit('deleteChildItemMetadata', { parentMetadatumId: metadatum_id, parentMetaId: parent_meta_id });
                commit('setLastUpdated');
                resolve({ itemMetadataRemoved: res.data.item_metadata_removed, parentMetaId: parent_meta_id });
            });
    });
};

/**
 * Clears the cached item metadata list.
 * Dispatches `item/cleanItemMetadata`.
 * @returns {void} No return value.
 */
export const cleanItemMetadata = ({ commit }) => {
    commit('cleanItemMetadata');
};

/**
 * Clears the item last-updated marker.
 * Dispatches `item/cleanLastUpdated`.
 * @returns {void} No return value.
 */
export const cleanLastUpdated = ({ commit }) => {
    commit('cleanLastUpdated');
};

/**
 * Sets a custom last-updated value or refreshes it to now.
 * Dispatches `item/setLastUpdated`.
 * @returns {void} No return value.
 */
export const setLastUpdated = ({ commit}, value) => {
    commit('setLastUpdated', value);
};

// Actions directly related to Item
/**
 * Fetches one item and exposes a cancel token source for request cancellation.
 * Dispatches `item/fetchItem`.
 * @returns {Object} Returns an object with `request` (Promise resolving item data or rejecting request errors) and `source` (Axios cancel token source).
 */
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
            axios.tainacanApi.get(endpoint,{
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

/**
 * Replaces the current cached item without API calls.
 * Dispatches `item/replaceItem`.
 * @returns {void} No return value.
 */
export const replaceItem = ({ commit }, item) => {
    commit('setItem', item);
};

/**
 * Fetches only the item title and updates title state.
 * Dispatches `item/fetchItemTitle`.
 * @returns {Promise<string>} Resolves with the item title; rejects on request error.
 */
export const fetchItemTitle = ({ commit }, id) => {
    commit('cleanItemTitle');
    return new Promise((resolve, reject) =>{ 
        axios.tainacanApi.get('/items/' + id + '?fetch_only=title')
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

/**
 * Creates a new item in a collection and stores the returned item.
 * Dispatches `item/sendItem`.
 * @returns {Promise<Object>} Resolves with created item data; rejects with API error_message and errors.
 */
export const sendItem = ( { commit }, item) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacanApi.post('/collection/'+ item.collection_id + '/items/?context=edit', item)
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
 
/**
 * Updates an existing item and syncs the cached item state.
 * Dispatches `item/updateItem`.
 * @returns {Promise<Object>} Resolves with updated item data; rejects with API error_message and errors.
 */
export const updateItem = ({ commit }, item) => {

    return new Promise((resolve, reject) => {
        axios.tainacanApi.put('/items/' + item.id + '?context=edit', item)
            .then( res => {
                commit('setItem', res.data);
                commit('setLastUpdated');
                resolve( res.data );
            }).catch( error => { 
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });

    }); 
};
 
/**
 * Duplicates an item one or more times in the same collection.
 * Dispatches `item/duplicateItem`.
 * @returns {Promise<Array>} Resolves with duplicated items; rejects on request error.
 */
export const duplicateItem = ({ commit }, { collectionId, itemId, copies }) => {

    return new Promise((resolve, reject) => {
        axios.tainacanApi.post('/collection/' + collectionId + '/items/' + itemId + '/duplicate', { copies: new Number(copies) })
            .then( res => {
                resolve( res.data.items );
            }).catch( error => { 
                reject(error);
            });

    }); 
};

/**
 * Updates the item document payload and optional document options.
 * Dispatches `item/updateItemDocument`.
 * @returns {Promise<Object>} Resolves with updated item data; rejects with API error_message and errors.
 */
export const updateItemDocument = ({ commit }, { item_id, document, document_type, document_options }) => {
    let params = {
        document: document,
        document_type: document_type,
    }
    if (document_options)
        params['document_options'] = document_options;
        
    return new Promise((resolve, reject) => {
        axios.tainacanApi.put('/items/' + item_id, params).then( res => {
            let item = res.data;

            commit('setItem', item);
            commit('setLastUpdated');
            resolve( res.data );
        }).catch( error => { 
            reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
        });

    }); 
};

/**
 * Fetches only related items and patches them into the current item.
 * Dispatches `item/fetchOnlyRelatedItems`.
 * @returns {Promise<Array>} Resolves with related items list; rejects on request error.
 */
export const fetchOnlyRelatedItems = ({ commit }, { itemId, contextEdit } ) => {

    let endpoint = '/items/'+ itemId + '?'; 

    if (contextEdit)
        endpoint += '&context=edit';

    endpoint += '&fetch_only=related_items'

    return new Promise((resolve, reject) => {
        axios.tainacanApi.get(endpoint)
            .then(res => {
                let relatedItems = res.data && res.data.related_items ? res.data.related_items : [];
                commit('setOnlyRelatedItemsToItem', {itemId: itemId, relatedItems: relatedItems });
                resolve( relatedItems );
            })
            .catch((thrown) => reject(thrown)); 
    });
};

// Attachments =======================================
/**
 * Uploads one attachment file to an item via WordPress media API.
 * Dispatches `item/sendAttachment`.
 * @returns {Promise<Object>} Resolves with uploaded attachment data; rejects with error.response.
 */
export const sendAttachment = ( { commit }, { item_id, file }) => {
    commit('cleanAttachment');
    return new Promise(( resolve, reject ) => {
        axios.wpApi.post('/media/?post=' + item_id, file, {
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

/**
 * Detaches one attachment from an item without deleting the media record.
 * Dispatches `item/removeAttachmentFromItem`.
 * @returns {Promise<Object>} Resolves with updated attachment data; rejects with error.response.
 */
export const removeAttachmentFromItem = ( { commit }, attachmentId) => {
    commit('cleanAttachment');
    return new Promise(( resolve, reject ) => {
        axios.wpApi.put('/media/' + attachmentId, {
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

/**
 * Permanently deletes one attachment media record.
 * Dispatches `item/deletePermanentlyAttachment`.
 * @returns {Promise<Object>} Resolves with deleted attachment response; rejects with error.response.
 */
export const deletePermanentlyAttachment = ( { commit }, attachmentId) => {
    return new Promise(( resolve, reject ) => {
        axios.wpApi.delete('/media/' + attachmentId + '?force=true')
            .then( res => {
                let attachment = res.data;
                resolve( attachment );
            })
            .catch(error => {
                reject( error.response );
            });
    });
};

/**
 * Fetches paginated attachments for an item and updates total count.
 * Dispatches `item/fetchAttachments`.
 * @returns {Promise<Object>} Resolves with `{ attachments, total }`; rejects on request error.
 */
export const fetchAttachments = ({ commit }, { page, attachmentsPerPage, itemId, excludeDocumentId, excludeThumbnailId }) => {
    let endpoint = '/items/' + itemId + '/attachments?order=ASC&orderby=menu_order&perpage=' + attachmentsPerPage + '&paged=' + page;

    if (excludeDocumentId && !isNaN(excludeDocumentId) && excludeThumbnailId && !isNaN(excludeThumbnailId))
        endpoint += '&exclude=' + excludeDocumentId + ',' + excludeThumbnailId;
    else if (excludeDocumentId && !isNaN(excludeDocumentId))
        endpoint += '&exclude=' + excludeDocumentId;
    else if (excludeThumbnailId && !isNaN(excludeThumbnailId))
        endpoint += '&exclude=' + excludeThumbnailId;

    return new Promise((resolve, reject) => {
        axios.tainacanApi.get(endpoint)
        .then(res => {
            let attachments = res.data;
            let total =  res.headers['x-wp-total'];

            commit('setAttachments', attachments);
            commit('setTotalAttachments', isNaN(total) ? 0 : Number(total));

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

/**
 * Updates item thumbnail relation and refreshes item state.
 * Dispatches `item/updateThumbnail`.
 * @returns {Promise<Object>} Resolves with updated item; rejects with API error_message and errors.
 */
export const updateThumbnail = ({ commit }, { itemId, thumbnailId, thumbnailAlt }) => {
    return new Promise((resolve, reject) => {
        axios.tainacanApi.put('/items/' + itemId, {
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

/**
 * Updates the alternative text of a thumbnail media record.
 * Dispatches `item/updateThumbnailAlt`.
 * @returns {Promise<Object>} Resolves with thumbnail data; rejects on request error.
 */
export const updateThumbnailAlt = ({ commit }, { thumbnailId, thumbnailAlt }) => {
    return new Promise((resolve, reject) => {
        axios.wpApi.put('/media/' + thumbnailId + '?force=true', {
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

/**
 * Runs WordPress AI alt-text ability and returns generated alt text (does not persist).
 */
export const generateThumbnailAltWithAi = (context, { thumbnailId }) => {
    return new Promise((resolve, reject) => {
        if (!thumbnailId) {
            reject(new Error('no_thumbnail'));
            return;
        }
        if (typeof tainacan_plugin === 'undefined' || !tainacan_plugin.wp_abilities_api_url) {
            reject(new Error('abilities_unavailable'));
            return;
        }
        const path = 'abilities/ai/alt-text-generation/run';
        axios.wpAbilitiesApi.post(path, {
            input: { attachment_id: Number(thumbnailId) }
        }).then((res) => {
            const altText = extractAltTextFromAbilityResponse(res.data);
            if (altText == null || altText === '') {
                reject(new Error('empty_alt_response'));
                return;
            }
            resolve(altText);
        }).catch((err) => {
            reject(err);
        });
    });
};

// Item Submission ======================================================
/**
 * Clears pending front-end item submission form state.
 * Dispatches `item/clearItemSubmission`.
 * @returns {void} No return value.
 */
export const clearItemSubmission = ({ commit }) => {
    commit('clearItemSubmission');
}

/**
 * Sets the item submission draft object.
 * Dispatches `item/setItemSubmission`.
 * @returns {void} No return value.
 */
export const setItemSubmission = ({ commit }, value) => {
    commit('setItemSubmission', value);
}

/**
 * Sets the item submission metadata payload.
 * Dispatches `item/setItemSubmissionMetadata`.
 * @returns {void} No return value.
 */
export const setItemSubmissionMetadata = ({ commit }, value) => {
    commit('setItemSubmissionMetadata', value);
}

/**
 * Updates a single key in the item submission draft.
 * Dispatches `item/updateItemSubmission`.
 * @returns {void} No return value.
 */
export const updateItemSubmission = ({ commit }, { key, value }) => {
    commit('updateItemSubmission', { key: key, value: value });
}

/**
 * Updates one metadatum value in the submission metadata structure.
 * Dispatches `item/updateItemSubmissionMetadatum`.
 * @returns {void} No return value.
 */
export const updateItemSubmissionMetadatum = ({ commit }, { metadatum_id, values, child_group_index, parent_id }) => {
    commit('updateItemSubmissionMetadatum', { metadatum_id: metadatum_id, values: values, child_group_index: child_group_index, parent_id: parent_id });
}

/**
 * Deletes one compound child group from submission metadata.
 * Dispatches `item/deleteGroupFromItemSubmissionMetadatum`.
 * @returns {void} No return value.
 */
export const deleteGroupFromItemSubmissionMetadatum = ({ commit }, { metadatum_id, child_group_index }) => {
    commit('deleteGroupFromItemSubmissionMetadatum', { metadatum_id: metadatum_id, child_group_index: child_group_index });
}

/**
 * Submits metadata fields to create a temporary submitted item entry.
 * Dispatches `item/submitItemSubmission`.
 * @returns {Promise<number|string>} Resolves with fake item id for upload finalization; rejects with API errors and error_message.
 */
export const submitItemSubmission = ({ commit }, { itemSubmission, itemSubmissionMetadata, captchaResponse }) => {
    return new Promise((resolve, reject) => {

        let item = JSON.parse(JSON.stringify(itemSubmission)); // Use a copy as the next request will need document, attachment and thumbnail

        for (let key of Object.keys(item)) {
            if (['attachments', 'thumbnail'].includes(key) )
                delete item[key];
            else if (key === 'document' && itemSubmission.document_type === 'attachment' )
                delete item[key];
        }

        if (captchaResponse)
            item['g-recaptcha-response'] = captchaResponse;

        axios.tainacanApi.post('/collection/' + itemSubmission.collection_id + '/items/submission', {...item, metadata: itemSubmissionMetadata } )
            .then( res => {
                resolve( res.data.id );
            }).catch( error => { 
                reject({
                    errors: error.error.response.data.errors,
                    error_message: error.error.response.data.error_message
                });
            });
    }); 
}

/**
 * Uploads document/attachments and finalizes a previously submitted item.
 * Dispatches `item/finishItemSubmission`.
 * @returns {Promise<Object>} Resolves with finalized item response; rejects with API errors and error_message.
 */
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
        axios.tainacanApi.post('/collection/' + itemSubmission.collection_id + '/items/submission/' + fakeItemId + '/finish', formData, config )
            .then( res => {
                resolve( res.data );
            }).catch( error => {
                reject({
                    errors: error.error.response.data.errors,
                    error_message: error.error.response.data.error_message
                });
            });
    }); 
}

/**
 * Parses Abilities API run response for alt text (WordPress AI: ai/alt-text-generation).
 *
 * @param {*} data Response JSON body
 * @return {string|null}
 */
function extractAltTextFromAbilityResponse(data) {
    if (data == null || typeof data !== 'object') {
        return null;
    }
    if (typeof data.alt_text === 'string') {
        return data.alt_text;
    }
    if (data.result != null && typeof data.result === 'object' && typeof data.result.alt_text === 'string') {
        return data.result.alt_text;
    }
    if (data.output != null && typeof data.output === 'object' && typeof data.output.alt_text === 'string') {
        return data.output.alt_text;
    }
    return null;
}