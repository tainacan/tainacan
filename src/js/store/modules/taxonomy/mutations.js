import Vue from 'vue';
import t from 't';

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

export const clearTerms = (state) => {
    state.terms = [];
};


export const setChildTerms = (state, { terms, parent }) => {
    
    if (parent > 0 ) {
        for (let term of state.terms) {
            let parentTerm = t.find(term, [], (node, par) => { return node.id == parent; });
            if (parentTerm != undefined) {
                if (parentTerm['children'] == undefined)
                    Vue.set(parentTerm, 'children', []);

                for (let term of terms){
                    parentTerm['children'].unshift(term);
                }     
            }
        }
    } else {
        if (state.terms != undefined) {
            
            for (let term of terms)
                state.terms.unshift(term);

        } else {    
            state.terms = terms;
        }
    }
};

export const updateChildTerm = (state, { term, parent }) => {
    
    if (parent > 0 ) {
        for (let aTerm of state.terms) {
            let childTerm = t.find(aTerm, [], (node, par) => { return node.id == term; });
            if (childTerm != undefined) {
                childTerm = term;   
            }
        }
    } else {
        if (state.terms != undefined) {
            for (let i = 0; i < state.terms.length; i++) {
                if (state.terms[i].id == term.id)
                    Vue.set(state.terms, i, term);
            }
        } else {    
            state.terms = []
            state.terms.unshift(term);
        }
    }
};

export const deleteChildTerm = ( state, {termId, parent} ) => {
    
    if (parent > 0 ) {
        for (let aTerm of state.terms) {
            let parentTerm = t.bfs(aTerm, [], (node, par) => { return node.id == parent; });
            if (parentTerm != undefined) {
                let index = parentTerm.children.findIndex(deletedTerm => deletedTerm.id == termId);
                if (index >= 0) {
                    parentTerm.children.splice(index, 1);
                }
            }
        }
    } else {
        if (state.terms != undefined) {
            for (let i = 0; i < state.terms.length; i++) {
                if (state.terms[i].id == termId)
                    state.terms.splice(i, 1);
            }
        }
    }
};

export const deleteTerm = ( state, termId ) => {
    let index = state.terms.findIndex(deletedTerm => deletedTerm.id === termId);
    if (index >= 0) {
        state.terms.splice(index, 1);
    }
};