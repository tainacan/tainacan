<template>
    <div>
        <h1 class="wp-heading-inline">{{ $route.meta.title }}</h1>
        <router-link
                to="/roles/new"
                class="page-title-action">
            {{ $i18n.get('Add new role') }}
        </router-link>
        <hr class="wp-header-end">
        
        <br>
        
        <label>{{ $i18n.get('Filter list by roles with capabilities related to:') }}</label>
        <ul 
                class="subsubsub"
                style="float: none;">
            <li :class="{ 'selected-entity': currentRelatedEntity == '' }">
                <a @click="filteByCapabilitiesRelatedTo('')">{{ $i18n.get('Any') }} </a> |
            </li>
            <li :class="{ 'selected-entity': currentRelatedEntity == 'repository' }">
                <a @click="filteByCapabilitiesRelatedTo('repository')">{{ $i18n.get('Repository') }} </a> |
            </li>
            <li :class="{ 'selected-entity': currentRelatedEntity == 'taxonomies' }">
                <a @click="filteByCapabilitiesRelatedTo('taxonomies')">{{ $i18n.get('Taxonomies') }} </a> |
            </li>
            <li :class="{ 'selected-entity': currentRelatedEntity == 'collections' }">
                <a @click="filteByCapabilitiesRelatedTo('collections')">{{ $i18n.get('Collections') }} </a> |
            </li>
            <li :class="{ 'selected-entity': currentRelatedEntity == 'metadata' }">
                <a @click="filteByCapabilitiesRelatedTo('metadata')">{{ $i18n.get('Metadata') }} </a> |
            </li>
            <li :class="{ 'selected-entity': currentRelatedEntity == 'filters' }">
                <a @click="filteByCapabilitiesRelatedTo('filter')">{{ $i18n.get('Filter') }} </a> |
            </li>
            <li :class="{ 'selected-entity': currentRelatedEntity == 'activities' }">
                <a @click="filteByCapabilitiesRelatedTo('activities')">{{ $i18n.get('Activities') }} </a>
            </li>
        </ul>
        
        <br>

        <h2 class="screen-reader-text">{{ $i18n.get('Roles list') }}</h2>

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
                        <a>
                            {{ $i18n.get('Name') }}
                        </a>
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
                            {{ role.name }}
                        </strong>
                        <br>
                        <div class="row-actions">
                            <span class="edit"><router-link :to="'/roles/' + role.slug">{{ $i18n.get('Edit') }}</router-link> | </span>
                            <span class="delete"><a class="submitdelete">{{ $i18n.get('Delete') }}</a></span>
                        </div>
                    </td>
                    <!-- <td
                            class="slug column-slug"
                            :data-colname="$i18n.get('Slug')">
                        {{ role.slug }}
                    </td> -->
                    <td
                            class="capabilities column-capabilities num"
                            :data-colname="$i18n.get('Number of capabilities')">
                        {{ Object.values(role.capabilities).length }}
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
                        <a>
                            {{ $i18n.get('Name') }}
                        </a>
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
                currentRelatedEntity: ''
            }
        },
        computed: {
            roles() {
                const roles = this.getRoles();

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
                    return filteredRoles;
                }
                return roles;
            }
        },
        methods: {
            ...mapActions('capability', [
                'fetchRoles'
            ]),
            ...mapGetters('capability', [
                'getRoles'
            ]),
            filteByCapabilitiesRelatedTo(entityName) {
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
        }
    }
</script>

<style lang="scss" scoped>
    .selected-entity a {
        font-weight: bold;
        color: black;
    }
</style>
