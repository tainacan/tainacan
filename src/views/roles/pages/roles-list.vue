<template>
    <div>
        <h1 class="wp-heading-inline">{{ $route.meta.title }}</h1>
        <div class="dropdown-new-role">
            <router-link
                    to="/roles/new"
                    class="page-title-action">
                {{ $i18n.get('New role') }}
            </router-link>
            <button 
                    v-tooltip="{
                        content: $i18n.get('Create a role based on: '),
                        autoHide: true,
                        placement: 'top',
                        classes: ['tainacan-roles-tooltip']     
                    }"
                    @click="showDropdownMenu = !showDropdownMenu"
                    class="button button-secondary"
                    aria-haspopup="true"
                    aria-controls="dropdown-menu"
                    :aria-expanded="showDropdownMenu">
                <span class="dashicons dashicons-arrow-down-alt2" />
            </button>

            <div 
                    :class="{ 'show': showDropdownMenu }"
                    class="dropdown-menu"
                    id="dropdown-menu"
                    :aria-hidden="showDropdownMenu">
                <!-- <p class="dropdown-menu-intro">{{ $i18n.get('Create a new role based on: ') }}</p> -->
                <ul>
                    <li 
                            v-for="role of roles"
                            :key="role.slug"
                            v-if="role.slug.match('tainacan')">
                        <router-link :to="'/roles/new?template=' + role.slug">
                            {{ role.name }}
                        </router-link>
                    </li>
                    <li><router-link to="/roles/new"><em>{{ $i18n.get('Blank') }}</em></router-link></li>
                </ul>
            </div>
        </div>
        <hr class="wp-header-end">

        <h2 class="screen-reader-text">{{ $i18n.get('Roles list') }}</h2>

        <p class="search-box">
            <label
                    class="screen-reader-text"
                    for="roles-search-input">
                {{ $i18n.get('Type to search by Role Name') }}
            </label>
            <input
                    type="search" 
                    id="roles-search-input" 
                    :placeholder="$i18n.get('Type to search by Role Name')"
                    v-model="searchString">
		</p>

        <div class="tablenav top">
            <div class="align-left actions">
                <p>{{ $i18n.get('Create and edit roles for users') }}</p>
            </div>
            <div class="tablenav-pages one-page">
                <span class="displaying-num">{{ Object.keys(roles).length + ' ' + $i18n.getWithNumber('item', 'items', Object.keys(roles).length) }}</span>
            </div>
        </div>

        <table 
                v-if="!isLoadingRoles"
                class="wp-list-table widefat fixed striped roles">
            <thead>
                <tr>
                    <!-- <td class="manage-column column-cb check-column">
                        <label
                                class="screen-reader-text"
                                for="cb-select-all">
                            {{ $i18n.get('Selecionar Todos') }}
                        </label>
                        <input
                                id="cb-select-all"
                                type="checkbox">
                    </td> -->
                    <th
                            scope="col"
                            id="name"
                            class="manage-column column-name">
                        {{ $i18n.get('Role\'s Name') }}
                    </th>
                    <!-- <th
                            scope="col"
                            id="role"
                            class="manage-column column-slug">
                        {{ $i18n.get('Slug') }}
                    </th> -->
                    <th
                            scope="col"
                            id="capabilities-number"
                            class="manage-column column-capabilities num">
                        {{ $i18n.get('Number of Capabilities') }}
                    </th>
                </tr>
            </thead>

            <tbody data-wp-lists="list:roles">
                <tr
                        v-for="role of roles"
                        :key="role.slug"
                        :id="role.slug">
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
                            class="name column-name has-row-actions column-primary"
                            :data-colname="$i18n.get('Role name')">
                        <strong>
                            <router-link 
                                    :to="'/roles/' + role.slug"
                                    class="submitdelete">
                                {{ role.name }}
                            </router-link>
                        </strong>
                        <br>
                        <div class="row-actions">
                            <span class="edit">
                                <router-link :to="'/roles/' + role.slug">
                                    {{ $i18n.get('Edit') }}
                                </router-link>
                            </span>
                            <span 
                                    v-if="role.slug.match('tainacan')"
                                    class="delete">
                                &nbsp;|&nbsp;
                                <a 
                                        @click="removeRole(role.slug)"
                                        class="submitdelete">
                                    {{ $i18n.get('Delete') }}
                                </a>
                            </span>
                        </div>
                    </td>
                    <!-- <td
                            class="slug column-slug"
                            :data-colname="$i18n.get('Slug')">
                        {{ role.slug }}
                    </td> -->
                    <td
                            class="capabilities column-capabilities num  column-primary"
                            :data-colname="$i18n.get('Number of capabilities')">
                        {{ Object.values(role.capabilities).filter((capability) => capability == true).length }}
                    </td>
                </tr>
            </tbody>

            <tfoot>
                <tr>
                    <!-- <td class="manage-column column-cb check-column">
                        <label
                                class="screen-reader-text"
                                for="cb-select-all-2">
                            {{ $i18n.get('Selecionar Todos') }}
                        </label>
                        <input
                                id="cb-select-all-2"
                                type="checkbox">
                    </td> -->
                    <th
                            scope="col"
                            id="name"
                            class="manage-column column-name column-primary">
                        {{ $i18n.get('Role\'s Name') }}
                    </th>
                    <!-- <th
                            scope="col"
                            id="role"
                            class="manage-column column-slug">
                        {{ $i18n.get('Slug') }}
                    </th> -->
                    <th
                            scope="col"
                            id="capabilities-number"
                            class="manage-column column-capabilities num">
                        {{ $i18n.get('Number of Capabilities') }}
                    </th>
                </tr>
            </tfoot>
        </table>

        <div class="tablenav bottom">
            <div 
                    style="margin-left: auto;"
                    class="tablenav-pages one-page">
                <span class="displaying-num">
                    {{ Object.keys(roles).length + ' ' + $i18n.getWithNumber('item', 'items', Object.keys(roles).length) }}
                </span>
            </div>
        </div>

    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';
     
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

                if (this.searchString) {
                    let searchedRoles = {}
                    for (let [roleKey, role] of Object.entries(roles)) {
                        if (role.name.toLowerCase().match(this.searchString))
                            searchedRoles[roleKey] = role;
                    }
                    roles = searchedRoles;
                }

                if (this.relatedEntities.length) {
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

                return roles;
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
                this.deleteRole(roleSlug)
                    .then(() => {
                        this.$forceUpdate();
                    })
            }
        }
    }
</script>

<style lang="scss" scoped>
    .tablenav {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        height: auto;
    }
    .search-box {
        margin-top: -34px;
        #roles-search-input {
            min-width: 300px;
        }
    }

    .dropdown-new-role {
        display: inline-flex;
        align-items: center;
        position: relative;

        a:first-child {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            margin-right: -1px;
            font-weight: normal;
        }
        .button {
            top: -3px;
            position: relative;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-top-right-radius: 2px;
            border-bottom-right-radius: 2px;
            padding: 0px 6px;
            line-height: 1em;
            height: 15px;
            font-size: 16px;
        }

        .dropdown-menu {
            display: none;
            opacity: 0;
            visibility: hidden;
            position: absolute;
            top: 50%;
            left: calc(100% - 42px);
            background:white;
            border: 1px solid#ccc;
            z-index: 9;
            transition: top 0.3s ease, opacity 0.3s ease, display 0.3s ease;
            text-align: center;

            &:after {
                content: "";
                display: block;
                position: absolute;
                right: calc(100% - 32px);
                width: 0;
                height: 0;
                border-style: solid;
                border-color: transparent transparent #ccc transparent;
                border-right-width: 8px;
                border-bottom-width: 8px;
                border-left-width: 8px;
                top: -12px;
            }
            &:before {
                content: "";
                display: block;
                position: absolute;
                right: calc(100% - 32px);
                width: 0;
                height: 0;
                border-style: solid;
                border-color: transparent transparent white transparent;
                border-right-width: 8px;
                border-bottom-width: 8px;
                border-left-width: 8px;
                top: -11px;
                z-index: 9;
            }

            &.show {
                display: block;
                opacity: 1;
                visibility: visible;
                top: calc(100% + 6px);
            }

            .dropdown-menu-intro {
                color: #898d8f;
                font-size: 0.75em;
                font-style: italic;
                padding: 0.75em 1em 0 0.75em;
                white-space: nowrap;
                margin: 0;
            }
            ul {
                margin: 0.5em 0;
            }
            li>a {
                display: block;
                margin: 0;
                padding: 0.25em 0.75em;
                white-space: nowrap;
                cursor: pointer;
                color: #32373c;
                text-decoration: none;

                &:hover {
                    background-color: #0073aa;;
                    color: white;
                }
            }
        }
        @media only screen and (max-width: 782px) {
            .button {
                padding: 1.24em 0.5em;  
                top: -1px;  
                font-size: 1em;
                .dashicons {
                    margin-top: -0.5em;
                }
            }
            .dropdown-menu {
                left: -50%;

                &::before,
                &::after {
                    right: 16px;
                }
            }
        }
    }

    .selected-entity { 
        margin-top: -4px;
        padding: 2px 0;
        a {
            font-weight: bold;
            color: black;
        }
    }

    table {
        table-layout: auto;

        &.widefat td,
        &.widefat th {
            padding: 8px 18px;
        }
    }

    .column-capabilities {
        width: 1px;
        white-space: nowrap;
    }
</style>
