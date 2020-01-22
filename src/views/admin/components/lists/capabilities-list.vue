<template>
    <div class="table-container">
        <div class="table-wrapper">
            <table class="tainacan-table is-narrow">
                <thead>
                    <tr>
                        <!-- Name -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_name') }}</div>
                        </th>
                        <!-- Description -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_description') }}</div>
                        </th>
                        <!-- Capability date -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_user_roles') }}</div>
                        </th>
                        <!-- Actions -->
                        <th class="actions-header">
                            &nbsp;
                            <!-- nothing to show on header for actions cell-->
                        </th>
                    </tr>
                </thead>
                <tbody v-if="!isLoading">
                    <template v-for="(capability, index) of capabilities">
                        <tr :key="index">
                            <!-- Name -->
                            <td
                                    class="column-default-width column-main-content"
                                    :label="$i18n.get('label_name')"
                                    :aria-label="$i18n.get('label_name') + ': ' + capability.display_name">
                                <p
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 120,
                                            },
                                            content: capability.display_name,
                                            autoHide: false,
                                            classes: ['tooltip', 'repository-tooltip'],
                                            placement: 'auto-start'
                                        }">
                                    {{ capability.display_name }}
                                </p>
                            </td>
                            <!-- Description -->
                            <td
                                    class="table-creation column-large-width"
                                    :label="$i18n.get('label_description')"
                                    :aria-label="$i18n.get('label_description') + ': ' + capability.description">
                                <p
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 120,
                                            },
                                            content: capability.description,
                                            autoHide: false,
                                            classes: ['tooltip', 'repository-tooltip'],
                                            placement: 'auto-start'
                                        }"
                                        v-html="capability.description"/>
                            </td>
                            <!-- Associated Roles -->
                            <complete-roles-list 
                                    v-if="capability.roles"
                                    :complete-roles-list="getCompleteRolesList(capability.roles, capability.roles_inherited)">
                                <td
                                        slot-scope="props"
                                        class="table-creation column-small-width"
                                        :label="$i18n.get('label_associated_roles')"
                                        :aria-label="$i18n.get('label_associated_roles') + ': ' + props['complete-roles-list']">
                                    <p
                                            v-tooltip="{
                                                delay: {
                                                    show: 500,
                                                    hide: 120,
                                                },
                                                content: props['complete-roles-list'],
                                                autoHide: false,
                                                classes: ['tooltip', 'repository-tooltip'],
                                                placement: 'auto-start'
                                            }"
                                            v-html="props['complete-roles-list']"/>
                                </td>
                            </complete-roles-list>
                            <!-- Actions -->
                            <td  
                                    class="actions-cell column-default-width" 
                                    :label="$i18n.get('label_actions')">
                                <div class="actions-container">
                                    <a 
                                            id="button-edit" 
                                            :aria-label="$i18n.get('edit')" 
                                            @click="openCapabilitiyEditModal(index)">                      
                                        <span 
                                                v-tooltip="{
                                                    content: $i18n.get('edit'),
                                                    autoHide: true,
                                                    classes: ['tooltip', 'repository-tooltip'],
                                                    placement: 'auto'
                                                }"
                                                class="icon">
                                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-edit"/>
                                        </span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import CapabilityEditionModal from '../modals/capability-edition-modal.vue';

    // Auxiliary component for avoinding multiple calls to getCompleteRolesList
    const CompleteRolesList = {
        render() {
            return this.$scopedSlots.default(this.$attrs)
        }
    }
    export default {
        name: 'CapabilitiesList',
        components: {
            CompleteRolesList
        },
        props: {
            isLoading: false,
            capabilities: Array
        },
        methods: {
            openCapabilitiyEditModal(capabilityKey) {
                this.$buefy.modal.open({
                    parent: this,
                    component: CapabilityEditionModal,
                    props: {
                        capability: this.capabilities[capabilityKey],
                        capabilityKey: capabilityKey
                    },
                    trapFocus: true
                });
            },
            getCompleteRolesList(roles, rolesInherited) {
                const rolesArray = roles && !Array.isArray(roles) ? Object.values(roles) : [];
                const rolesInheritedArray = rolesInherited && !Array.isArray(rolesInherited) ? Object.values(rolesInherited) : [];

                if (rolesArray.length || rolesInheritedArray.length) {
                    const completeRoles = rolesArray.map(role => role.name).concat(rolesInheritedArray.map(roleInherited => roleInherited.name))
                    let completeRolesString = '';
                    for (let i = 0; i < completeRoles.length; i++) {
                        completeRolesString += completeRoles[i];
                        if (completeRoles.length > 2 && i < completeRoles.length - 1) {
                            if (i < completeRoles.length - 2)
                                completeRolesString += ', '
                            else
                                completeRolesString += ' ' + this.$i18n.get('label_and') + ' ';
                        } else if (completeRoles.length == 2 && i == 0) {
                            completeRolesString += ' ' + this.$i18n.get('label_and') + ' ';
                        }
                    }
                    return completeRolesString
                }
                else    
                    return '<span class=is-italic>' + this.$i18n.get('info_no_associated_role') + '</span>';
            }
        },

    }
</script>

<style scoped lang="scss">

.table-container .table-wrapper table.tainacan-table tbody tr {
    cursor: default;
}

</style>
