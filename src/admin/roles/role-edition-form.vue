<template>
    <div>
        <h1 class="wp-heading-inline">{{ $route.meta.title }}&nbsp;<strong>{{ role.name }}</strong></h1>
        <hr class="wp-header-end">
        <br>
        <template v-if="!isLoadingRole">
            <div class="name-edition-box">
                <h2 id="role-name-label">{{ $i18n.get('Role name') }}</h2>
                <input
                    aria-labelledby="role-name-label" 
                    type="text" 
                    id="rolen-name-input" 
                    name="name" 
                    @input="onUpdateRoleName($event.target.value)"
                    :value="role.name" 
                    :placeholder="$i18n.get('Type here the role name...')">
            </div>
        </template>
        <template v-if="!isLoadingRole && !isLoadingCapabilities">
            <br>
            <h2>{{ $i18n.get('Role\'s Repository related Capabilities List') }}</h2>
            <div class="capabilities-list">
                <div
                        class="capability-group"
                        v-for="(group, groupIndex) of groupedRepositoryCapabilities"
                        :key="groupIndex">
                    <h3>{{ groupIndex }}</h3>
                    <ul>
                        <li
                                v-for="(capability, index) of group"
                                :key="index"
                                :id="'capability-' + capability">
                            <span
                                    scope="row"
                                    class="check-column">
                                <label
                                        class="screen-reader-text"
                                        :for="'capability_' + capability">
                                    {{ $i18n.get('Selecionar') + ' ' + repositoryCapabilities[capability].display_name }}
                                </label>
                                <input
                                    type="checkbox"
                                    name="roles[]"
                                    :id="'capability_'+ capability"
                                    :disabled="repositoryCapabilities[capability].supercaps.length > 0 && repositoryCapabilities[capability].supercaps.findIndex((supercap) => role.capabilities[supercap] == true) >= 0"
                                    :checked="role.capabilities[capability] || (repositoryCapabilities[capability].supercaps.length > 0 && repositoryCapabilities[capability].supercaps.findIndex((supercap) => role.capabilities[supercap] == true) >= 0)"
                                    @input="onUpdateCapability($event.target.checked, capability)">
                            </span>
                            <span 
                                    class="name column-name"
                                    :data-colname="$i18n.get('Capability name')">
                                <strong>{{ repositoryCapabilities[capability].display_name }}</strong>
                                <help-button 
                                        :title="repositoryCapabilities[capability].display_name"
                                        :message="repositoryCapabilities[capability].description"
                                        :super-caps="repositoryCapabilities[capability].supercaps"
                                        :capabilities="capabilities"/>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <br>

            <template v-if="!isLoadingCollections"> 

                <h2>{{ $i18n.get('Role\'s Collection related Capabilities List') }}</h2>

                <div class="tablenav top">
                    <div class="alignleft collection-selector">
                        <label 
                                for="bulk-action-selector-top" 
                                class="screen-reader-text">
                            {{ $i18n.get('Select the collection to change capabilities') }}
                        </label>
                        <select 
                                name="collection" 
                                id="collection-select"
                                :value="selectedCollection"
                                @input="selectedCollection = $event.target.value">
                            <option value="all">{{ $i18n.get('All Collections') }}</option>
                            <option 
                                    :key="index"
                                    v-for="(collection, index) of collections"
                                    :value="collection.id">
                                {{ collection.name }}
                            </option>
                        </select>    
                    </div>
                    <br class="clear">
                </div>

                <div class="capabilities-list">
                    <div
                            class="capability-group"
                            v-for="(group, groupIndex) of groupedCollectionCapabilities"
                            :key="groupIndex">
                        <h3>{{ groupIndex }}</h3>
                        <ul>
                            <li
                                    v-for="(capability, index) of group"
                                    :key="index"
                                    :id="'capability-' + capability.replace('%d', selectedCollection)">
                                <span
                                        scope="row"
                                        class="check-column">
                                    <label
                                            class="screen-reader-text"
                                            :for="'capability_' + capability.replace('%d', selectedCollection)">
                                        {{ $i18n.get('Selecionar') + ' ' + collectionCapabilities[capability].display_name }}
                                    </label>
                                    <input
                                        type="checkbox"
                                        name="roles[]"
                                        :id="'capability_'+ capability.replace('%d', selectedCollection)"
                                        :disabled="collectionCapabilities[capability].supercaps.length > 0 && collectionCapabilities[capability].supercaps.findIndex((supercap) => role.capabilities[supercap.replace('%d', selectedCollection)] == true && capability.replace('%d', selectedCollection) != 'manage_tainacan_collection_all') >= 0"
                                        :checked="role.capabilities[capability.replace('%d', selectedCollection)] || (collectionCapabilities[capability].supercaps.length > 0 && collectionCapabilities[capability].supercaps.findIndex((supercap) => role.capabilities[supercap.replace('%d', selectedCollection)] == true) >= 0)"
                                        @input="onUpdateCapability($event.target.checked, capability.replace('%d', selectedCollection))">
                                </span>
                                <span 
                                        class="name column-name"
                                        :data-colname="$i18n.get('Capability name')">
                                    <strong>{{ collectionCapabilities[capability].display_name }}</strong>
                                    <help-button 
                                            :title="collectionCapabilities[capability].display_name"
                                            :message="collectionCapabilities[capability].description"
                                            :super-caps="collectionCapabilities[capability].supercaps"
                                            :capabilities="capabilities"/>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </template>
        </template>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';

    export default {
        data() {
            return {
                isUpdatingRole: false,
                isLoadingRole: false,
                isLoadingCapabilities: false,
                selectedCollection: 'all',
                collections: [],
                isLoadingCollections: false
            }
        },
        computed: {
            role() {
                return this.getRole()
            },
            capabilities() {
                return this.getCapabilities();
            },
            collectionCapabilities() {
                let collectionCapabilities = {}

                for (let [capabilityKey, capability] of Object.entries(this.capabilities)) {
                    if (capability.scope === 'collection')
                        collectionCapabilities[capabilityKey] = capability;
                }
                return collectionCapabilities;
            },
            repositoryCapabilities() {
                let repositoryCapabilities = {}
                for (let [capabilityKey, capability] of Object.entries(this.capabilities)) {
                    if (capability.scope === 'repository')
                        repositoryCapabilities[capabilityKey] = capability;
                }
                return repositoryCapabilities;
            },
            groupedCollectionCapabilities() {
                return _.groupBy(Object.keys(this.collectionCapabilities), this.getCapabilityRelatedEntity);
            },
            groupedRepositoryCapabilities() {
                return _.groupBy(Object.keys(this.repositoryCapabilities), this.getCapabilityRelatedEntity);
            }
        },
        methods: {
            ...mapActions('collection', [
                'fetchAllCollectionNames'
            ]),
            ...mapActions('capability', [
                'updateRole',
                'fetchRole',
                'fetchCapabilities',
                'addCapabilityToRole',
                'removeCapabilityFromRole'
            ]),
            ...mapGetters('collection', [
                'getCollections'
            ]),
            ...mapGetters('capability', [
                'getRole',
                'getCapabilities'
            ]),
            onUpdateRoleName: _.debounce(function(newName) {
                const updatedRole = JSON.parse(JSON.stringify(this.role));
                updatedRole['name'] = newName;
                this.isUpdatingRole = true;
                this.updateRole(updatedRole)
                    .then(() => {
                        this.isUpdatingRole = false;
                    }).catch(() => {
                        this.isUpdatingRole = false;
                    });
            }, 800),
            onUpdateCapability(value, capabilityKey) {
                if (value)
                    this.addCapabilityToRole({ capabilityKey: capabilityKey, role: this.roleSlug })
                else
                    this.removeCapabilityFromRole({ capabilityKey: capabilityKey, role: this.roleSlug })
            },
            getCapabilityRelatedEntity(capabilitySlug) {
                if (capabilitySlug.match('collection'))
                    return this.$i18n.get('Collection')
                else if (capabilitySlug.match('metadata') || capabilitySlug.match('metadatum'))
                    return this.$i18n.get('Metadata')
                else if (capabilitySlug.match('filter'))
                    return this.$i18n.get('Filters')
                else if (capabilitySlug.match('log'))
                    return this.$i18n.get('Activities')
                else if (capabilitySlug.match('taxonomy') || capabilitySlug.match('taxonomies'))
                    return this.$i18n.get('Taxonomies')
                else if (capabilitySlug.match('item'))
                    return this.$i18n.get('Items')
                else if (capabilitySlug.match('%d'))
                    return this.$i18n.get('Collection')
                else
                    return this.$i18n.get('Repository')
            }
        },
        created() {
            this.roleSlug = this.$route.params.roleSlug;

            this.isLoadingRole = true;
            this.fetchRole(this.roleSlug)
                .then(() => {
                    this.isLoadingRole = false;
                }).catch(() => {
                    this.isLoadingRole = false;
                });

            this.isLoadingCapabilities = true;
            this.fetchCapabilities({ collectionId: undefined })
                .then(() => {
                    this.isLoadingCapabilities = false;
                }).catch(() => {
                    this.isLoadingCapabilities = false;
                });

            this.isLoadingCollections = true;
            this.fetchAllCollectionNames()
                .then((resp) => {
                    resp.request
                        .then((collections) => {
                            this.collections = collections;
                            this.isLoadingCollections = false;
                        }).catch(() => {
                            this.isLoadingCollections = false;
                        });
                })
                .catch(() => {
                    this.isLoadingCollections = false;
                });

        }
    }
</script>

<style lang="scss" scoped>
    .capabilities-list {
        padding: 1rem;
        break-inside: avoid;
        column-count: 5;

        .capability-group {
            break-inside: avoid;
            h3 {
                margin-top: 0;
                font-weight: normal;
            }
            ul {
                padding-bottom: 1rem;
                li {
                    margin-bottom: 1rem;
                }
            }
        }
        @media only screen and (max-width: 1600px) {
            column-count: 4;
        }
        @media only screen and (max-width: 1400px) {
            column-count: 3;
        }
        @media only screen and (max-width: 962px) {
            column-count: 2;
        }
        @media only screen and (max-width: 568px) {
            column-count: 1;
        }
    }
</style>