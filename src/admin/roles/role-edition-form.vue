<template>
    <form @submit="onSubmit">
        <h1 class="wp-heading-inline">{{ $route.meta.title }}&nbsp;<strong>{{ role.name ? role.name : '' }}</strong></h1>
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
                    v-model="role.name" 
                    :placeholder="$i18n.get('Type here the role name...')">
            </div>
        </template>
        <template v-if="!isLoadingRole && !isLoadingCapabilities">
            <br>
            <div id="capabilities-tabs">
                <h2 class="nav-tab-wrapper">
                    <a 
                            class="nav-tab"
                            :class="{ 'nav-tab-active': capabilitiesTab == 'repository'}"
                            @click="capabilitiesTab = 'repository'">
                        {{ $i18n.get('Repository') }}
                    </a>
                    <a 
                            class="nav-tab"
                            :class="{ 'nav-tab-active': capabilitiesTab == 'collections'}"
                            @click="capabilitiesTab = 'collections'">
                        {{ $i18n.get('Collections') }}
                    </a>
                </h2>
                <div 
                        class="tabs-content"
                        v-if="capabilitiesTab === 'repository'"
                        id="tab-repository">
                    <h3>{{ $i18n.get('Role\'s Repository related Capabilities List') }}</h3>
                    <div class="capabilities-list">
                        <div
                                class="capability-group"
                                v-for="(group, groupIndex) of groupedRepositoryCapabilities"
                                :key="groupIndex">
                            <h4>{{ groupIndex }}</h4>
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
                                            name="capabilities[]"
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
                </div> <!-- End of Repository Tab -->

                <div 
                        class="tabs-content"
                        v-else-if="capabilitiesTab === 'collections'"
                        id="tab-collections">
                    <template v-if="!isLoadingCollections"> 
                        <h3>{{ $i18n.get('Role\'s Collection related Capabilities List') }}</h3>

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
                                <h4>{{ groupIndex }}</h4>
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
                </div> <!-- End of Collections Tab -->
            
            </div> <!-- End of Tabs-->

        </template>
        <div class="form-submit">
            <p class="cancel">
                <input 
                        type="button"
                        name="cancel"
                        @click="$router.go(-1)"
                        id="cancel" 
                        class="button"
                        :value="$i18n.get('Cancel')">
            </p>
            <p class="submit">
                <input 
                        type="submit"
                        name="submit"
                        id="submit" 
                        class="button button-primary"
                        :value="$i18n.get('Save Changes')">
            </p>
        </div>
    </form>
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
                isLoadingCollections: false,
                role: {
                    name: '',
                    capabilities: {}
                },
                capabilitiesTab: 'repository'
            }
        },
        computed: {
            originalRole() {
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
                'createRole',
                'updateRole',
                'fetchRole',
                'fetchCapabilities'
            ]),
            ...mapGetters('collection', [
                'getCollections'
            ]),
            ...mapGetters('capability', [
                'getRole',
                'getCapabilities'
            ]),
            onUpdateCapability(value, capabilityKey) {
                const capabilities = this.role.capabilities ? this.role.capabilities : {};
                this.$set(capabilities, capabilityKey, value);
                this.$set(this.role, 'capabilities', capabilities);
            },
            onSubmit(event) {
                event.preventDefault();
                this.isUpdatingRole = true;

                if (this.roleSlug === 'new') {
                    this.createRole(this.role)
                        .then((createdRole) => {
                            this.roleSlug = createdRole.slug;
                            this.isUpdatingRole = false;
                            this.$router.go(-1);
                        })
                        .catch(() => {
                            this.isUpdatingRole = false;
                        });
                } else {
                    this.updateRole(this.role)
                        .then(() => {
                            this.isUpdatingRole = false;
                            this.$router.go(-1);
                        })
                        .catch(() => {
                            this.isUpdatingRole = false;
                        });
                }
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

            if (this.roleSlug !== 'new') {
                this.isLoadingRole = true;
                this.fetchRole(this.roleSlug)
                    .then((originalRole) => {
                        this.role = JSON.parse(JSON.stringify(originalRole));
                        this.isLoadingRole = false;
                    }).catch(() => {
                        this.isLoadingRole = false;
                    });
            } else {
                this.role = {
                    name: '',
                    capabilities: {}
                }
            }

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
    .form-submit {
        display: flex;
        justify-content: space-between;
        align-content: center;
        margin: 1rem 0;
    }
    .tabs-content {
        border: 1px solid #ccc;
        border-top: none;
        padding: 1rem 2rem;
    }
    .capabilities-list {
        padding: 1rem;
        break-inside: avoid;
        column-count: 5;

        .capability-group {
            break-inside: avoid;
            h4 {
                margin-top: 0;
                margin-bottom: 1rem;
                font-size: 1rem;
                font-weight: normal;
                color: #0073aa;
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