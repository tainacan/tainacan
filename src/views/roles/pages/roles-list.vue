<template>
    <div class="tainacan-fixed-subheader">
        <h1 class="tainacan-page-title">
            {{ $route.meta.title }}
        </h1>

        <!-- New Role Button -->
        <div 
                class="header-item"
                style="margin-right: auto; margin-left: 0;">
            <b-dropdown
                    id="roles-page-add-new"
                    aria-role="list"
                    trap-focus>
                <template #trigger>
                    <router-link
                            to="/roles/new"
                            custom>
                        <button 
                                id="button-create-role"
                                v-tooltip="{
                                    content: $i18n.get('Create a role based on: '),
                                    autoHide: true,
                                    placement: 'top',
                                    popperClass: ['tainacan-tooltip', 'tainacan-roles-tooltip']     
                                }"
                                type="button"
                                role="link" 
                                class="button is-secondary">
                            <span>
                                {{ $i18n.get('New role') }}
                            </span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                    </router-link>
                </template>
                <b-dropdown-item 
                        aria-role="listitem"
                        has-link>
                    <router-link
                            to="/roles/new"
                            style="min-height: unset;">
                        {{ $i18n.get('Blank') }}
                    </router-link>
                </b-dropdown-item>
                <b-dropdown-item separator />
                <b-dropdown-item 
                        class="dropdown-item-secstion-separator"
                        aria-role="listitem"
                        custom>
                    <em>{{ $i18n.get('Create a role based on: ') }}</em>
                </b-dropdown-item>
                <template 
                        v-for="role of roles"
                        :key="role.slug">
                    <b-dropdown-item 
                            v-if="role.slug.match('tainacan')"
                            aria-role="listitem"
                            has-link>
                        <router-link 
                                :to="'/roles/new?template=' + role.slug"
                                style="min-height: unset;">
                            {{ role.name }}
                        </router-link>
                    </b-dropdown-item>
                </template>
                
            </b-dropdown>
        </div>
    </div>

    <div class="sub-header">
        <b-field 
                id="roles-page-search"
                class="header-item">
            <b-input 
                    v-model="searchString"
                    :placeholder="$i18n.get('Type to search by Role Name')"
                    type="search"
                    size="is-small"
                    icon-right="magnify"
                    icon-right-clickable />
        </b-field>
    </div>

    <div class="above-subheader">
        <div class="table-container">
            <div class="table-wrapper">
                <table 
                        v-if="!isLoadingRoles"
                        class="tainacan-table is-narrow roles">
                    <thead>
                        <tr>
                            <th
                                    id="name"
                                    scope="col">
                                <div class="th-wrap">
                                    {{ $i18n.get('Role\'s Name') }}
                                </div>
                            </th>
                            <!-- <th
                                        scope="col"
                                        id="role">
                                    <div class="th-wrap">
                                        {{ $i18n.get('Slug') }}
                                    </div>
                            </th> -->
                            <th
                                    id="capabilities-number"
                                    scope="col"
                                    class="column-capabilities">
                                <div class="th-wrap">
                                    {{ $i18n.get('Number of Capabilities') }}
                                </div>
                            </th>
                            <!-- Actions -->
                            <th class="actions-header">
                                &nbsp;
                            <!-- nothing to show on header for actions cell-->
                            </th>
                        </tr>
                    </thead>

                    <tbody data-wp-lists="list:roles">
                        <tr
                                v-for="role of roles"
                                :id="role.slug"
                                :key="role.slug">
                            <!-- <th
                                    scope="row"
                                    class="check-column">
                                <label
                                        class="screen-reader-text"
                                        :for="'role_' + role.slug">
                                    {{ $i18n.get('Selecionar') + ' ' + role.name }}
                                </label>
                                <input
                                    type="checkbox"
                                    name="roles[]"
                                    :id="'role_'+ role.slug"
                                    :value="role.slug">
                            </th> -->
                            <td 
                                    class="column-default-width column-main-content"
                                    :data-colname="$i18n.get('Role name')">
                                <p>
                                    <router-link :to="'/roles/' + role.slug">
                                        {{ role.name }}
                                    </router-link>
                                </p>
                            </td>
                            <!-- <td
                                    class="slug column-slug"
                                    :data-colname="$i18n.get('Slug')">
                                {{ role.slug }}
                            </td> -->
                            <td
                                    class="column-small-width column-align-right"
                                    :data-colname="$i18n.get('Number of capabilities')">
                                <p>
                                    {{ Object.values(role.capabilities).filter((capability) => capability == true).length }}
                                </p>
                            </td>
                            <td class="column-default-width actions-cell">
                                <div class="actions-container">
                                    <router-link :to="'/roles/' + role.slug">
                                        <span
                                                v-tooltip="{
                                                    content: $i18n.get('Edit'),
                                                    autoHide: true,
                                                    popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'], 
                                                    placement: 'bottom'
                                                }"
                                                class="icon">
                                            <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit" />
                                        </span>
                                    </router-link>
                                    <a 
                                            v-if="role.slug.match('tainacan')"
                                            @click.prevent.stop="removeRole(role.slug)">
                                        <span
                                                v-tooltip="{
                                                    content: $i18n.get('Delete'),
                                                    autoHide: true,
                                                    popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                    placement: 'bottom'
                                                }"
                                                class="icon">
                                            <i class="tainacan-icon-delete has-text-secondary tainacan-icon tainacan-icon-1-25em" />
                                        </span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
    import CustomDialog from '../../admin/components/other/custom-dialog.vue';
     
    export default { 
        name: "RolesList",
        data() {
            return {
                isLoadingRoles: false,
                relatedEntities: [],
                currentRelatedEntity: '',
                showDropdownMenu: false,
                searchString: ''
            }
        },
        computed: {
            roles() {
                let roles = this.getRoles();

                if ( this.searchString && roles ) {
                    let searchedRoles = {}
                    for (let [roleKey, role] of Object.entries(roles)) {
                        if (role.name.toLowerCase().match(this.searchString))
                            searchedRoles[roleKey] = role;
                    }
                    roles = searchedRoles;
                }

                if ( this.relatedEntities.length && roles ) {
                    let filteredRoles = {};
                    for (let [roleKey, role] of Object.entries(roles)) {
                        for (let entity of this.relatedEntities) {
                            const existingIndex = Object.entries(role.capabilities).findIndex((capability) => capability[1] ? capability[0].match(entity) : false)
                            if (existingIndex >= 0) {
                                filteredRoles[roleKey] = role;
                                break;
                            }
                        }
                    }
                    roles = filteredRoles;
                }

                return roles ? roles : {};
            }
        },
        created() {
            this.isLoadingRoles = true;
            this.fetchRoles()
                .then(() => {
                    this.isLoadingRoles = false;
                }).catch(() => {
                    this.isLoadingRoles = false;
                });
        },
        methods: {
            ...mapActions('capability', [
                'fetchRoles',
                'deleteRole'
            ]),
            ...mapGetters('capability', [
                'getRoles'
            ]),
            filterByCapabilitiesRelatedTo(entityName) {
                this.currentRelatedEntity = entityName;
                switch(entityName) {
                    case 'repository':
                        this.relatedEntities = ['repository', 'manage-tainacan', 'manage-users', 'users'];
                        break;
                    case 'taxonomy':
                        this.relatedEntities = ['taxonomy', 'taxonomies'];
                        break;
                    case 'collection':
                        this.relatedEntities = ['collection', 'item'];
                        break;
                    case 'metadata':
                        this.relatedEntities = ['metadata', 'metadatum'];
                        break;
                    case 'filter':
                        this.relatedEntities = ['filter'];
                        break;
                    case 'activity':
                        this.relatedEntities = ['log'];
                        break;
                    default:
                        this.relatedEntities = [];
                }
            },
            removeRole(roleSlug) {
                this.$buefy.modal.open({
                    parent: this,
                    component: CustomDialog,
                    props: {
                        icon: 'alert',
                        confirmText: this.$i18n.get('Delete'),
                        title: this.$i18n.get('Warning'),
                        message: this.$i18n.get('Do you really want to permanently delete this user role?'),
                        onConfirm: () => {
                            this.deleteRole(roleSlug)
                                .then(() => {
                                    this.$forceUpdate();
                                })
                        }
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('Close')
                });
            }
        }
    }
</script>

<style lang="scss" scoped>

    .above-subheader {
        margin-bottom: 0;
        margin-top: 0;
        height: auto;
    }
    .sub-header {
        min-height: 2.5em;
        padding: 0.5em 0;
        height: auto;
        border-bottom: 1px solid var(--tainacan-gray2);
        display: inline-flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        width: 100%;
        gap: 4px;
    }
    #roles-page-search {
        max-width: 300px;

        .control {
            z-index: 0;
        }
    }
    #roles-page-add-new .dropdown-menu .has-link a,
    #roles-page-add-new .dropdown-menu .dropdown-item {
        box-sizing: border-box;
    }
    #roles-page-add-new .dropdown-menu .dropdown-item-secstion-separator,
    #roles-page-add-new .dropdown-menu .dropdown-item-secstion-separator:hover,
    #roles-page-add-new .dropdown-menu .dropdown-item-secstion-separator:focus {
        background-color: transparent;
    }

    .tainacan-table.roles {
        margin-bottom: 2rem;

        .column-small-width {
            width: 80px;
        }
        .column-main-content a {
            width: 100%;
            display: block;
        }
        a {
            color: var(--tainacan-label-color);
        }
    }
</style>
