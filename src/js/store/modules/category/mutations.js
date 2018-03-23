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