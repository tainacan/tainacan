import axios from '../../../axios/axios'

// TAXONOMIES
export const createTaxonomy = ({commit}, taxonomy) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.post('/taxonomies', {
            name: taxonomy.name,
            description: taxonomy.description,
            status: taxonomy.status,
            slug: taxonomy.slug,
            allow_insert: taxonomy.allowInsert
        })
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

export const deleteTaxonomy = ({ commit }, taxonomyId) => {
  return new Promise(( resolve, reject ) => {
      axios.tainacan.delete(`/taxonomies/${taxonomyId}?permanently=${true}`)
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
        axios.tainacan.patch(`/taxonomies/${taxonomy.taxonomyId}`, {
            name: taxonomy.name,
            description: taxonomy.description,
            status: taxonomy.status,
            slug: taxonomy.slug ? taxonomy.slug : '',
            allow_insert: taxonomy.allowInsert
        })
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

export const fetch = ({ commit }, { page, taxonomiesPerPage, status } ) => {
    return new Promise((resolve, reject) => {
        let endpoint = `/taxonomies?paged=${page}&perpage=${taxonomiesPerPage}&context=edit`;

        if (status != undefined && status != '')
            endpoint = endpoint + '&status=' + status;

        axios.tainacan.get(endpoint)
            .then(res => {
                let taxonomies = res.data;

                commit('set', taxonomies);

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
export const sendTerm = ({commit}, { taxonomyId, name, description, parent, headerImageId }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.post('/taxonomy/' + taxonomyId + '/terms/', {
            name: name,
            description: description,
            parent: parent,
            header_image_id: headerImageId,
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
        axios.tainacan.delete(`/taxonomy/${taxonomyId}/terms/${termId}?permanently=${true}`)
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

export const updateTerm = ({ commit }, { taxonomyId, termId, name, description, parent, headerImageId }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.patch(`/taxonomy/${taxonomyId}/terms/${termId}`, {
            name: name,
            description: description,
            parent: parent,
            header_image_id: headerImageId,
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

export const fetchTerms = ({ commit }, taxonomyId ) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/taxonomy/${taxonomyId}/terms/?hideempty=0&order=asc`)
            .then(res => {
                let terms = res.data;
                commit('setTerms', terms);
                resolve( terms );
            })
            .catch(error => {
                reject( error );
            });
    });
};
