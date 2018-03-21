import axios from '../../../axios/axios'

export const createCategory = ({commit}, category) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.post('/taxonomies/', {
            name: category.name,
            description: category.description,
            status: category.status,
            slug: category.slug,
            allow_insert: category.allow_insert
        })
            .then( res => {
                commit('setCategory', category);
                resolve( res.data );
            })
            .catch(error => {
                reject( error.response );
            });
    });
};

export const fetchCategories = ({ commit }, { page, categoriesPerPage } ) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get('/taxonomies?paged='+ page +'&perpage='+ categoriesPerPage)
            .then(res => {
                let categories = res.data;
                commit('setCategories', categories);
                resolve({'categories': categories, 'total': res.headers['x-wp-total'] });
            })
            .catch(error => {
                console.log(error);
                reject(error);
            });
    });
};