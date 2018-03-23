import axios from '../../../axios/axios'

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

                console.log(category);
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
              console.info(res);

              commit('deleteCategory', res.data);

              resolve( res );
          })
          .catch(error => {
              console.error(error);

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
                reject( error.response );
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