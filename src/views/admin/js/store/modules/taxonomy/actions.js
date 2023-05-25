import axios from '../../../axios'
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

export const fetch = ({ commit }, { page, taxonomiesPerPage, status, order, orderby, search } ) => {
    return new Promise((resolve, reject) => {
        let endpoint = `/taxonomies?paged=${page}&perpage=${taxonomiesPerPage}&context=edit`;

        if (status != undefined && status != '' && status != false)
            endpoint = endpoint + '&status=' + status;
        else
            endpoint += '&status=publish,private,draft';

        if (order != undefined && order != '' && orderby != undefined && orderby != '')
            endpoint = endpoint + '&order=' + order + '&orderby=' + orderby;

        if (search != undefined && search != '')
            endpoint = endpoint + '&search=' + search;

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

export const fetchTaxonomy = ({ commit }, { taxonomyId, isContextEdit }) => {
    let endpoint = `/taxonomies/${taxonomyId}`;

    if (isContextEdit)
        endpoint += '?context=edit'

    return new Promise((resolve, reject) => {
       axios.tainacan.get(endpoint)
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
export const fetchTerms = ({}, {taxonomyId, fetchOnly, search, all, order, offset, number, exclude }) => {

    let query = '';

    if (order == undefined)
        order = 'asc';

    if (fetchOnly && search && !all )
        query = `?order=${order}&${qs.stringify(fetchOnly)}&${qs.stringify(search)}`;
    else if (fetchOnly && search && all )
        query = `?hideempty=0&order=${order}&${qs.stringify(fetchOnly)}&${qs.stringify(search)}`;
    else if (search && !all && !fetchOnly)
        query = `?hideempty=0&order=${order}&${qs.stringify(search)}`;
    else
        query =`?hideempty=0&order=${order}`;

    if (number != undefined)
        query += '&number=' + number;

    if (offset != undefined)
        query += '&offset=' + offset;

    if (exclude != undefined)
        query += '&' + qs.stringify({ exclude: exclude });

    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/taxonomy/${taxonomyId}/terms${query}`)
            .then(res => {
                resolve({ terms: res.data, total: res.headers['x-wp-total'] });
            })
            .catch(error => {
                reject( error );
            });
    });
};

export const sendChildTerm = ({ commit }, { taxonomyId, term }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.post(`/taxonomy/${taxonomyId}/terms/`, term)
            .then( res => {
                const newTerm = res.data;
                resolve( newTerm );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

export const updateTerm = ({}, { taxonomyId, term }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.patch(`/taxonomy/${taxonomyId}/terms/${term.id}`, term)
            .then( res => {
               const updatedTerm = res.data;
               resolve( updatedTerm );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

export const deleteTerm = ({}, { taxonomyId, termId, deleteChildTerms = false }) => {
    let query = 'permanently=1&hideempty=0';

    if ( deleteChildTerms )
        query += `&delete_child_terms=${deleteChildTerms}`;

    return new Promise(( resolve, reject ) => {
        axios.tainacan.delete(`/taxonomy/${taxonomyId}/terms/${termId}?${query}`)
            .then(res => {
                const term = res.data;
                resolve( term );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

export const deleteTerms = ({}, { taxonomyId, terms, parent, deleteChildTerms = false }) => {
    let query = `permanently=1&hideempty=0&number=0`;

    if ( parent !== undefined )
        query += `&parent=${parent}`;

    if ( terms.length )
        query += `&include=${terms}`;

    if ( deleteChildTerms )
        query += `&delete_child_terms=${deleteChildTerms}`;

    return new Promise(( resolve, reject ) => {
        axios.tainacan.delete(`/taxonomy/${taxonomyId}/terms/?${query}`)
            .then(res => {
                const terms = res.data;
                resolve( terms );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

export const changeTermsParent = ({}, { taxonomyId, newParentTerm, terms, parent }) => {
    let query = `hideempty=0&number=0`;

    if ( parent !== undefined )
        query += `&parent=${parent}`;

    if ( terms.length )
        query += `&include=${terms}`;

    return new Promise(( resolve, reject ) => {
        axios.tainacan.patch(`/taxonomy/${taxonomyId}/terms/newparent/${newParentTerm}?${query}`)
            .then(res => {
                const terms = res.data;
                resolve( terms );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

// Used only on Term Edit form, for autocomplete search for parents
export const fetchPossibleParentTerms = ({ commit }, { taxonomyId, termId, search, offset } ) => {

    const excludeTree = termId ? qs.stringify({ exclude_tree: termId }) : '';

    let endpoint = '/taxonomy/' + taxonomyId + '/terms?searchterm=' + search + '&hierarchical=1&hideempty=0&offset=0&number=20&order=asc&' + excludeTree;

    if (offset)
        endpoint += '&offset=' + offset;

    return new Promise((resolve, reject) => {
        axios.tainacan.get(endpoint)
        .then(res => {
            let parentTerms = res.data;

            const theParentIndex = parentTerms.findIndex((term) => term.id == termId);
            if (theParentIndex >= 0)
                parentTerms.splice(theParentIndex, 1);

            const totalTerms = res.headers['x-wp-total'];

            resolve( { parentTerms, totalTerms } );
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

export const multipleTermsInsertion = ({}, { taxonomyId, parent, termNames } ) => {
    const terms = termNames.map(aTermName => {
        return {
            parent: parent,
            name: aTermName
        }
    });

    return new Promise((resolve, reject) => {
        axios.tainacan.post('/taxonomy/' + taxonomyId + '/terms/bulkinsert', terms )
        .then(res => {
            resolve( res.data );
        })
        .catch(error => {
            reject(error['response']['data']);
        });
    });
};
