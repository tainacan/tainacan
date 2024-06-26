import { createRouter, createWebHashHistory } from 'vue-router'
import qs from 'qs';

// Main Pages
const HomePage = () => import('../pages/home-page.vue');
const CollectionsPage = () => import('../pages/lists/collections-page.vue');
const CollectionPage = () => import('../pages/singles/collection-page.vue');
const ItemsPage = () => import('../pages/lists/items-page.vue');
const ItemPage = () => import('../pages/singles/item-page.vue');
const MetadataPage = () => import('../pages/lists/metadata-page.vue');
const FiltersPage = () => import('../pages/lists/filters-page.vue');
const TaxonomyPage = () => import('../pages/lists/taxonomies-page.vue');
const ActivitiesPage = () => import('../pages/lists/activities-page.vue');
const AvailableExportersPage = () => import('../pages/lists/available-exporters-page.vue');
const AvailableImportersPage = () => import('../pages/lists/available-importers-page.vue');
const CapabilitiesPage = () => import('../pages/lists/capabilities-page.vue');

// Edit Form Components
const CollectionEditionForm = () => import('../components/edition/collection-edition-form.vue');
const ImporterEditionForm = () => import('../components/edition/importer-edition-form.vue');
const ImporterMappingForm = () => import('../components/edition/importer-mapping-form.vue');
const ItemEditionForm = () => import('../components/edition/item-edition-form.vue');
const ItemBulkEditionForm = () => import('../components/edition/item-bulk-edition-form.vue');
const TaxonomyEditionForm = () => import('../components/edition/taxonomy-edition-form.vue');
const ExporterEditionForm = () => import('../components/edition/exporter-edition-form.vue');

const i18nGet = function (key) {
  let string = tainacan_plugin.i18n[key];
  return (string !== undefined && string !== null && string !== '' ) ? string : "ERROR: Invalid i18n key!";
};

const routes = [
    { path: '/', redirect: { name: 'HomePage' } },
    { path: '/home', name: 'HomePage', component: HomePage, meta: {title: 'Tainacan'} },

    { path: '/collections', name: 'CollectionsPage', component: CollectionsPage, meta: { title: i18nGet('title_repository_collections_page') } },
    { path: '/collections/new', name: 'CollectionCreationForm', component: CollectionEditionForm, meta: {title: i18nGet('title_create_collection') } },
    { path: '/collections/new/:mapper', name: 'MappedCollectionCreationForm', component: CollectionEditionForm, meta: {title: i18nGet('title_create_collection') } },

    { path: '/collections/:collectionId', component: CollectionPage, meta: {title: i18nGet('title_collection_page') },
      children: [
        /**
         * Previously, the first child route had a path: ''. Due to the following Vue3 update, we're doing it like that
         * https://router.vuejs.org/guide/migration/#Named-children-routes-with-an-empty-path-no-longer-appends-a-slash
         */
        { path: '/collections/:collectionId', redirect: { name: 'CollectionItemsPage' }},
        { path: 'items', component: ItemsPage, name: 'CollectionItemsPage', meta: {title: i18nGet('title_collection_page') }, props: { isOnTheme: false } },
        { path: 'items/:itemId/edit', name: 'ItemEditionForm', component: ItemEditionForm, meta: {title:  i18nGet('title_edit_item') } },
        { path: 'items/new', name: 'CollectionItemCreatePage', component: ItemEditionForm, meta: {title: i18nGet('title_create_item_collection') } },
        { path: 'items/:itemId', name: 'ItemPage', component: ItemPage, meta: {title: i18nGet('title_item_page') } },
        { path: 'bulk-add', name: 'CollectionItemBulkAddPage', component: ItemBulkEditionForm, meta: {title: i18nGet('title_item_bulk_add') } },
        { path: 'settings', component: CollectionEditionForm,  name: 'CollectionEditionForm', meta: {title: i18nGet('title_collection_settings') } },
        { path: 'metadata', component: MetadataPage, name: 'CollectionMetadataPage', meta: {title: i18nGet('title_collection_metadata_edit') } },
        { path: 'filters', component: FiltersPage, name: 'CollectionFiltersPage', meta: {title: i18nGet('title_collection_filters_edit') } },
        { path: 'activities', component: ActivitiesPage, name: 'CollectionActivitiesPage', meta: {title: i18nGet('title_collection_activities') } },
        { path: 'capabilities', component: CapabilitiesPage, name: 'CollectionCapabilitiesPage', meta: {title: i18nGet('title_collection_capabilities') } },
        { path: 'sequence/:sequenceId', name: 'SavedSequenceEditionForm', component: ItemEditionForm, meta: {title:  i18nGet('title_edit_item') } },
        { path: 'sequence/:sequenceId/:itemPosition', name: 'SequenceEditionForm', component: ItemEditionForm, meta: {title:  i18nGet('title_edit_item') } },
    ]
    },

    { path: '/items', name: 'RepositoryItemsPage', component: ItemsPage, meta: {title: i18nGet('title_items_page') } },
    { path: '/items/new', name: 'ItemCreationForm', component: ItemEditionForm, meta: {title: i18nGet('title_create_item') } },

    { path: '/filters', name: 'FiltersPage', component: FiltersPage, meta: {title: i18nGet('title_repository_filters_page') } },

    { path: '/metadata', name: 'MetadataPage', component: MetadataPage, meta: {title: i18nGet('title_repository_metadata_page') } },

    { path: '/taxonomies', name: 'TaxonomyPage', component: TaxonomyPage, meta: {title: i18nGet('title_taxonomies_page') } },
    { path: '/taxonomies/new', name: 'TaxonomyCreationForm', component: TaxonomyEditionForm, meta: {title: i18nGet('title_create_taxonomy_page') } },
    { path: '/taxonomies/:taxonomyId/edit', name: 'TaxonomyEditionForm', component: TaxonomyEditionForm, meta: {title: i18nGet('title_taxonomy_edit_page') } },
    { path: '/taxonomies/:taxonomyId', redirect: { name: 'TaxonomyEditionForm' } },

    { path: '/activities',  name: 'ActivitiesPage', component: ActivitiesPage, meta: {title: i18nGet('title_repository_activities_page') } },

    { path: '/capabilities', component: CapabilitiesPage, name: 'CapabilitiesPage', meta: {title: i18nGet('title_repository_capabilities') } },

    { path: '/importers/', name: 'AvailableImportersPage', component: AvailableImportersPage, meta: {title: i18nGet('title_importers_page') } },
    { path: '/importers/:importerSlug', name: 'ImporterEditionForm', component: ImporterEditionForm, meta: {title: i18nGet('title_importer_page') } },
    { path: '/importers/:importerSlug/:sessionId', name: 'ImporterCreationForm', component: ImporterEditionForm, meta: { title: i18nGet('title_importer_page') } },
    { path: '/importers/:importerType/:sessionId/mapping/:collectionId', name: 'ImporterMappingForm', component: ImporterMappingForm, meta: {title: i18nGet('title_importer_mapping_page') } },

    { path: '/exporters/', name: 'ExportersPage', component: AvailableExportersPage, meta: {title: i18nGet('title_exporters_page') } },
    { path: '/exporters/:exporterSlug', name: 'ExporterEditionForm', component: ExporterEditionForm, meta: {title: i18nGet('title_exporter_page') }},

    { path: '/:pathMatch(.*)*', name: 'DefaultRedirect', redirect: { name: 'HomePage' } }
];

export default createRouter({
    routes,
    history: createWebHashHistory(),
    // Set custom query resolver. Important for dealing with nested query params such as taxquery objects.
    parseQuery(query) {
        return qs.parse(query);
    },
    stringifyQuery(query) {
        let result = qs.stringify(query);
        return result ? result : '';
    }
});
