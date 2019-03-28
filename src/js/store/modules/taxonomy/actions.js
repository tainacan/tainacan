import axios from '../../../axios/axios'
import qs from 'qs'

// TAXONOMIES
export const createTaxonomy = ({commit}, taxonomy) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.post('/taxonomies', taxonomy)
            .then( res => {
                let taxonomy = res.data;
                commit('setTaxonomy', taxonomy);

                resolve( taxonomy );
            })
            .catch(error => {
                reject( error.response );
            });
    });
};

export const deleteTaxonomy = ({ commit }, { taxonomyId, isPermanently }) => {
  return new Promise(( resolve, reject ) => {
      axios.tainacan.delete(`/taxonomies/${taxonomyId}?permanently=` + (isPermanently ? '1' : '0'))
          .then(res => {
              commit('deleteTaxonomy', res.data);

              resolve( res );
          })
          .catch(error => {
              reject( error )
          });
  });
};

export const updateTaxonomy = ({ commit }, taxonomy) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.patch(`/taxonomies/${taxonomy.taxonomyId}`, taxonomy)
            .then( res => {
                let taxonomy = res.data;
                commit('setTaxonomy', taxonomy);
                resolve( taxonomy );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

export const fetch = ({ commit }, { page, taxonomiesPerPage, status, order, orderby } ) => {
    return new Promise((resolve, reject) => {
        let endpoint = `/taxonomies?paged=${page}&perpage=${taxonomiesPerPage}&context=edit`;

        if (status != undefined && status != '')
            endpoint = endpoint + '&status=' + status;
        
        if (order != undefined && order != '' && orderby != undefined && orderby != '')
            endpoint = endpoint + '&order=' + order + '&orderby=' + orderby;

        axios.tainacan.get(endpoint)
            .then(res => {
                let taxonomies = res.data;

                commit('set', taxonomies);

                commit('setRepositoryTotalTaxonomies', {
                    draft: res.headers['x-tainacan-total-taxonomies-draft'],
                    trash: res.headers['x-tainacan-total-taxonomies-trash'],
                    publish: res.headers['x-tainacan-total-taxonomies-publish'],
                    private: res.headers['x-tainacan-total-taxonomies-private'],
                });

                resolve({
                    'taxonomies': taxonomies,
                    'total': res.headers['x-wp-total']
                });
            })
            .catch(error => {
                reject(error);
            });
    });
};

export const fetchTaxonomy = ({ commit }, taxonomyId) => {
    return new Promise((resolve, reject) => {
       axios.tainacan.get(`/taxonomies/${taxonomyId}`)
           .then(res => {
               let taxonomy = res.data;

               commit('setTaxonomy', taxonomy);

               resolve({
                   'taxonomy': taxonomy
               })
           })
           .catch(error => {
               reject(error);
           })
    });
};

export const fetchTaxonomyName = ({ commit }, taxonomyId) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/taxonomies/${taxonomyId}?fetch_only=name`)
            .then(res => {
                let name = res.data;

                commit('setTaxonomyName');
                resolve(name.name)
            })
            .catch(error => {
                reject(error)
            })
    });
};

// TAXONOMY TERMS
export const sendTerm = ({commit}, { taxonomyId, name, description, parent, headerImageId, headerImage }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.post('/taxonomy/' + taxonomyId + '/terms/', {
            name: name,
            description: description,
            parent: parent,
            header_image_id: headerImageId,
            header_image: headerImage
        })
            .then( res => {
                let term = res.data;
                commit('setSingleTerm', term);
                resolve( term );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

export const deleteTerm = ({ commit }, { taxonomyId, termId }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.delete(`/taxonomy/${taxonomyId}/terms/${termId}?permanently=1`)
            .then(res => {
                let term = res.data;
                commit('deleteTerm', termId);
                resolve( term );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

export const updateTerm = ({ commit }, { taxonomyId, id, name, description, parent, headerImageId, headerImage }) => {

    return new Promise(( resolve, reject ) => {
        axios.tainacan.patch(`/taxonomy/${taxonomyId}/terms/${id}`, {
            name: name,
            description: description,
            parent: parent,
            header_image_id: headerImageId,
            header_image: headerImage,
        })
            .then( res => {
                let term = res.data;
                commit('setSingleTerm', term);
                resolve( term );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

export const fetchTerms = ({ commit }, {taxonomyId, fetchOnly, search, all, order, offset, number}) => {
    
    let query = '';
    if (order == undefined) {
        order = 'asc';
    }

    if(fetchOnly && search && !all ){
        query = `?order=${order}&${qs.stringify(fetchOnly)}&${qs.stringify(search)}`;
    } else if(fetchOnly && search && all ){ 
        query = `?hideempty=0&order=${order}&${qs.stringify(fetchOnly)}&${qs.stringify(search)}`;
    } else if(search && !all && !fetchOnly){ 
        query = `?hideempty=0&order=${order}&${qs.stringify(search)}`;
    } else {
        query =`?hideempty=0&order=${order}`;
    }

    if (offset != undefined && number != undefined) {
        query += '&offset=' + offset + '&number=' + number;
    }

    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/taxonomy/${taxonomyId}/terms${query}`)
            .then(res => {
                let terms = res.data;
                commit('setTerms', terms);
                resolve({ terms: terms, total: res.headers['x-wp-total'] });
            })
            .catch(error => {
                reject( error );
            });
    });
};

// Hierarchy usage of terms list -----------------
export const fetchChildTerms = ({ commit }, { parentId, taxonomyId, fetchOnly, search, all, order, offset, number }) => {

    let query = '';
    if (order == undefined) {
        order = 'asc';
    }

    if(fetchOnly && search && !all ){
        query = `?order=${order}&${qs.stringify(fetchOnly)}&${qs.stringify(search)}`;
    } else if(fetchOnly && search && all ){ 
        query = `?hideempty=0&order=${order}&${qs.stringify(fetchOnly)}&${qs.stringify(search)}`;
    } else if(search && !all && !fetchOnly){ 
        query = `?hideempty=0&order=${order}&${qs.stringify(search)}`;
    } else {
        query =`?hideempty=0&order=${order}`;
    }

    query += '&parent=' + parentId;

    if (offset != undefined && number != undefined) {
        query += '&offset=' + offset + '&number=' + number;
    }
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/taxonomy/${taxonomyId}/terms${query}`)
            .then(res => {
                let terms = res.data;
                commit('setChildTerms', { terms: terms, parent: parentId });
                resolve({ terms: terms, total: res.headers['x-wp-total'] });
            })
            .catch(error => {
                reject(error);
            });
    });
};

export const sendChildTerm = ({ commit }, { taxonomyId, term }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.post(`/taxonomy/${taxonomyId}/terms/`, term)
            .then( res => {
                let newTerm = res.data;
                commit('addChildTerm', {term: newTerm, parent: term.parent });
                resolve( newTerm );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

export const updateChildTerm = ({ commit }, { taxonomyId, term }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.patch(`/taxonomy/${taxonomyId}/terms/${term.id}`, term)
            .then( res => {
                let updatedTerm = res.data;
                commit('updateChildTerm', { term: updatedTerm, parent: updatedTerm.parent, oldParent: term.parent });
                resolve( term );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

// Used to update parent changes after deletion only locally
export const updateChildTermLocal = ({ commit }, { term, parent, oldParent }) => {  
    commit('updateChildTerm', { term: term, parent: parent, oldParent: oldParent });
};

export const deleteChildTerm = ({ commit }, { taxonomyId, termId, parent }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.delete(`/taxonomy/${taxonomyId}/terms/${termId}?permanently=1`)
            .then(res => {
                let term = res.data;
                commit('deleteChildTerm', { termId: termId, parent: parent });
                resolve( term );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};


export const clearTerms = ({ commit }) => {
    commit('clearTerms');
};

// Used only on Term Edition form, for autocomplete searhc for parents
export const fetchPossibleParentTerms = ({ commit }, { taxonomyId, termId, search } ) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get('/taxonomy/' + taxonomyId + '/terms?searchterm=' + search + '&hierarchical=1&exclude_tree=' + termId + "&hideempty=0&offset=0&number=20")
        .then(res => {
            let parentTerms = res.data;
            resolve( parentTerms );
        })
        .catch(error => {
            reject( error );
        });
    });
};
export const fetchParentName = ({ commit }, { taxonomyId, parentId } ) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get('/taxonomy/' + taxonomyId + '/terms/' + parentId + '?fetch_only=name')
        .then(res => {
            let parentName = res.data.name;
            resolve( parentName );
        })
        .catch(error => {
            reject( error );
        });
    });
};
