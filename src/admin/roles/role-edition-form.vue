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
                

                <ul>
                    <li
                            v-for="(capability, index) of repositoryCapabilities"
                            :key="index"
                            :id="'capability-' + index">
                        <span
                                scope="row"
                                class="check-column">
                            <label
                                    class="screen-reader-text"
                                    :for="'capability_' + index">
                                {{ $i18n.get('Selecionar') + ' ' + capability.display_name }}
                            </label>
                            <input
                                type="checkbox"
                                name="roles[]"
                                :id="'capability_'+ index"
                                :disabled="capability.supercaps.length > 0 && capability.supercaps.findIndex((supercap) => role.capabilities[supercap] == true) >= 0"
                                :checked="role.capabilities[index] || (capability.supercaps.length > 0 && capability.supercaps.findIndex((supercap) => role.capabilities[supercap] == true) >= 0)"
                                @input="onUpdateCapability($event.target.checked, index)">
                        </span>
                        <span 
                                class="name column-name"
                                :data-colname="$i18n.get('Capability name')">
                            <strong>{{ capability.display_name }}</strong>
                            <help-button 
                                    :title="capability.display_name"
                                    :message="capability.description"/>
                        </span>
                    </li>
                </ul>
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
                    <ul>
                        <li
                                v-for="(capability, index) of collectionCapabilities"
                                :key="index"
                                :id="'capability-' + index.replace('%d', selectedCollection)">
                            <span
                                    scope="row"
                                    class="check-column">
                                <label
                                        class="screen-reader-text"
                                        :for="'capability_' + index.replace('%d', selectedCollection)">
                                    {{ $i18n.get('Selecionar') + ' ' + capability.display_name }}
                                </label>
                                <input
                                    type="checkbox"
                                    name="roles[]"
                                    :id="'capability_'+ index.replace('%d', selectedCollection)"
                                    :disabled="capability.supercaps.length > 0 && capability.supercaps.findIndex((supercap) => role.capabilities[supercap] == true) >= 0"
                                    :checked="role.capabilities[index.replace('%d', selectedCollection)] || (capability.supercaps.length > 0 && capability.supercaps.findIndex((supercap) => role.capabilities[supercap] == true) >= 0)"
                                    @input="onUpdateCapability($event.target.checked, index.replace('%d', selectedCollection))">
                            </span>
                            <span 
                                    class="name column-name"
                                    :data-colname="$i18n.get('Capability name')">
                                <strong>{{ capability.display_name }}</strong>
                                <help-button 
                                    :title="capability.display_name"
                                    :message="capability.description"/>
                            </span>
                        </li>
                    </ul>
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
            collectionCapabilities() {
                const capabilities = this.getCapabilities();
                let collectionCapabilities = {}

                for (let [capabilityKey, capability] of Object.entries(capabilities)) {
                    if (capability.scope === 'collection')
                        collectionCapabilities[capabilityKey] = capability;
                }
                return collectionCapabilities;
            },
            repositoryCapabilities() {
                const capabilities = this.getCapabilities();
                let repositoryCapabilities = {}

                for (let [capabilityKey, capability] of Object.entries(capabilities)) {
                    if (capability.scope === 'repository')
                        repositoryCapabilities[capabilityKey] = capability;
                }
                return repositoryCapabilities;
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
        margin: 1rem;
        ul {
            column-count: 4;
            li {
                margin-bottom: 1rem;
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
    }
</style>