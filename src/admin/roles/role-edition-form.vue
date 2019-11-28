<template>
    <div>
        <h1 class="wp-heading-inline">{{ $route.meta.title }}&nbsp;<strong>{{ role.name }}</strong></h1>
        <hr class="wp-header-end">
        <br>
        <template v-if="!isLoadingRole && !isLoadingCapabilities">
            <h2>{{ $i18n.get('Role\'s Repository related Capabilities List') }}</h2>
            <table class="wp-list-table widefat fixed striped capabilities">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <label
                                    class="screen-reader-text"
                                    for="cb-select-all-repository">
                                {{ $i18n.get('Selecionar Todos') }}
                            </label>
                            <input
                                    id="cb-select-all-repository"
                                    type="checkbox">
                        </td>
                        <th
                                scope="col"
                                id="name-repository"
                                class="manage-column column-name">
                            <a>
                                {{ $i18n.get('Name') }}
                            </a>
                        </th>
                        <th
                                scope="col"
                                id="description-repository"
                                class="manage-column column-description">
                            <a>
                                {{ $i18n.get('Description') }}
                            </a>
                        </th>
                    </tr>
                </thead>

                <tbody data-wp-lists="list:repository-capabilities">
                    <tr
                            v-for="(capability, index) of repositoryCapabilities"
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
                                :checked="role.capabilities[index]"
                                @input="onUpdateCapability($event.target.checked, index)">
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
                                    for="cb-select-all-repository-2">
                                {{ $i18n.get('Selecionar Todos') }}
                            </label>
                            <input
                                    id="cb-select-all-repository-2"
                                    type="checkbox">
                        </td>
                        <th
                                scope="col"
                                id="name-repository"
                                class="manage-column column-name column-primary">
                            <a>
                                {{ $i18n.get('Name') }}
                            </a>
                        </th>
                        <th
                                scope="col"
                                id="description-repository"
                                class="manage-column column-description">
                            <a>
                                {{ $i18n.get('Description') }}
                            </a>
                        </th>
                    </tr>
                </tfoot>
            </table>

            <br>

            <h2>{{ $i18n.get('Role\'s Collection related Capabilities List') }}</h2>
            <table class="wp-list-table widefat fixed striped capabilities">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <label
                                    class="screen-reader-text"
                                    for="cb-select-all-collection">
                                {{ $i18n.get('Selecionar Todos') }}
                            </label>
                            <input
                                    id="cb-select-all-collection"
                                    type="checkbox">
                        </td>
                        <th
                                scope="col"
                                id="name-collection"
                                class="manage-column column-name">
                            <a>
                                {{ $i18n.get('Name') }}
                            </a>
                        </th>
                        <th
                                scope="col"
                                id="description-collection"
                                class="manage-column column-description">
                            <a>
                                {{ $i18n.get('Description') }}
                            </a>
                        </th>
                    </tr>
                </thead>

                <tbody data-wp-lists="list:collection-capabilities">
                    <tr
                            v-for="(capability, index) of collectionCapabilities"
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
                                :checked="role.capabilities[index]"
                                @input="onUpdateCapability($event.target.checked, index)">
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
                                    for="cb-select-all-collection-2">
                                {{ $i18n.get('Selecionar Todos') }}
                            </label>
                            <input
                                    id="cb-select-all-collection-2"
                                    type="checkbox">
                        </td>
                        <th
                                scope="col"
                                id="name-collection"
                                class="manage-column column-name column-primary">
                            <a>
                                {{ $i18n.get('Name') }}
                            </a>
                        </th>
                        <th
                                scope="col"
                                id="description-collection"
                                class="manage-column column-description">
                            <a>
                                {{ $i18n.get('Description') }}
                            </a>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </template>
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
            collectionCapabilities() {
                const capabilities = this.getCapabilities();
                let collectionCapabilities = {}

                for (let [capabilityKey, capability] of Object.entries(capabilities)) {
                    if (capability.scope === 'repository')
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
            ...mapActions('capability', [
                'fetchRole',
                'fetchCapabilities',
                'addCapabilityToRole',
                'removeCapabilityFromRole'
            ]),
            ...mapGetters('capability', [
                'getRole',
                'getCapabilities'
            ]),
            onUpdateCapability(value, capabilityKey) {
                if (value)
                    this.addCapabilityToRole({ capabilityKey: capabilityKey, role: this.roleSlug })
                else
                    this.removeCapabilityFromRole({ capabilityKey: capabilityKey, role: this.roleSlug })
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
        }
    }
</script>