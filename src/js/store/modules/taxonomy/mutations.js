import Vue from 'vue';

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
    if ( index >= 0){
        Vue.set( state.terms, index, term );
    } else {
        state.terms.push( term );
    }
};

export const setTerms = (state, terms) => {
    state.terms = terms;
};

export const setChildTerms = (state, { terms, parent }) => {
    let index = state.terms.findIndex(aTerm => aTerm.id == parent);
    if (index >= 0) {
        let newIndex = 1;
        for (let i = 0; i < terms.length; i++) {
            let termIndex = state.terms.findIndex(aTerm => aTerm.id == terms[i].id);
            if (termIndex >= 0) {
                state.terms[termIndex] = terms[i];
            } else {
                state.terms.splice(index + newIndex, 0, terms[i]);
                newIndex++;
            }
        }
    } else {
        state.terms = terms;
    }
};

export const deleteTerm = ( state, termId ) => {
    let index = state.terms.findIndex(deletedTerm => deletedTerm.id === termId);
    if (index >= 0) {
        state.terms.splice(index, 1);
    }
};