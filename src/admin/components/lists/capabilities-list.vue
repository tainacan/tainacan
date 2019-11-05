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
                    </tr>
                </thead>
                <tbody>
                    <tr
                            :key="index"
                            v-for="(capability, index) of capabilities">
                        <!-- Name -->
                        <td
                                class="column-default-width column-main-content"
                                @click="openCapabilityDetailsModal(capability)"
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
                                @click="openCapabilityDetailsModal(capability)"
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
                                @click="openCapabilityDetailsModal(capability)"
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
                    </tr>
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
                    <p>{{ $i18n.get('info_no_capability_found') }}</p>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
    // import CapabilityDetailsModal from '../other/capability-details-modal.vue';

    export default {
        name: 'CapabilitiesList',
        props: {
            isLoading: false,
            collectionId: String,
            capabilities: Array
        },
        methods: {
            approveCapability(capability) {
                this.approve(capability.id)
                    .then(data => {
                        this.$console.info('approved!', data);
                    })
                    .catch(error => this.$console.error(error));
            },
            openCapabilityDetailsModal(capability) {
                this.$console.log(capability)
                // this.$buefy.modal.open({
                //     parent: this,
                //     component: CapabilityDetailsModal,
                //     props: {
                //         collectionId: collectionId,
                //         capabilityId: capability.id,
                //     },
                //     trapFocus: true
                // });
            },
        }
    }
</script>
