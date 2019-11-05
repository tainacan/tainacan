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
                            <td
                                    class="table-creation column-small-width"
                                    :label="$i18n.get('label_associated_roles')"
                                    :aria-label="$i18n.get('label_associated_roles') + ': ' + capability.roles.length">
                                <p
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: capability.roles.length,
                                            autoHide: false,
                                            classes: ['tooltip'],
                                            placement: 'auto-start'
                                        }"
                                        v-html="capability.roles.length ? JSON.stringify(capability.roles) : '<span class=is-italic>' + $i18n.get('info_no_associated_role') + '</span>'"/>
                            </td>
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
                                    <b-field :addons="false">
                                        <label class="label is-inline-block">
                                            {{ $i18n.get('label_associated_roles') }}
                                            <help-button
                                                    :title="$i18n.get('label_associated_roles')"
                                                    :message="$i18n.get('info_associated_roles')"/>
                                        </label>
                                        <template v-if="capability.roles.length">
                                            <b-checkbox
                                                    v-for="(role, roleIndex) of capability.roles"
                                                    :key="roleIndex"
                                                    size="is-small"
                                                    @input="updateRole(role, index)"
                                                    name="roles">
                                                {{ role }}
                                            </b-checkbox>
                                        </template>
                                        <p v-else>{{ $i18n.get('info_no_role_associated_capability') }}</p>
                                    </b-field>
                                </td>
                            </transition>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Empty state image -->
        <div v-if="capabilities.length <= 0 && !isLoading">
            <section class="section">
                <div class="content has-text-grey has-text-centered">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-user"/>
                    </span>
                    <p class="is-italic has-text-gray">{{ $i18n.get('info_no_capability_found') }}</p>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex';

    export default {
        name: 'CapabilitiesList',
        props: {
            isLoading: false,
            collectionId: String,
            capabilities: Array,
            editingCapability: ''
        },
        methods: {
            ...mapActions('capability', [
                'associateCapabilityWithRole',
                'disassociateCapabilityWithRole'
            ]),
            toggleEditForm(capabilityKey) {
                if (this.editingCapability == capabilityKey)
                    this.editingCapability = ''    
                else
                    this.editingCapability = capabilityKey;
            },
            updateRole(role, capabilityKey) {
                if (role)
                    this.disassociateCapabilityWithRole({ capability: capabilityKey, role: role })
                else 
                    this.disassociateCapabilityWithRole({ capability: capabilityKey, role: role })
            }
        }
    }
</script>

<style scoped>
.table-container .table-wrapper table.tainacan-table tbody tr {
    cursor: default;
}
.table-container .table-wrapper table.tainacan-table tbody tr.capabilities-edit-form td {
    min-height: 300px;
    height: 300px;
    padding: 16px 24px;
    background-color: #f6f6f6;
    vertical-align: top;
}
.table-container .table-wrapper table.tainacan-table tbody tr.capabilities-edit-form:hover {
    background-color: #f6f6f6 !important;
}
</style>
