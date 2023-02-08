<template>
    <form 
            class="tainacan-role-edition-form"
            @submit="onSubmit">
        <h1 
                v-if="roleSlug !== 'new'"
                class="wp-heading-inline">
            {{ $route.meta.title }}&nbsp;<strong>{{ form.name ? form.name : '' }}</strong>
        </h1>
        <h1 
                v-else
                class="wp-heading-inline">
            {{ $i18n.get('Add new role') }}
        </h1>
         <transition name="appear-from-right">
            <div 
                    v-if="showNotice"
                    class="notice notice-success notice-alt">
                <p>{{ $i18n.get('User Role Saved') }}</p>
            </div>
            <div 
                    v-if="showErrorNotice"
                    class="notice notice-error notice-alt">
                <p>{{ errorMessage }}</p>
            </div>
        </transition>
        <hr class="wp-header-end">
        <br>
        <template v-if="!isLoadingRole">
            <div class="name-edition-box">
                <label for="role-name-input">{{ $i18n.get('Role name') + ':' }}</label>
                <input
                    type="text" 
                    id="role-name-input" 
                    name="name"
                    @input="showNotice = false" 
                    v-model="form.name" 
                    :placeholder="$i18n.get('Insert the role name...')">
            </div>
            <br>
            <!-- Hook for extra Form options -->
            <template v-if="hasBeginLeftForm">  
                <form
                    @click="showNotice = false" 
                    id="form-role-begin-left"
                    class="form-hook-region"
                    v-html="getBeginLeftForm"/>
                <br>
            </template>
        </template>

        <span 
                v-if="isLoadingRole || isLoadingCapabilities"
                class="spinner is-active"
                style="float: none; margin: 0 auto; width: 100%; display: block;" />

        <template v-if="!isLoadingRole">

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

                    <a        
                            v-if="hasBeginRightForm || hasEndRightForm"
                            class="nav-tab"
                            :class="{ 'nav-tab-active': capabilitiesTab == 'extra'}"
                            @click="capabilitiesTab = 'extra'">
                        {{ $i18n.get('Others') }}
                    </a>
                </h2>
                <div 
                        class="tabs-content"
                        v-if="capabilitiesTab === 'repository'"
                        id="tab-repository">
                    <!-- <h3>{{ $i18n.get('Role\'s Repository Related Capabilities List') }}</h3> -->
                    <div 
                            v-if="!isLoadingCapabilities"
                            class="capabilities-list">
                        <div
                                class="capability-group"
                                v-for="(group, groupIndex) of groupedRepositoryCapabilities"
                                :key="groupIndex">
                            <h3>{{ groupIndex }}</h3>
                            <ul>
                                <template v-for="(capability, index) of group">
                                    <li
                                            v-tooltip="{
                                                content: repositoryCapabilities[capability].description,
                                                autoHide: true,
                                                delay: 0,
                                                placement: 'bottom',
                                                popperClass: ['tainacan-tooltip', 'tainacan-roles-tooltip']     
                                            }"
                                            :key="index"
                                            :id="'capability-' + capability">
                                        <span class="check-column">
                                            <label
                                                    class="screen-reader-text"
                                                    :for="'capability_' + capability">
                                                {{ $i18n.get('Selecionar') + ' ' + repositoryCapabilities[capability].display_name }}
                                            </label>
                                            <input
                                                type="checkbox"
                                                name="capabilities[]"
                                                :id="'capability_'+ capability"
                                                :disabled="repositoryCapabilities[capability].supercaps.length > 0 && repositoryCapabilities[capability].supercaps.findIndex((supercap) => form.capabilities[supercap] == true) >= 0"
                                                :checked="form.capabilities[capability] || (repositoryCapabilities[capability].supercaps.length > 0 && repositoryCapabilities[capability].supercaps.findIndex((supercap) => form.capabilities[supercap] == true) >= 0)"
                                                @input="onUpdateCapability($event.target.checked, capability)">
                                        </span>
                                        <span 
                                                class="name column-name"
                                                :data-colname="$i18n.get('Capability name')">
                                            {{ repositoryCapabilities[capability].display_name }}
                                        </span>
                                    </li>
                                    <br :key="index">
                                </template>
                            </ul>
                        </div>
                    </div>
                    <p><span class="dashicons dashicons-info" />&nbsp; {{ $i18n.get('The capability "Manage Tainacan" may affect other capabilities related to repository and collections.') }}</p>
                </div> <!-- End of Repository Tab -->

                <div 
                        class="tabs-content"
                        v-else-if="capabilitiesTab === 'collections'"
                        id="tab-collections">
                    <span 
                            v-if="isLoadingCollections"
                            class="spinner is-active"
                            style="float: none; margin: 0 auto;" />
                    <template v-if="!isLoadingCollections"> 
                        <!-- <h3>{{ $i18n.get('Role\'s Collection Related Capabilities List') }}</h3> -->
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

                        <div 
                                v-if="!isLoadingCapabilities"
                                class="capabilities-list">
                            <div
                                    class="capability-group"
                                    v-for="(group, groupIndex) of groupedCollectionCapabilities"
                                    :key="groupIndex">
                                <h3>{{ groupIndex }}</h3>
                                <ul>
                                    <template v-for="(capability, index) of group">
                                        <li
                                                v-tooltip="{
                                                    content: collectionCapabilities[capability].description,
                                                    autoHide: true,
                                                    delay: 0,
                                                    placement: 'bottom',
                                                    popperClass: ['tainacan-tooltip', 'tainacan-roles-tooltip']     
                                                }"
                                                :key="index"
                                                :id="'capability-' + capability.replace('%d', selectedCollection)">
                                            <span class="check-column">
                                                <label
                                                        class="screen-reader-text"
                                                        :for="'capability_' + capability.replace('%d', selectedCollection)">
                                                    {{ $i18n.get('Selecionar') + ' ' + collectionCapabilities[capability].display_name }}
                                                </label>
                                                <input
                                                    type="checkbox"
                                                    name="roles[]"
                                                    :id="'capability_'+ capability.replace('%d', selectedCollection)"
                                                    :disabled="collectionCapabilities[capability].supercaps.length > 0 && collectionCapabilities[capability].supercaps.filter((supercap) => supercap.replace('%d', selectedCollection) != capability.replace('%d', selectedCollection)).findIndex((supercap) => form.capabilities[supercap.replace('%d', selectedCollection)] == true) >= 0"
                                                    :checked="form.capabilities[capability.replace('%d', selectedCollection)] || (collectionCapabilities[capability].supercaps.length > 0 && collectionCapabilities[capability].supercaps.findIndex((supercap) => form.capabilities[supercap.replace('%d', selectedCollection)] == true) >= 0)"
                                                    @input="onUpdateCapability($event.target.checked, capability.replace('%d', selectedCollection))">
                                            </span>
                                            <span 
                                                    class="name column-name"
                                                    :data-colname="$i18n.get('Capability name')">
                                                {{ collectionCapabilities[capability].display_name }}
                                            </span>
                                        </li>
                                        <br :key="index">
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </template>
                    <p><span class="dashicons dashicons-info" />&nbsp; {{ $i18n.get('The capability "Manage Tainacan" may affect other capabilities related to repository and collections.') }}</p>
                    <p><span class="dashicons dashicons-info" />&nbsp; {{ $i18n.get('Capabilities related to All Collections shall affect other Collections capabilities.') }}</p>
                </div> <!-- End of Collections Tab -->

                <div
                        class="tabs-content"
                        v-show="capabilitiesTab === 'extra'"
                        id="tab-extra">
                    <br>
                    
                    <!-- Hook for extra Form options -->
                    <template v-if="hasBeginRightForm">  
                        
                        <form 
                            @click="showNotice = false"
                            id="form-role-begin-right"
                            class="form-hook-region"
                            v-html="getBeginRightForm"/>
                    </template>
                    
                    <hr v-if="hasBeginRightForm && hasEndRightForm">
                    
                    <!-- Hook for extra Form options -->
                    <template v-if="hasEndRightForm"> 
                        <form 
                            @click="showNotice = false"
                            id="form-role-end-right"
                            class="form-hook-region"
                            v-html="getEndRightForm"/>
                    </template>
                    
                    <br>
                </div><!-- End of the Extra Tab -->

            </div> <!-- End of Tabs-->

        </template>

        <!-- Hook for extra Form options -->
        <template v-if="hasEndLeftForm && !isLoadingRole">  
            <br>
            <form 
                @click="showNotice = false"
                id="form-role-end-left"
                class="form-hook-region"
                v-html="getEndLeftForm"/>
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
                <span 
                        v-if="isUpdatingRole"
                        class="spinner is-active"
                        style="float: none;" />
                <input 
                        type="submit"
                        name="submit"
                        id="submit"
                        :disabled="!form.name || showNotice" 
                        class="button button-primary"
                        :value="roleSlug === 'new' ? $i18n.get('Create Role') : $i18n.get('Save Changes')">
            </p>
        </div>
    </form>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import { formHooks } from '../../admin/js/mixins';

    export default {
        mixins: [ formHooks ],
        data() {
            return {
                entityName: 'role',
                isUpdatingRole: false,
                isLoadingRole: false,
                isLoadingCapabilities: false,
                selectedCollection: 'all',
                collections: [],
                isLoadingCollections: false,
                form: {
                    name: '',
                    capabilities: {}
                },
                capabilitiesTab: 'repository',
                showNotice: false,
                showErrorNotice: false,
                errorMessage: ''
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
        created() {
            this.roleSlug = this.$route.params.roleSlug;
        },
        mounted() {
            if (this.roleSlug !== 'new') {
                this.isLoadingRole = true;
                this.fetchRole(this.roleSlug)
                    .then((originalRole) => {
                        this.form = JSON.parse(JSON.stringify(originalRole));

                        this.isLoadingRole = false;

                        // Fills hook forms with it's real values 
                        this.$nextTick(() => this.updateExtraFormData(this.form) );
                        
                    }).catch(() => {
                        this.isLoadingRole = false;
                    });
            } else if (this.roleSlug === 'new' && this.$route.query.template) {
                this.isLoadingRole = true;
                this.fetchRole(this.$route.query.template)
                    .then((originalRole) => {
                        this.form = JSON.parse(JSON.stringify(originalRole));
                        this.form.name = this.form.name + ' ' + this.$i18n.get('(Copy)');
                        this.form.slug = undefined;

                        this.isLoadingRole = false;

                        // Fills hook forms with it's real values 
                        this.$nextTick(() => this.updateExtraFormData(this.form) );

                    }).catch(() => {
                        this.isLoadingRole = false;
                    });
            } else {
                this.form = {
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
                this.showNotice = false;
                const capabilities = this.form.capabilities && Object.keys(this.form.capabilities).length ? this.form.capabilities : {};
                this.$set(capabilities, capabilityKey, value);
                this.$set(this.form, 'capabilities', capabilities);
            },
            onSubmit(event) {
                event.preventDefault();
                this.isUpdatingRole = true;
                
                let data = {
                    name: this.form.name,
                    capabilities: this.form.capabilities,
                    slug: this.form.slug
                }
                this.fillExtraFormData(data);

                if (this.roleSlug === 'new') {
                    this.createRole(data)
                        .then((createdRole) => {
                            this.roleSlug = createdRole.slug;
                            this.form = createdRole;
                            this.$router.push('/roles/' + this.roleSlug);
                            this.isUpdatingRole = false;
                            this.showNotice = true;
                            this.showErrorNotice = false;
                            this.errorMessage = '';
                        })
                        .catch((error) => {
                            this.isUpdatingRole = false;
                            this.errorMessage = error.error_message;
                            this.showErrorNotice = true;
                        });
                } else {
                    this.updateRole(data)
                        .then(() => {
                            this.isUpdatingRole = false;
                            this.showNotice = true;
                            this.showErrorNotice = false;
                            this.errorMessage = '';
                        })
                        .catch((error) => {
                            this.isUpdatingRole = false;
                            this.errorMessage = error.error_message;
                            this.showErrorNotice = true;
                        });
                }
            },
            getCapabilityRelatedEntity(capabilitySlug) {
                if (capabilitySlug.match('collection'))
                    return this.$i18n.get('Collection')
                else if (capabilitySlug.match('metadata') || capabilitySlug.match('metadatum'))
                    return this.$i18n.get('Metadata')
                else if (capabilitySlug.match('metasection'))
                    return this.$i18n.get('Metadata Sections')
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
        }
    }
</script>

<style lang="scss">

.tainacan-role-edition-form {

    @keyframes appear-from-right {
        from {
            right: -100%;
            opacity: 0.5;
        }
        to {
            right: 0;
            opacity: 1;
        }
    }
    .appear-from-right-enter-active {
        animation: appear-from-right 0.8s;
    }
    .appear-from-right-leave-active {
        animation: appear-from-right 0.8s reverse;
    }
    .notice {
        position: relative;
        float: right;
    }
    #role-name-input {
        min-width: 200px;
    }
    .form-submit {
        display: flex;
        justify-content: space-between;
        align-content: center;
        margin: 2em 0 1em 0;

        p {
            margin: 0;
            padding: 0;
        }
        .button {
            padding: 2px 16px;
        }
    }
    .name-edition-box label {
        margin-right: 2em;
        padding-bottom: 3px;
        font-size: 1em;
        font-weight: bold;
    }
    .nav-tab {
        background-color: #faf9f9;
        border-bottom-color: #faf9f9;
        padding: 5px 24px;
    }
    .tabs-content {
        background-color: #faf9f9;
        border: 1px solid #ccc;
        border-top: none;
        padding: 1em 2em;
    }
    .dashicons-info {
        color: #bb7700;
    }
    .capabilities-list {
        padding: 1em 0;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;

        .capability-group {
            padding: 1em 1em 1em 0em;
            margin-right: auto;
            break-inside: avoid;
            
            h3 {
                margin-top: 0;
                margin-bottom: 1em;
                font-size: 1em;
                font-weight: bold;
                color: #0073aa;
            }
            ul {
                li {
                    margin: 0 0.5em 0.5em;
                    display: inline-block;
                    white-space: nowrap;
                    .column-name {
                        white-space: nowrap;
                    }
                }
            }
        }
    }
    @media only screen and (max-width: 783px) {
        #collection-select {
            width: 100%;
        }
        .nav-tab-wrapper {
            border-bottom: 1px solid #ccc;
        }
    }
}
</style>