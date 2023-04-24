import Vue from 'vue';

export const setRepositoryTotalTaxonomies = (state, repositoryTotalTaxonomies) => {
    state.repositoryTotalTaxonomies = repositoryTotalTaxonomies;
};

// TAXONOMIES
export const setTaxonomy = (state, taxonomy) => {
    state.taxonomy = taxonomy;
};

export const set = (state, taxonomies) => {
    state.taxonomies = taxonomies;
};

export const setTaxonomyName = (state, name) => {
    state.taxonomyName = name;
};

export const deleteTaxonomy = ( state, taxonomy ) => {
    let index = state.taxonomies.findIndex(deletedTaxonomy => deletedTaxonomy.id === taxonomy.id);

    if (index >= 0) {
        state.taxonomies.splice(index, 1);
    }
};

// TAXONOMY TERMS
export const setSingleTerm = (state, term) => {

    let index = state.terms.findIndex(updatedTerm => updatedTerm.id === term.id);
    if ( index >= 0 )
        Vue.set( state.terms, index, term );
    else
        state.terms.push( term );
};

export const setTerms = (state, terms) => {

    for (let term of terms) {
        let existingTermIndex = state.terms.findIndex(aTerm => aTerm.id == term.id);
        if (existingTermIndex >= 0)
            Vue.set(state.terms, existingTermIndex, term);
        else
            state.terms.push(term);
    }

};

export const deleteTerm = ( state, termId ) => {
    let index = state.terms.findIndex(deletedTerm => deletedTerm.id === termId);
    if (index >= 0) {
        state.terms.splice(index, 1);
    }
};