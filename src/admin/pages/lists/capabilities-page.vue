<template>
    <div 
            :class="{
                'repository-level-page': isRepositoryLevel,
                'page-container': isRepositoryLevel
            }">
        <tainacan-title 
                :bread-crumb-items="[{ path: '', label: this.$i18n.get('capabilities') }]"/>

        <div class="sub-header">
            <b-field 
                    style="margin-left: auto; margin-right: 0;"
                    class="header-item">
                <div class="control has-icons-right  is-small is-clearfix">
                    <b-autocomplete
                            v-model="filteredRole"
                            :data="filteredRoles"
                            :placeholder="$i18n.get('instruction_type_search_roles_filter')"
                            keep-first
                            open-on-focus
                            :loading="isFetchingRoles"
                            field="name"
                            icon="magnify" />
                </div>
            </b-field>
        </div>
        <div>
            <b-loading
                    :is-full-page="true" 
                    :active.sync="isLoading" 
                    :can-cancel="false"/>

            <capabilities-list
                    :is-loading="isLoading || isFetchingRoles"
                    :capabilities="capabilities"/>
            
            <!-- Empty state -->
            <div v-if="capabilities.length <= 0 && !isLoading">
                <section class="section">
                    <div class="content has-text-grey has-text-centered">
                        <span class="icon is-medium">
                            <i class="tainacan-icon tainacan-icon-36px tainacan-icon-user"/>
                        </span>
                        <p>
                            {{ $i18n.get('info_no_capabilities_found') }}
                        </p>
                    </div>
                </section>
            </div>
            <!-- Footer -->
            <div 
                    class="pagination-area" 
                    v-if="capabilities.length > 0">
                <div class="shown-items">
                    {{
                        $i18n.get('info_showing_capabilities') +
                        (capabilitiesPerPage * (page - 1) + 1) +
                        $i18n.get('info_to') +
                        capabilities.length + 
                        $i18n.get('info_of') + total + '.'
                    }}
                </div>
            </div>
        </div>  
    </div>
</template>

<script>
    import CapabilitiesList from "../../components/lists/capabilities-list.vue";
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'CapabilitiesPage',
        data() {
            return {
                isRepositoryLevel: false,
                isLoading: false,
                roles: [],
                isFetchingRoles: false,
                filteredRole: ''
            }
        },
        computed: {
            capabilities() {
                const capabilities = this.getCapabilities()
                if (capabilities) {
                    if (this.filteredRole) {
                        let filteredCapabilities = {};

                        for (let [capabilitySlug, capability] of Object.entries(capabilities)) {
                            const rolesArray = capability.roles && !Array.isArray(capability.roles) ? Object.values(capability.roles) : [];
                            const rolesInheritedArray = capability.roles_inherited && !Array.isArray(capability.roles_inherited) ? Object.values(capability.roles_inherited) : [];
                            const completeRoles = rolesArray.map(role => role.name).concat(rolesInheritedArray.map(roleInherited => roleInherited.name))

                            if (completeRoles.toString().search(this.filteredRole) >= 0)
                                filteredCapabilities[capabilitySlug] = capability;
                        }
                        return Object.keys(filteredCapabilities).length === 0 ? [] : filteredCapabilities;
                    } else {
                        return capabilities;
                    }
                }
                else {
                    return []
                }
            },
            filteredRoles() {
                if (this.roles && this.roles.length) {
                    return this.roles
                        .filter((option) => {
                            if (option) {
                                return option.name
                                    .toString()
                                    .toLowerCase()
                                    .indexOf(this.filteredRole.toLowerCase()) >= 0
                            } else {
                                return false
                            }
                        });
                } else {
                    return []
                }
            }
        },
        components: {
            CapabilitiesList
        },
        methods: {
            ...mapActions('capability', [
                'fetchCapabilities',
                'fetchRoles'
            ]),
            ...mapGetters('capability', [
                'getCapabilities'
            ]),
            loadCapabilities() {
                this.isLoading = true;

                this.fetchCapabilities({ collectionId: this.$route.params.collectionId })
                    .then(() => {
                        this.isLoading = false;
                    })
                    .catch(() => {
                        this.isLoading = false;
                    });
            },
            fetchRolesForFiltering() {
                this.isFetchingRoles = true;

                this.fetchRoles()
                    .then((roles) => {
                        this.roles = Object.values(roles);
                        this.isFetchingRoles = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingRoles = false;
                    });
            }
        },
        mounted() {
            this.loadCapabilities();
            this.fetchRolesForFiltering();
        },
        created() {
            this.isRepositoryLevel = (this.$route.params.collectionId === undefined);
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../scss/_variables.scss';

    .sub-header {
        min-height: $subheader-height;
        height: $header-height;
        padding-left: 0;
        padding-right: 0;
        border-bottom: 1px solid #ddd;
        display: inline-flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        width: 100%;

        .header-item {
            margin-bottom: 0 !important;

            &:first-child {
                margin-right: auto;
            }
            &:not(:last-child) {
                padding-right: 0.5em;
            }

            .label {
                font-size: 0.875rem;
                font-weight: normal;
                margin-top: 3px;
                margin-bottom: 2px;
                cursor: default;
            }

            &:not(:first-child) {
                .button {
                    display: flex;
                    align-items: center;
                    border-radius: 0 !important;
                    height: 1.95rem !important;
                }
            }
            
            .field {
                align-items: center;
            }

            .gray-icon, .gray-icon .icon {
                color: $gray4 !important;
                padding-right: 10px;
            }
            .gray-icon .icon i::before, 
            .gray-icon i::before {
                font-size: 1.3125rem !important;
                max-width: 26px;
            }

            .icon {
                pointer-events: all;
                cursor: pointer;
                color: $blue5;
                height: 27px;
                font-size: 18px !important;
                height: 1.75rem !important;
            }
        }

        @media screen and (max-width: 769px) {
            height: 160px;
            margin-top: -0.5em;
            padding-top: 0.9em;

            .header-item:not(:last-child) {
                padding-right: 0.2em;
            }
        }
    }
    .above-subheader {
        margin-bottom: 0;
        margin-top: 0;
        height: auto;
    }
</style>


