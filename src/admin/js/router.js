import Vue from 'vue';
import VueRouter from 'vue-router'
import qs from 'qs';

// Main Pages
import CollectionsPage from '../pages/lists/collections-page.vue'
import CollectionPage from '../pages/singles/collection-page.vue'
import ItemsPage from '../pages/lists/items-page.vue'
import ItemPage from '../pages/singles/item-page.vue'
import MetadataPage from '../pages/lists/metadata-page.vue'
import FiltersPage from '../pages/lists/filters-page.vue'
import Page from '../pages/lists/taxonomies-page.vue'
import TaxonomyPage from '../pages/singles/taxonomy-page.vue'
import EventsPage from '../pages/lists/events-page.vue'
import EventPage from '../pages/singles/event-page.vue'
import ExportPage from '../pages/singles/export-page.vue'

// Edition Form Components
import CollectionEditionForm from '../components/edition/collection-edition-form.vue'
import ImporterEditionForm from '../components/edition/importer-edition-form.vue'
import ImporterMappingForm from '../components/edition/importer-mapping-form.vue'
import ItemEditionForm from '../components/edition/item-edition-form.vue'
import TaxonomyEditionForm from '../components/edition/taxonomy-edition-form.vue'
import AvailableImportersPage from '../pages/lists/available-importers-page.vue';

// Listing components
import FiltersList from '../components/lists/filters-list.vue'
import MetadataList from '../components/lists/metadata-list.vue'

Vue.use(VueRouter);

const i18nGet = function (key) {
  let string = tainacan_plugin.i18n[key];
  return (string !== undefined && string !== null && string !== '' ) ? string : "ERROR: Invalid i18n key!";
};

const routes = [                
    { path: '/', redirect:'/collections' },

    { path: '/collections', name: 'CollectionsPage', component: CollectionsPage, meta: {title: i18nGet('title_repository_collections_page'), icon: 'folder-multiple'} },
    { path: '/collections/new', name: 'CollectionCreationForm', component: CollectionEditionForm, meta: {title: i18nGet('title_create_collection'), icon: 'folder-multiple'} },
    { path: '/collections/new/:mapper', name: 'MappedCollectionCreationForm', component: CollectionEditionForm, meta: {title: i18nGet('title_create_collection'), icon: 'folder-multiple'} },
    
    { path: '/collections/:collectionId', component: CollectionPage, meta: {title: i18nGet('title_collection_page'), icon: 'folder-multiple'}, 
      children: [
        { path: '', redirect: 'items'},
        { path: 'items', component: ItemsPage, name: 'CollectionItemsPage', meta: {title: i18nGet('title_collection_page'), icon: 'folder-multiple'} }, 
        { path: 'items/:itemId/edit', name: 'ItemEditionForm', component: ItemEditionForm, meta: {title:  i18nGet('title_edit_item'), icon: 'folder-multiple'} },
        { path: 'items/new', name: 'CollectionItemCreatePage', component: ItemEditionForm, meta: {title: i18nGet('title_create_item_collection'), icon: 'folder-multiple'} },
        { path: 'items/:itemId', name: 'ItemPage', component: ItemPage, meta: {title: i18nGet('title_item_page'), icon: 'folder-multiple'} },   
        { path: 'settings', component: CollectionEditionForm,  name: 'CollectionEditionForm', meta: {title: i18nGet('title_collection_settings'), icon: 'folder-multiple'} },
        { path: 'metadata', component: MetadataList, name: 'MetadataList', meta: {title: i18nGet('title_collection_metadata_edition'), icon: 'folder-multiple'} },
        { path: 'filters', component: FiltersList, name: 'FiltersList', meta: {title: i18nGet('title_collection_filters_edition'), icon: 'folder-multiple'} },
        { path: 'events', component: EventsPage, name: 'CollectionEventsPage', meta: {title: i18nGet('title_collection_events'), icon: 'flash'} },
        { path: 'sequence/:sequenceId', name: 'SavedSequenceEditionForm', component: ItemEditionForm, meta: {title:  i18nGet('title_edit_item'), icon: 'folder-multiple'} },
        { path: 'sequence/:sequenceId/:itemPosition', name: 'SequenceEditionForm', component: ItemEditionForm, meta: {title:  i18nGet('title_edit_item'), icon: 'folder-multiple'} },
    ]
    },

    { path: '/items', name: 'ItemsPage', component: ItemsPage, meta: {title: i18nGet('title_items_page'), icon: 'file-multiple'} },
    { path: '/items/new', name: 'ItemCreationForm', component: ItemEditionForm, meta: {title: i18nGet('title_create_item'), icon: 'file-multiple'} },

    { path: '/filters', name: 'FiltersPage', component: FiltersPage, meta: {title: i18nGet('title_repository_filters_page'), icon: 'filter'} },

    { path: '/metadata', name: 'MetadataPage', component: MetadataPage, meta: {title: i18nGet('title_repository_metadata_page'), icon: 'format-list-checks'} },

    { path: '/taxonomies', name: 'Page', component: Page, meta: {title: i18nGet('title_taxonomies_page'), icon: 'shape'} },
    { path: '/taxonomies/new', name: 'TaxonomyCreationForm', component: TaxonomyEditionForm, meta: {title: i18nGet('title_create_taxonomy_page'), icon: 'shape'} },
    { path: '/taxonomies/:taxonomyId/edit', name: 'TaxonomyEditionForm', component: TaxonomyEditionForm, meta: {title: i18nGet('title_taxonomy_edition_page'), icon: 'shape'} },
    { path: '/taxonomies/:taxonomyId', redirect: '/taxonomies/:taxonomyId/edit' },

    { path: '/events',  name: 'EventsPage', component: EventsPage, meta: {title: i18nGet('title_repository_events_page'), icon: 'flash'} },
    { path: '/events/:eventId', name: 'EventPage', component: EventPage, meta: {title: i18nGet('title_event_page'), icon: 'flash'} },

    { path: '/importers/', name: 'AvailableImportersPage', component: AvailableImportersPage, meta: {title: i18nGet('title_importers_page'), icon: 'import'} },
    { path: '/importers/:importerSlug', name: 'ImporterEditionForm', component: ImporterEditionForm, meta: {title: i18nGet('title_importer_page'), icon: 'import'} },
    { path: '/importers/:importerSlug/:sessionId', name: 'ImporterCreationForm', component: ImporterEditionForm, meta: { title: i18nGet('title_importer_page'), icon: 'import' } },
    { path: '/importers/:importerType/:sessionId/mapping/:collectionId', name: 'ImporterMappingForm', component: ImporterMappingForm, meta: {title: i18nGet('title_importer_mapping_page'), icon: 'import'} },

    { path: '/export/collection/:collectionId', name: 'ExportCollection', component: ExportPage, meta: {title: i18nGet('title_export_collection_page'), icon: 'export'} },
    { path: '/export/item/:itemId', name: 'ExportItem', component: ExportPage, meta: {title: i18nGet('title_export_item_page'), icon: 'export'} },
    { path: '/export', name: 'Export', component: ExportPage, meta: {title: i18nGet('title_export_page'), icon: 'export'} },

    { path: '*', redirect: '/'}
];

export default new VueRouter ({
    routes,
    // set custom query resolver
    parseQuery(query) {
        return qs.parse(query);
    },
    stringifyQuery(query) {
        let result = qs.stringify(query);

        return result ? ('?' + result) : '';
    }
});
