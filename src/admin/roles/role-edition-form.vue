<template>
    <div>
        <h1 class="wp-heading-inline">{{ $route.meta.title }}&nbsp;<strong>{{ role.name }}</strong></h1>
        <hr class="wp-header-end">
        <br>
        <h2 class="screen-reader-text">{{ $i18n.get('Role Capabilities list') }}</h2>
        <table 
                v-if="!isLoadingRole"
                class="wp-list-table widefat fixed striped capabilities">
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
                            id="description"
                            class="manage-column column-description">
                        <a>
                            {{ $i18n.get('Description') }}
                        </a>
                    </th>
                </tr>
            </thead>

            <tbody data-wp-lists="list:capabilities">
                <tr
                        v-for="(capability, index) of capabilities"
                        :key="index"
                        :id="'capability-' + index">
                    <th
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
                            :value="role.capabilities[index]"
                            :checked="role.capabilities[index]">
                    </th>
                    <td 
                            class="name column-name"
                            :data-colname="$i18n.get('Capability name')">
                        <strong>{{ capability.display_name }}</strong>
                    </td>
                    <td 
                            class="description column-descritption"
                            :data-colname="$i18n.get('Capabilitiy description')">
                        {{ capability.description }}
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
                            id="description"
                            class="manage-column column-description">
                        <a>
                            {{ $i18n.get('Description') }}
                        </a>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';

    export default {
        data() {
            return {
                isLoadingRole: false,
                isLoadingCapabilities: false
            }
        },
        computed: {
            role() {
                return this.getRole()
            },
            capabilities() {
                return this.getCapabilities();
            }
        },
        methods: {
            ...mapActions('capability', [
                'fetchRole',
                'fetchCapabilities'
            ]),
            ...mapGetters('capability', [
                'getRole',
                'getCapabilities'
            ]),
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
        }
    }
</script>