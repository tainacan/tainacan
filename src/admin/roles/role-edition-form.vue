<template>
    <div>
        <h1 class="wp-heading-inline">{{ $route.meta.title }}&nbsp;<strong>{{ role.name }}</strong></h1>
        <hr class="wp-header-end">
        <br>
        <h2 class="screen-reader-text">{{ $i18n.get('Role Capabilities list') }}</h2>
        <table 
                v-if="!isLoading"
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
                </tr>
            </thead>

            <tbody data-wp-lists="list:roles">
                <tr
                        v-for="(capability, index) of role.capabilities"
                        :key="index"
                        :id="'capability-' + index">
                    <th
                            scope="row"
                            class="check-column">
                        <label
                                class="screen-reader-text"
                                :for="'capability_' + index">
                            {{ $i18n.get('Selecionar') + ' ' + capability }}
                        </label>
                        <input
                            type="checkbox"
                            name="roles[]"
                            :id="'capability_'+ index"
                            :value="index">
                    </th>
                    <td 
                            class="name column-name has-row-actions"
                            :data-colname="$i18n.get('Role name')">
                        <strong>
                            <a>{{ index }}</a>
                        </strong>
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
                isLoading: false
            }
        },
        computed: {
            role() {
                return this.getRole()
            }
        },
        methods: {
            ...mapActions('capability', [
                'fetchRole'
            ]),
            ...mapGetters('capability', [
                'getRole'
            ]),
        },
        created() {
            this.roleSlug = this.$route.params.roleSlug;

            this.isLoading = true;
            this.fetchRole(this.roleSlug)
                .then(() => {
                    this.isLoading = false;
                }).catch(() => {
                    this.isLoading = false;
                });
        }
    }
</script>