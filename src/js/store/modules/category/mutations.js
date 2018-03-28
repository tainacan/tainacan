// CATEGORIES
export const setCategory = (state, category) => {
    state.category = category;
};

export const setCategories = (state, categories) => {
    state.categories = categories;
};

export const setCategoryName = (state, name) => {
    state.categoryName = name;
};

export const deleteCategory = ( state, category ) => {
    let index = state.categories.findIndex(deletedCategory => deletedCategory.id === category.id);

    if (index >= 0) {
        state.categories.splice(index, 1);
    }
};

// CATEGORY TERMS
export const setSingleTerm = (state, term) => {

    let index = state.terms.findIndex(updatedTerm => updatedTerm.id === term.id);
    if ( index >= 0){
        Vue.set( state.terms, index, term );
    } else {
        state.terms.push( term );
    }
};

export const setTerms = (state, terms) => {
    state.terms = terms;
};

export const deleteTerm = ( state, termId ) => {
    let index = state.terms.findIndex(deletedTerm => deletedTerm.id === termId);
    if (index >= 0) {
        state.terms.splice(index, 1);
    }
};