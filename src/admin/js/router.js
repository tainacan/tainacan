import Vue from 'vue';
import VueRouter from 'vue-router'

// Main Pages
import AdminPage from '../admin.vue'
import CollectionsPage from '../pages/lists/collections-page.vue'
import CollectionPage from '../pages/singles/collection-page.vue'
import ItemsPage from '../pages/lists/items-page.vue'
import ItemPage from '../pages/singles/item-page.vue'
import FieldsPage from '../pages/lists/fields-page.vue'
import FiltersPage from '../pages/lists/filters-page.vue'
import CategoriesPage from '../pages/lists/categories-page.vue'
import EventsPage from '../pages/lists/events-page.vue'

// Edition Form Components
import CollectionEditionForm from '../components/edition/collection-edition-form.vue'
import ItemEditionForm from '../components/edition/item-edition-form.vue'
import FilterEditionForm from '../components/edition/filter-edition-form.vue'
import CategoryEditionForm from '../components/edition/category-edition-form.vue'

// Listing components
import CollectionsList from '../components/lists/collections-list.vue'
import ItemsList from '../components/lists/items-list.vue'
import FiltersList from '../components/lists/filters-list.vue'
import CategoriesList from '../components/lists/categories-list.vue'
import FieldsList from '../components/lists/fields-list.vue'

Vue.use(VueRouter);

const i18nGet = function (key) {
  let string = tainacan_plugin.i18n[key];
  return (string != undefined && string != null && string != '' ) ? string : "ERROR: Invalid i18n key!";
}

const routes = [
    { path: '/', redirect:'/collections' },

    { path: '/collections', name: 'CollectionsPage', component: CollectionsPage, meta: {title: i18nGet('title_collections_page'), icon: 'folder-multiple'} },
    { path: '/collections/new', name: 'CollectionEditionForm', component: CollectionEditionForm, meta: {title: i18nGet('title_create_collection'), icon: 'folder-multiple'} },
    
    { path: '/collections/:collectionId', name: 'CollectionPage', component: CollectionPage, meta: {title: i18nGet('title_collection_page'), icon: 'folder-multiple'}, 
      children: [
        { path: '', redirect: 'items'},
        { path: 'items', component: ItemsPage, name: 'CollectionItemsPage', meta: {title: i18nGet('title_collection_page'), icon: 'folder-multiple'} }, 
        { path: 'items/:itemId/edit', name: 'ItemEditionForm', component: ItemEditionForm, meta: {title:  i18nGet('title_item_edition'), icon: 'folder-multiple'} },
        { path: 'items/new', name: 'ItemCreatePage', component: ItemEditionForm, meta: {title: i18nGet('title_create_item'), icon: 'folder-multiple'} },
        { path: 'items/:itemId', name: 'ItemPage', component: ItemPage, meta: {title: i18nGet('title_item_page'), icon: 'folder-multiple'} },   
        { path: 'edit', component: CollectionEditionForm,  name: 'CollectionEditionForm', meta: {title: i18nGet('title_collection_edition'), icon: 'folder-multiple'} },
        { path: 'fields', component: FieldsList, name: 'FieldsList', meta: {title: i18nGet('title_collection_fields_edition'), icon: 'folder-multiple'} }, 
        { path: 'filters', component: FiltersList, name: 'FiltersList', meta: {title: i18nGet('title_collection_page'), icon: 'folder-multiple'} }
      ]
    },

    { path: '/items', name: 'ItemsPage', component: ItemsPage, meta: {title: i18nGet('title_items_page'), icon: 'file-multiple'} },
    { path: '/items/new', name: 'ItemEditionForm', component: ItemEditionForm, meta: {title: i18nGet('title_create_item'), icon: 'file-multiple'} },

    { path: '/filters', name: 'FiltersPage', component: FiltersPage, meta: {title: i18nGet('title_filters_page'), icon: 'filter'} },

    { path: '/fields', name: 'FieldsPage', component: FieldsPage, meta: {title: i18nGet('title_fields_page'), icon: 'format-list-checks'} },

    { path: '/categories', name: 'CategoriesPage', component: CategoriesPage, meta: {title: i18nGet('title_categories_page'), icon: 'shape'} },
    { path: '/categories/new', name: 'CategoryEditionForm', component: CategoryEditionForm, meta: {title: i18nGet('title_create_category_page'), icon: 'shape'} },
    { path: '/categories/:categoryId/edit', name: 'CategoryEditionForm', component: CategoryEditionForm, meta: {title: i18nGet('title_category_edition_page'), icon: 'shape'} },

    { path: '/events',  name: 'EventsPage', component: EventsPage, meta: {title: i18nGet('title_events_page'), icon: 'bell'} },

    { path: '*', redirect: '/'}
]

export default new VueRouter ({
    routes
})