import Vue from 'vue';
import VueRouter from 'vue-router'
import qs from 'qs';

// Main Pages
import HomePage from '../pages/home-page.vue'
import CollectionsPage from '../pages/lists/collections-page.vue'
import CollectionPage from '../pages/singles/collection-page.vue'
import ItemsPage from '../pages/lists/items-page.vue'
import ItemPage from '../pages/singles/item-page.vue'
import MetadataPage from '../pages/lists/metadata-page.vue'
import FiltersPage from '../pages/lists/filters-page.vue'
import TaxonomyPage from '../pages/lists/taxonomies-page.vue'
import ActivitiesPage from '../pages/lists/activities-page.vue'
import AvailableExportersPage from '../pages/lists/available-exporters-page.vue'
import AvailableImportersPage from '../pages/lists/available-importers-page.vue'
import CapabilitiesPage from '../pages/lists/capabilities-page.vue'

// Edition Form Components
import CollectionEditionForm from '../components/edition/collection-edition-form.vue'
import ImporterEditionForm from '../components/edition/importer-edition-form.vue'
import ImporterMappingForm from '../components/edition/importer-mapping-form.vue'
import ItemEditionForm from '../components/edition/item-edition-form.vue'
import ItemBulkEditionForm from '../components/edition/item-bulk-edition-form.vue'
import TaxonomyEditionForm from '../components/edition/taxonomy-edition-form.vue'
import ExporterEditionForm from '../components/edition/exporter-edition-form.vue'

// Listing components
import FiltersList from '../components/lists/filters-list.vue'
import MetadataList from '../components/lists/metadata-list.vue'

Vue.use(VueRouter);

const i18nGet = function (key) {
  let string = tainacan_plugin.i18n[key];
  return (string !== undefined && string !== null && string !== '' ) ? string : "ERROR: Invalid i18n key!";
};

const routes = [                
    { path: '/', redirect:'/home' },
    { path: '/home', name: 'HomePage', component: HomePage, meta: {title: 'Tainacan'} },

    { path: '/collections', name: 'CollectionsPage', component: CollectionsPage, meta: { title: i18nGet('title_repository_collections_page') } },
    { path: '/collections/new', name: 'CollectionCreationForm', component: CollectionEditionForm, meta: {title: i18nGet('title_create_collection') } },
    { path: '/collections/new/:mapper', name: 'MappedCollectionCreationForm', component: CollectionEditionForm, meta: {title: i18nGet('title_create_collection') } },
    
    { path: '/collections/:collectionId', component: CollectionPage, meta: {title: i18nGet('title_collection_page') }, 
      children: [
        { path: '', redirect: 'items'},
        { path: 'items', component: ItemsPage, name: 'CollectionItemsPage', meta: {title: i18nGet('title_collection_page') }, props: { isOnTheme: false } }, 
        { path: 'items/:itemId/edit', name: 'ItemEditionForm', component: ItemEditionForm, meta: {title:  i18nGet('title_edit_item') } },
        { path: 'items/new', name: 'CollectionItemCreatePage', component: ItemEditionForm, meta: {title: i18nGet('title_create_item_collection') } },
        { path: 'items/:itemId', name: 'ItemPage', component: ItemPage, meta: {title: i18nGet('title_item_page') } },   
        { path: 'bulk-add', name: 'CollectionItemBulkAddPage', component: ItemBulkEditionForm, meta: {title: i18nGet('title_item_bulk_add') } },
        { path: 'settings', component: CollectionEditionForm,  name: 'CollectionEditionForm', meta: {title: i18nGet('title_collection_settings') } },
        { path: 'metadata', component: MetadataPage, name: 'CollectionMetadataPage', meta: {title: i18nGet('title_collection_metadata_edition') } },
        { path: 'filters', component: FiltersPage, name: 'CollectionFiltersPage', meta: {title: i18nGet('title_collection_filters_edition') } },
        { path: 'activities', component: ActivitiesPage, name: 'CollectionActivitiesPage', meta: {title: i18nGet('title_collection_activities') } },
        { path: 'capabilities', component: CapabilitiesPage, name: 'CollectionCapabilitiesPage', meta: {title: i18nGet('title_collection_capabilities') } },
        { path: 'sequence/:sequenceId', name: 'SavedSequenceEditionForm', component: ItemEditionForm, meta: {title:  i18nGet('title_edit_item') } },
        { path: 'sequence/:sequenceId/:itemPosition', name: 'SequenceEditionForm', component: ItemEditionForm, meta: {title:  i18nGet('title_edit_item') } },
    ]
    },

    { path: '/items', name: 'ItemsPage', component: ItemsPage, meta: {title: i18nGet('title_items_page') } },
    { path: '/items/new', name: 'ItemCreationForm', component: ItemEditionForm, meta: {title: i18nGet('title_create_item') } },

    { path: '/filters', name: 'FiltersPage', component: FiltersPage, meta: {title: i18nGet('title_repository_filters_page') } },

    { path: '/metadata', name: 'MetadataPage', component: MetadataPage, meta: {title: i18nGet('title_repository_metadata_page') } },

    { path: '/taxonomies', name: 'TaxonomyPage', component: TaxonomyPage, meta: {title: i18nGet('title_taxonomies_page') } },
    { path: '/taxonomies/new', name: 'TaxonomyCreationForm', component: TaxonomyEditionForm, meta: {title: i18nGet('title_create_taxonomy_page') } },
    { path: '/taxonomies/:taxonomyId/edit', name: 'TaxonomyEditionForm', component: TaxonomyEditionForm, meta: {title: i18nGet('title_taxonomy_edition_page') } },
    { path: '/taxonomies/:taxonomyId', redirect: '/taxonomies/:taxonomyId/edit' },

    { path: '/activities',  name: 'ActivitiesPage', component: ActivitiesPage, meta: {title: i18nGet('title_repository_activities_page') } },

    { path: '/capabilities', component: CapabilitiesPage, name: 'CapabilitiesPage', meta: {title: i18nGet('title_repository_capabilities') } },

    { path: '/importers/', name: 'AvailableImportersPage', component: AvailableImportersPage, meta: {title: i18nGet('title_importers_page') } },
    { path: '/importers/:importerSlug', name: 'ImporterEditionForm', component: ImporterEditionForm, meta: {title: i18nGet('title_importer_page') } },
    { path: '/importers/:importerSlug/:sessionId', name: 'ImporterCreationForm', component: ImporterEditionForm, meta: { title: i18nGet('title_importer_page') } },
    { path: '/importers/:importerType/:sessionId/mapping/:collectionId', name: 'ImporterMappingForm', component: ImporterMappingForm, meta: {title: i18nGet('title_importer_mapping_page') } },

    { path: '/exporters/', name: 'ExportersPage', component: AvailableExportersPage, meta: {title: i18nGet('title_exporters_page') } },
    { path: '/exporters/:exporterSlug', name: 'ExporterEditionForm', component: ExporterEditionForm, meta: {title: i18nGet('title_exporter_page') }},

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
