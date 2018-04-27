import axios from '../../../axios/axios'

// CATEGORIES
export const createCategory = ({commit}, category) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.post('/taxonomies', {
            name: category.name,
            description: category.description,
            status: category.status,
            slug: category.slug,
            allow_insert: category.allowInsert
        })
            .then( res => {
                let category = res.data;
                commit('setCategory', category);

                resolve( category );
            })
            .catch(error => {
                reject( error.response );
            });
    });
};

export const deleteCategory = ({ commit }, categoryId) => {
  return new Promise(( resolve, reject ) => {
      axios.tainacan.delete(`/taxonomies/${categoryId}?permanently=${true}`)
          .then(res => {
              commit('deleteCategory', res.data);

              resolve( res );
          })
          .catch(error => {
              reject( error )
          });
  });
};

export const updateCategory = ({ commit }, category) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.patch(`/taxonomies/${category.categoryId}`, {
            name: category.name,
            description: category.description,
            status: category.status,
            slug: category.slug ? category.slug : '',
            allow_insert: category.allowInsert
        })
            .then( res => {
                let category = res.data;

                commit('setCategory', category);

                resolve( category );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};

export const fetchCategories = ({ commit }, { page, categoriesPerPage } ) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/taxonomies?paged=${page}&perpage=${categoriesPerPage}`)
            .then(res => {
                let categories = res.data;

                commit('setCategories', categories);

                resolve({
                    'categories': categories,
                    'total': res.headers['x-wp-total']
                });
            })
            .catch(error => {
                reject(error);
            });
    });
};

export const fetchCategory = ({ commit }, categoryId) => {
    return new Promise((resolve, reject) => {
       axios.tainacan.get(`/taxonomies/${categoryId}`)
           .then(res => {
               let category = res.data;

               commit('setCategory', category);

               resolve({
                   'category': category
               })
           })
           .catch(error => {
               reject(error);
           })
    });
};

export const fetchCategoryName = ({ commit }, categoryId) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/taxonomies/${categoryId}?fetch_only=name`)
            .then(res => {
                let name = res.data;

                commit('setCategoryName');

                resolve(name.name)
            })
            .catch(error => {
                reject(error)
            })
    });
};

// CATEGORY TERMS
export const sendTerm = ({commit}, { categoryId, name, description, parent, headerImageId }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.post('/taxonomy/' + categoryId + '/terms/', {
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

export const deleteTerm = ({ commit }, { categoryId, termId }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.delete(`/taxonomy/${categoryId}/terms/${termId}?permanently=${true}`)
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

export const updateTerm = ({ commit }, { categoryId, termId, name, description, parent, headerImageId }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.patch(`/taxonomy/${categoryId}/terms/${termId}`, {
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

export const fetchTerms = ({ commit }, categoryId ) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get(`/taxonomy/${categoryId}/terms/?hideempty=0`)
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
