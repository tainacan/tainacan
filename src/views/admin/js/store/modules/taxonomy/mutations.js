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