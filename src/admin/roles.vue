<template>
    <div
            id="tainacan-roles-app"
            class="wrap">
        <h1 class="wp-heading-inline">{{ $i18n.get('Roles') }}</h1>
        <a class="page-title-action">
            {{ $i18n.get('Add new role') }}
        </a>
        <hr class="wp-header-end">
        <br>
        <h2 class="screen-reader-text">{{ $i18n.get('Roles list') }}</h2>
        <table 
                v-if="!isLoadingRoles"
                class="wp-list-table widefat fixed striped users">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <label
                                class="screen-reader-text"
                                for="cb-select-all">
                            {{ $i18n.get('Selecionar Todos') }}
                        </label>
                        <input
                                id="cb-select-all"
                                type="checkbox">
                    </td>
                    <th
                            scope="col"
                            id="name"
                            class="manage-column column-name">
                        <a>
                            {{ $i18n.get('Name') }}
                        </a>
                    </th>
                    <th
                            scope="col"
                            id="role"
                            class="manage-column column-slug">
                        {{ $i18n.get('Slug') }}
                    </th>
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
                    <th
                            scope="row"
                            class="check-column">
                        <label
                                class="screen-reader-text"
                                :for="'user_' + role.slug">
                            {{ $i18n.get('Selecionar') + ' ' + role.name }}
                        </label>
                        <input
                            type="checkbox"
                            name="roles[]"
                            :id="'user_'+ role.slug"
                            :value="role.slug">
                    </th>
                    <td 
                            class="name column-name has-row-actions column-primary"
                            :data-colname="$i18n.get('Role name')">
                        <strong>
                            <a>{{ role.name }}</a>
                        </strong>
                        <br>
                        <div class="row-actions">
                            <span class="edit"><a>{{ $i18n.get('Editar') }}</a> | </span>
                            <span class="view"><a>{{ $i18n.get('Editar') }}</a></span>
                        </div>
                        <button
                                type="button"
                                class="toggle-row">
                            <span class="screen-reader-text">{{ $i18n.get('Mostrar detalhes') }}</span>
                        </button>
                    </td>
                    <td
                            class="slug column-slug"
                            :data-colname="$i18n.get('Slug')">
                        {{ role.slug }}
                    </td>
                    <td
                            class="capabilities column-capabilities num"
                            :data-colname="$i18n.get('Number of capabilities')">
                        {{ Object.values(role.capabilities).length }}
                    </td>
                </tr>
            </tbody>

            <tfoot>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <label
                                class="screen-reader-text"
                                for="cb-select-all-2">
                            {{ $i18n.get('Selecionar Todos') }}
                        </label>
                        <input
                                id="cb-select-all-2"
                                type="checkbox">
                    </td>
                    <th
                            scope="col"
                            id="name"
                            class="manage-column column-name column-primary">
                        <a>
                            {{ $i18n.get('Name') }}
                        </a>
                    </th>
                    <th
                            scope="col"
                            id="role"
                            class="manage-column column-slug">
                        {{ $i18n.get('Slug') }}
                    </th>
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
        name: "RolesPage",
        data() {
            return {
                isLoadingRoles: false,
            }
        },
        computed: {
            roles() {
                return this.getRoles();
            }
        },
        methods: {
            ...mapActions('capability', [
                'fetchRoles'
            ]),
            ...mapGetters('capability', [
                'getRoles'
            ]),
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

<style lang="scss">

</style>