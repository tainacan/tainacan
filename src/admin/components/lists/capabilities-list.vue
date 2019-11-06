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
                            <div class="th-wrap">{{ $i18n.get('label_associated_roles') }}</div>
                        </th>
                        <!-- Actions -->
                         <th class="actions-header">
                            &nbsp;
                            <!-- nothing to show on header for actions cell-->
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(capability, index) of capabilities">
                        <tr 
                                :key="index"
                                :style="index == editingCapability ? 'background-color: #f2f2f2' : ''">
                            <!-- Name -->
                            <td
                                    class="column-default-width column-main-content"
                                    :label="$i18n.get('label_name')"
                                    :aria-label="$i18n.get('label_name') + ': ' + capability.display_name">
                                <p
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: capability.display_name,
                                            autoHide: false,
                                            classes: ['tooltip'],
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
                                                hide: 300,
                                            },
                                            content: capability.description,
                                            autoHide: false,
                                            classes: ['tooltip'],
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
                                                    hide: 300,
                                                },
                                                content: props['complete-roles-list'],
                                                autoHide: false,
                                                classes: ['tooltip'],
                                                placement: 'auto-start'
                                            }"
                                            v-html="props['complete-roles-list']"/>
                                </td>
                            </complete-roles-list>
                            <!-- Actions -->
                            <td  
                                    class="actions-cell column-default-width" 
                                    :label="$i18n.get('label_actions')">
                                <div 
                                        class="actions-container"
                                        :style="index == editingCapability ? 'background-color: #dbdbdb' : ''">
                                    <a 
                                            id="button-edit" 
                                            :aria-label="$i18n.get('edit')" 
                                            @click="toggleEditForm(index)">                      
                                        <span 
                                                v-tooltip="{
                                                    content: $i18n.get('edit'),
                                                    autoHide: true,
                                                    classes: ['tooltip'],
                                                    placement: 'auto'
                                                }"
                                                class="icon">
                                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-edit"/>
                                        </span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr 
                                :key="index"
                                class="capabilities-edit-form">
                            <transition name="form-collapse">
                                <td 
                                        v-if="index == editingCapability"
                                        class="tainacan-form"
                                        colspan="4">
                                    <template v-if="existingRoles && Object.values(existingRoles).length && capability.roles">
                                        <b-field :addons="false">
                                            <label class="label is-inline-block">
                                                {{ $i18n.get('label_associated_roles') }}
                                                <help-button
                                                        :title="$i18n.get('label_associated_roles')"
                                                        :message="$i18n.get('info_associated_roles')"/>
                                            </label>
                                            <div class="roles-list">
                                                <b-checkbox
                                                        v-for="(role, roleIndex) of existingRoles"
                                                        :key="roleIndex"
                                                        size="is-small"
                                                        :value="capability.roles[role.slug] || capability.roles_inherited[role.slug] ? true : false"
                                                        @input="($event) => updateRole(role.slug, index, $event)"
                                                        name="roles"
                                                        :disabled="capability.roles_inherited[role.slug]">
                                                    {{ role.name }}
                                                </b-checkbox>
                                            </div>
                                        </b-field>
                                    </template>
                                    <p 
                                            v-else
                                            class="is-italic has-text-gray">
                                        {{ $i18n.get('info_no_role_associated_capability') }}
                                    </p>
                                </td>
                            </transition>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex';

    // Auxiliary component for avoinding multiple calls to getCompleteRolesList
    const CompleteRolesList = {
        render() {
            return this.$scopedSlots.default(this.$attrs)
        }
    }
    export default {
        name: 'CapabilitiesList',
        props: {
            isLoading: false,
            collectionId: String,
            capabilities: Array,
            editingCapability: ''
        },
        components: {
            CompleteRolesList
        },
        data() {
            return {
                existingRoles: []
            }
        },
        methods: {
            ...mapActions('capability', [
                'fetchRoles',
                'addCapabilityToRole',
                'removeCapabilityFromRole'
            ]),
            toggleEditForm(capabilityKey) {
                if (this.editingCapability == capabilityKey)
                    this.editingCapability = ''    
                else
                    this.editingCapability = capabilityKey;
            },
            updateRole(role, capabilityKey, value) {
                if (value)
                    this.addCapabilityToRole({ capabilityKey: capabilityKey, role: role })
                else 
                    this.removeCapabilityFromRole({ capabilityKey: capabilityKey, role: role })
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
        created() {
            this.fetchRoles().then(roles => this.existingRoles = roles);
        }
    }
</script>

<style scoped lang="scss">
.table-container .table-wrapper table.tainacan-table tbody tr {
    cursor: default;
    
    &.capabilities-edit-form td{
        min-height: 300px;
        height: 300px;
        padding: 16px 24px;
        background-color: #f6f6f6;
        vertical-align: top;

        .roles-list {
            margin-top: 12px;
            column-count: 3;
            break-inside: avoid;

            label {
                margin-left: 12px;
            }

            @media screen and (max-width: 768px) {
                column-count: 1;
            }
        }
    }
    &.capabilities-edit-form:hover {
        background-color: #f6f6f6 !important;
    }
}

</style>
