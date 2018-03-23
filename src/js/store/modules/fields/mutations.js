import Vue from 'vue';

export const deleteField = ( state, field ) => {
    let index = state.fields.findIndex(deletedField => deletedField.id === field.id);
    if (index >= 0) {
        state.fields.splice(index, 1);
    }
}

export const setSingleField = (state, {field, index}) => {
    Vue.set( state.fields, index, field);
}

export const setFields = (state, fields) => {
    state.fields = fields;
}

export const setFieldTypes = (state, fieldTypes) => {
    state.fieldTypes = fieldTypes;
}