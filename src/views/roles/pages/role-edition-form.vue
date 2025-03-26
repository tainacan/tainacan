<template>
    <form 
            class="tainacan-role-edition-form tainacan-form"
            @submit="onSubmit">
        <div class="tainacan-fixed-subheader">
            <h1 
                    v-if="roleSlug !== 'new'"
                    class="tainacan-page-title">
                {{ $route.meta.title }}&nbsp;<strong>{{ form.name ? form.name : '' }}</strong>
            </h1>
            <h1 
                    v-else
                    class="tainacan-page-title">
                {{ $i18n.get('Add new role') }}
            </h1>
            <transition name="appear-from-right">
                <div 
                        v-if="showNotice"
                        class="notice notice-success notice-alt">
                    <p>{{ $i18n.get('User Role Saved') }}</p>
                </div>
            </transition>
            <transition name="appear-from-right">
                <div 
                        v-if="showErrorNotice"
                        class="notice notice-error notice-alt">
                    <p>{{ errorMessage }}</p>
                </div>
            </transition>
        </div>

        <template v-if="!isLoadingRole">
            <b-field
                    class="name-edition-input"
                    :addons="false"
                    :label="$i18n.get('Role name')">
                <b-input
                        v-model="form.name" 
                        type="text"
                        name="name"
                        :placeholder="$i18n.get('Insert the role name...')" 
                        @update:model-value="showNotice = false" />
            </b-field>

            <!-- Hook for extra Form options -->
            <template v-if="hasBeginLeftForm">  
                <form
                        id="form-role-begin-left" 
                        class="form-hook-region"
                        @click="showNotice = false"
                        v-html="getBeginLeftForm" />
                <br>
            </template>
        </template>
        
        <span 
                v-if="isLoadingRole || isLoadingCapabilities"
                class="spinner is-active"
                style="float: none; margin: 0 auto; width: 100%; display: block;" />

        <template v-if="!isLoadingRole">

            <div id="capabilities-tabs">
                <div class="tabs">
                    <ul>
                        <li :class="{ 'is-active': capabilitiesTab == 'repository'}">
                            <a @click="capabilitiesTab = 'repository'">
                                {{ $i18n.get('Repository') }}
                            </a>
                        </li>
                        <li :class="{ 'is-active': capabilitiesTab == 'collections'}">
                            <a @click="capabilitiesTab = 'collections'">
                                {{ $i18n.get('Collections') }}
                            </a>
                        </li>
                        <li :class="{ 'is-active': capabilitiesTab == 'admin-ui'}">
                            <a @click="capabilitiesTab = 'admin-ui'">
                                {{ $i18n.get('Admin Appearence') }}
                            </a>
                        </li>
                        <li 
                                v-if="hasBeginRightForm || hasEndRightForm"
                                :class="{ 'is-active': capabilitiesTab == 'extra'}">
                            <a @click="capabilitiesTab = 'extra'">
                                {{ $i18n.get('Others') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div 
                        v-if="capabilitiesTab === 'repository'"
                        id="tab-repository"
                        class="tabs-content">
                    <!-- <h3>{{ $i18n.get('Role\'s Repository Related Capabilities List') }}</h3> -->
                    <div 
                            v-if="!isLoadingCapabilities"
                            class="capabilities-list">
                        <div
                                v-for="(group, groupIndex) of groupedRepositoryCapabilities"
                                :key="groupIndex"
                                class="capability-group">
                            <h3>{{ groupIndex }}</h3>
                            <ul>
                                <template 
                                        v-for="(capability, index) of group"
                                        :key="index">
                                    <li :id="'capability-' + capability">
                                        <label>
                                            <input
                                                    :id="'capability_'+ capability"
                                                    type="checkbox"
                                                    name="capabilities[]"
                                                    :disabled="repositoryCapabilities[capability].supercaps.length > 0 && repositoryCapabilities[capability].supercaps.findIndex((supercap) => form.capabilities[supercap] == true) >= 0"
                                                    :checked="form.capabilities[capability] || (repositoryCapabilities[capability].supercaps.length > 0 && repositoryCapabilities[capability].supercaps.findIndex((supercap) => form.capabilities[supercap] == true) >= 0)"
                                                    @input="onUpdateCapability($event.target.checked, capability)">
                                            <span 
                                                    v-tooltip="{
                                                        content: repositoryCapabilities[capability].description,
                                                        autoHide: true,
                                                        delay: { show: 500, hide: 0 },
                                                        placement: 'auto-end',
                                                        instantMove: true,
                                                        popperClass: ['tainacan-tooltip', 'tainacan-roles-tooltip']     
                                                    }"        
                                                    class="name column-name"
                                                    :data-colname="$i18n.get('Capability name')">
                                                {{ repositoryCapabilities[capability].display_name }}
                                            </span>
                                        </label>
                                    </li>
                                    <br>
                                </template>
                            </ul>
                        </div>
                    </div>
                    <p><span class="dashicons dashicons-info" />&nbsp; {{ $i18n.get('The capability "Manage Tainacan" may affect other capabilities related to repository and collections.') }}</p>
                </div> <!-- End of Repository Tab -->

                <div 
                        v-else-if="capabilitiesTab === 'collections'"
                        id="tab-collections"
                        class="tabs-content">
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
                                        class="sr-only">
                                    {{ $i18n.get('Select the collection to change capabilities') }}
                                </label>
                                <span class="select">
                                    <select 
                                            id="collection-select" 
                                            name="collection"
                                            :value="selectedCollection"
                                            @input="selectedCollection = $event.target.value">
                                        <option value="all">
                                            {{ $i18n.get('All Collections') }}
                                        </option>
                                        <option 
                                                v-for="(collection, index) of collections"
                                                :key="index"
                                                :value="collection.id">
                                            {{ collection.name }}
                                        </option>
                                    </select>
                                </span>
                            </div>
                            <br class="clear">
                        </div>

                        <div 
                                v-if="!isLoadingCapabilities"
                                class="capabilities-list">
                            <div
                                    v-for="(group, groupIndex) of groupedCollectionCapabilities"
                                    :key="groupIndex"
                                    class="capability-group">
                                <h3>{{ groupIndex }}</h3>
                                <ul>
                                    <template 
                                            v-for="(capability, index) of group"
                                            :key="index">
                                        <li :id="'capability-' + capability.replace('%d', selectedCollection)">
                                            <label>
                                                <input
                                                        :id="'capability_' + capability.replace('%d', selectedCollection)"
                                                        type="checkbox"
                                                        name="roles[]"
                                                        :style="{ 'margin-left': collectionCapabilities[capability].deps && collectionCapabilities[capability].deps.size > 0 ? ( collectionCapabilities[capability].deps.size + 'em') : '0' }"
                                                        :disabled="isCapabilityDisabled(capability, selectedCollection)"
                                                        :checked="isCapabilityChecked(capability, selectedCollection)"
                                                        @input="onUpdateCapability($event.target.checked, capability.replace('%d', selectedCollection))">
                                                <span 
                                                        v-tooltip="{
                                                            content: collectionCapabilities[capability].description,
                                                            autoHide: true,
                                                            delay: { show: 500, hide: 0 },
                                                            placement: 'auto-end',
                                                            instantMove: true,
                                                            popperClass: ['tainacan-tooltip', 'tainacan-roles-tooltip']     
                                                        }"
                                                        class="name column-name"
                                                        :data-colname="$i18n.get('Capability name')">
                                                    {{ collectionCapabilities[capability].display_name }}
                                                </span>
                                            </label>
                                        </li>
                                        <br>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </template>
                    <p><span class="dashicons dashicons-info" />&nbsp; {{ $i18n.get('The capability "Manage Tainacan" may affect other capabilities related to repository and collections.') }}</p>
                    <p><span class="dashicons dashicons-info" />&nbsp; {{ $i18n.get('Capabilities related to All Collections shall affect other Collections capabilities.') }}</p>
                </div> <!-- End of Collections Tab -->

                <div
                        v-else-if="capabilitiesTab === 'admin-ui'"
                        id="tab-admin-ui"
                        class="tabs-content">
                    <p>{{ $i18n.get('The following capabilities are related to the admin interface appearence.') }}</p>

                    <p v-if="roleSlug === 'new'">
                        <span class="dashicons dashicons-info" />&nbsp; {{ $i18n.get('You must first create the slug before defining apperaence options for it.') }}
                    </p>
                   
                    <div class="capabilities-list">
                        <div
                                v-for="(group, groupIndex) of groupedAdminUIOptions"
                                :key="groupIndex"
                                class="capability-group">
                            <h3>{{ groupIndex }}</h3>
                            <ul>
                                <template
                                        v-for="(optionLabel, optionSlug) of group"
                                        :key="optionSlug">
                                    <li>
                                        <label>
                                            <input 
                                                    :id="optionSlug"
                                                    type="checkbox"
                                                    name="tainacan_admin_options_by_role"
                                                    :disabled="roleSlug === 'new'"
                                                    :checked="getAdminUIOptionValue(optionSlug)"
                                                    @input="($event) => setAdminUIOptionValue($event, optionSlug)">
                                            <span class="name column-name">{{ optionLabel }}</span>
                                        </label>
                                    </li>
                                    <br>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div><!-- End of PluginUI tab -->

                <div
                        v-show="capabilitiesTab === 'extra'"
                        id="tab-extra"
                        class="tabs-content">
                    <br>
                    
                    <!-- Hook for extra Form options -->
                    <template v-if="hasBeginRightForm">  
                        
                        <form 
                                id="form-role-begin-right"
                                class="form-hook-region"
                                @click="showNotice = false"
                                v-html="getBeginRightForm" />
                    </template>
                    
                    <hr v-if="hasBeginRightForm && hasEndRightForm">
                    
                    <!-- Hook for extra Form options -->
                    <template v-if="hasEndRightForm"> 
                        <form 
                                id="form-role-end-right"
                                class="form-hook-region"
                                @click="showNotice = false"
                                v-html="getEndRightForm" />
                    </template>
                    
                </div><!-- End of the Extra Tab -->

            </div> <!-- End of Tabs-->

        </template>

        <!-- Hook for extra Form options -->
        <template v-if="hasEndLeftForm && !isLoadingRole">  
            <br>
            <form 
                    id="form-role-end-left"
                    class="form-hook-region"
                    @click="showNotice = false"
                    v-html="getEndLeftForm" />
        </template>

        <div class="form-submit">
            <div class="control">
                <input 
                        id="cancel"
                        type="button"
                        name="cancel"
                        class="button is-outlined" 
                        :value="$i18n.get('Cancel')"
                        @click="$router.go(-1)">
            </div>
            <div class="control">
                <span 
                        v-if="isUpdatingRole"
                        class="spinner is-active"
                        style="float: none;" />
                <input 
                        id="submit"
                        type="submit"
                        name="submit"
                        :disabled="!form.name || showNotice" 
                        class="is-success button"
                        :value="roleSlug === 'new' ? $i18n.get('Create Role') : $i18n.get('Save Changes')">
            </div>
        </div>
    </form>
</template>

<script>
    import { nextTick } from 'vue';
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
                errorMessage: '',
                groupedAdminUIOptions: tainacan_plugin && tainacan_plugin.admin_ui_options ? tainacan_plugin.admin_ui_options : {},
                isLoadingAdminUIOptions: false,
                localAdminUIOptions: {},
            }
        },
        computed: {
            ...mapGetters('capability', {
                'originalRole': 'getRole',
                'capabilities': 'getCapabilities',
                'adminUIOptions': 'getAdminUIOptions'
            }),
            collectionCapabilities() {
                let collectionCapabilities = {}

                for (let [capabilityKey, capability] of Object.entries(this.capabilities)) {
                    if (capability.scope === 'collection') {
                        collectionCapabilities[capabilityKey] = capability;
                        
                        if ( ( capabilityKey.includes('edit') && capabilityKey.includes('items') && capabilityKey !== 'tnc_col_%d_edit_items' ) || capabilityKey === 'tnc_col_%d_publish_items' ) {
                            
                            if ( !collectionCapabilities[capabilityKey].deps ) 
                                collectionCapabilities[capabilityKey].deps = new Set();
                            
                            collectionCapabilities[capabilityKey].deps.add('tnc_col_%d_edit_items');

                            // if ( capabilityKey.includes('published_items') || capabilityKey.includes('private_items') ) 
                            //     collectionCapabilities[capabilityKey].deps.add('tnc_col_%d_edit_others_items');
                        }

                        if ( capabilityKey.includes('delete') && capabilityKey.includes('items') && capabilityKey !== 'tnc_col_%d_delete_items') {
                            
                            if ( !collectionCapabilities[capabilityKey].deps ) 
                                collectionCapabilities[capabilityKey].deps = new Set();
                            
                            collectionCapabilities[capabilityKey].deps.add('tnc_col_%d_delete_items');

                            // if ( capabilityKey.includes('published_items') || capabilityKey.includes('private_items') ) 
                            //     collectionCapabilities[capabilityKey].deps.add('tnc_col_%d_delete_others_items');
                        }
                    }
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
                        nextTick(() => this.updateExtraFormData(this.form) );
                        
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
                        nextTick(() => this.updateExtraFormData(this.form) );

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

            this.isLoadingAdminUIOptions = true;
            this.fetchAdminUIOptions()
                .then(() => {
                    if ( Array.isArray(this.adminUIOptions) && this.adminUIOptions.length === 0 )
                        this.localAdminUIOptions = {};
                    else
                        this.localAdminUIOptions = JSON.parse(JSON.stringify(this.adminUIOptions));
                    
                    this.isLoadingAdminUIOptions = false;
                }).catch(() => {
                    this.isLoadingAdminUIOptions = false;
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
                'fetchCapabilities',
                'fetchAdminUIOptions',
                'updateAdminUIOptions'
            ]),
            onUpdateCapability(value, capabilityKey) {
                this.showNotice = false;
                const capabilities = this.form.capabilities && Object.keys(this.form.capabilities).length ? this.form.capabilities : {};
                Object.assign(capabilities, { [capabilityKey]: value });
                Object.assign(this.form, { 'capabilities': capabilities });
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

                if ( this.roleSlug === 'new' ) {
                    this.createRole(data)
                        .then((createdRole) => {
                            this.roleSlug = createdRole.slug;
                            this.form = createdRole;
                            this.$router.push('/roles/' + this.roleSlug);
                            this.isUpdatingRole = false;
                           
                            this.isLoadingAdminUIOptions = true;
                            this.updateAdminUIOptions(this.localAdminUIOptions)
                                .then(() => {
                                    this.isLoadingAdminUIOptions = false;
                                    
                                    this.showNotice = true;
                                    this.showErrorNotice = false;
                                    this.errorMessage = '';
                                }).catch((error) => {
                                    this.isLoadingAdminUIOptions = false;

                                    this.errorMessage = error.error_message;
                                    this.showErrorNotice = true;
                                });
                            
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
                           
                            this.isLoadingAdminUIOptions = true;
                            
                            this.updateAdminUIOptions(JSON.parse(JSON.stringify(this.localAdminUIOptions)))
                                .then(() => {
                                    this.isLoadingAdminUIOptions = false;

                                    this.showNotice = true;
                                    this.showErrorNotice = false;
                                    this.errorMessage = '';
                                }).catch((error) => {
                                    this.isLoadingAdminUIOptions = false;
                                    this.errorMessage = error.error_message;
                                    this.showErrorNotice = true;
                                });
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
            },
            getAdminUIOptionValue(optionSlug) {
                return this.localAdminUIOptions[this.roleSlug] && this.localAdminUIOptions[this.roleSlug][optionSlug] ? this.localAdminUIOptions[this.roleSlug][optionSlug] : false;
            },
            setAdminUIOptionValue($event, optionSlug) {

                this.showNotice = false;
                    
                // Ensure the localAdminUIOptions object is initialized for the current roleSlug
                if (!this.localAdminUIOptions[this.roleSlug]) {
                    this.localAdminUIOptions[this.roleSlug] = {};
                }

                // Update the specific optionSlug value
                this.localAdminUIOptions[this.roleSlug][optionSlug] = $event.target.checked;
            },
            isCapabilityChecked(capability, selectedCollection) {
                const isCapabilityEnabled = this.form.capabilities[capability.replace('%d', selectedCollection)] ;

                if ( isCapabilityEnabled )
                    return true;

                const supercaps = this.collectionCapabilities[capability].supercaps;

                const hasActiveSupercaps = supercaps.length > 0 && 
                    supercaps.findIndex((supercap) => 
                        this.form.capabilities[supercap.replace('%d', selectedCollection)] == true
                    ) >= 0;
                
                if ( hasActiveSupercaps )
                    return true;

                return false;
            },
            isCapabilityDisabled(capability, selectedCollection) {
                
                // Check for unmet dependencies.
                const deps = this.collectionCapabilities[capability].deps;
                if ( deps && deps.size && Array.from(deps).some((requiredCap) => 
                    !this.form.capabilities[requiredCap.replace('%d', selectedCollection)] &&
                    !this.form.capabilities[requiredCap.replace('%d', 'all')] // In order to disable completely, we must also make sure that the all collections version is not enabled as well.
                ) ) {
                    return true;
                }

                // Check for active supercaps
                const supercaps = this.collectionCapabilities[capability].supercaps;
                if ( supercaps.length > 0 && supercaps.some((supercap) => 
                    this.form.capabilities[supercap.replace('%d', selectedCollection)] && 
                    supercap.replace('%d', selectedCollection) !== capability.replace('%d', selectedCollection)
                ) ) {
                    return true;
                }

                // Check if any dependent capability is checked
                const isCapabilityEnabled = this.form.capabilities[capability.replace('%d', selectedCollection)];
                if ( isCapabilityEnabled ) {
                    for ( let formCapability in this.form.capabilities ) {
                        if ( this.form.capabilities[formCapability] ) {
                            const otherCollectionCapability = formCapability.replace(selectedCollection, '%d');
                            if (
                                this.collectionCapabilities[otherCollectionCapability] &&
                                this.collectionCapabilities[otherCollectionCapability].deps &&
                                this.collectionCapabilities[otherCollectionCapability].deps.has(capability)
                            ) {
                                return true;
                            }
                        }
                    }
                }

                return false;
            }
        }
    }
</script>

<style lang="scss">

.tainacan-role-edition-form {
    display: flex;
    flex-direction: column;
    height: 100%;
    gap: var(--tainacan-container-padding);

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
    .name-edition-input {
        max-width: 280px;
    }
    .form-submit {
        display: flex;
        justify-content: space-between;
        gap: var(--tainacan-container-padding);
        position: sticky;
        bottom: 0;
        right: 0;
        left: 0;
        z-index: 1001;
        background-color: var(--tainacan-gray1);
        width: calc( 100% + ( 2 * var(--tainacan-page-container-padding-x, var(--tainacan-one-column)) ) );
        align-items: center;
        transition: bottom 0.5s ease, width 0.2s linear;
        box-shadow: 0px 0px 12px -8px var(--tainacan-black);
        margin-left: calc( -1 * var(--tainacan-page-container-padding-x, var(--tainacan-one-column)));
        margin-top: auto;
        padding: var(--tainacan-page-container-padding-y, 1rem) var(--tainacan-page-container-padding-x, var(--tainacan-one-column));

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
        padding: 5px 24px;
        margin: 0;
        background-color: transparent;
        border-top: none;
        border-left: none;
        border-right: none;
        border-bottom: 3px solid transparent;

        &.nav-tab-active {
            border-bottom: 3px solid var(--tainacan-secondary);
        }
    }
    .tabs-content {
        border-top: none;
        padding: 0.25em 2em;
    }
    .dashicons-info {
        color: var(--tainacan-warning);
    }
    .capabilities-list {
        padding: 1em 0;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        font-size: 13px;
        width: 100%;

        .capability-group {
            padding: 1em 1em 1em 0em;
            margin-right: auto;
            break-inside: avoid;
            
            h3 {
                margin-top: 0;
                margin-bottom: 1em;
                font-size: 1em;
                font-weight: bold;
                color: var(--taincan-label-color);
            }
            ul {
                li {
                    margin: 0 0.5em 0.5em;
                    display: inline-block;

                    label {
                        display: flex;
                        align-items: center;
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
            border-bottom: 1px solid var(--tainacan-gray2);
        }
    }
}
</style>