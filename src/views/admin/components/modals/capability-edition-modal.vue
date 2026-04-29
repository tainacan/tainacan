<template>
    <div class="tainacan-form tainacan-modal-content">
        <header class="tainacan-modal-title">
            <h2 v-if="capability.display_name != undefined">
                {{ $i18n.get('label_editing_capability') + ' ' }} <span class="has-text-bold">{{ capability.display_name }}</span>
            </h2>
            <button         
                    class="button is-medium is-white is-align-self-flex-start"
                    :aria-label="$i18n.get('close')"
                    @click="closeModal()">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-close tainacan-icon-1-125em" />
                </span>
            </button>
        </header>
        <div 
                v-if="!isLoading"
                class="capabilities-edit-form">  
            <div>
                <template v-if="roles && Object.values(roles).length && capability.roles">
                    <b-field :addons="false">
                        <label class="label is-inline">
                            {{ $i18n.get('label_associated_roles') }}
                        </label>
                        <p>{{ $i18n.get('info_associated_roles') }}</p>
                        <br>
                        <div class="roles-list">
                            <template
                                    v-for="(role, roleIndex) of roles"
                                    :key="roleIndex">
                                <b-checkbox
                                        v-if="!capability.roles_inherited[role.slug]"
                                        :model-value="capability.roles[role.slug] || capability.roles_inherited[role.slug] ? true : false"
                                        name="roles"
                                        @update:model-value="($event) => updateRole(role.slug, $event)">
                                    {{ role.name }}
                                </b-checkbox>
                            </template>
                        </div>
                    </b-field>
                </template>
                <p 
                        v-else
                        class="is-italic has-text-dark">
                    {{ $i18n.get('info_no_role_associated_capability') }}
                </p>
            </div>
            <div>
                <template v-if="roles && Object.values(roles).length && capability.roles">
                    <b-field :addons="false">
                        <label class="label is-inline">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-alertcircle" />
                            {{ $i18n.get('label_inherited_roles') }}
                            <help-button
                                    :title="$i18n.get('label_inherited_roles')"
                                    :message="$i18n.get('info_inherited_roles')"
                                    extra-classes="is-warning"
                                    forced-icon-color="yellow2" />
                        </label>
                        <br>
                        <div class="roles-list">
                            <template 
                                    v-for="(role, roleIndex) of roles"
                                    :key="roleIndex">
                                <b-checkbox
                                        v-if="capability.roles_inherited[role.slug]"
                                        class="has-text-warning"
                                        :model-value="capability.roles[role.slug] || capability.roles_inherited[role.slug] ? true : false"
                                        name="roles_inherited"
                                        disabled>
                                    {{ role.name }}
                                </b-checkbox>
                            </template>
                        </div>
                    </b-field>
                </template>
                <p 
                        v-else
                        class="is-italic has-text-dark">
                    {{ $i18n.get('info_no_role_associated_capability') }}
                </p>
            </div>
        </div>
        <div v-else>
            <b-loading
                    v-model="isLoading" 
                    :is-full-page="false" />
        </div>
        <div class="field is-grouped form-submit">
            <div class="control">
                <button
                        id="button-cancel-importer-edition"
                        class="button is-outlined"
                        type="button"
                        @click="closeModal()">
                    {{ $i18n.get('exit') }}</button>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'CapabilityEditionModal',
    props: {
        capability: Object,
        capabilityKey: String
    },
    emits: [
        'beforeClose',
        'close'
    ],
    data() {
        return {
            isLoading: false
        }
    },
    computed: {
        ...mapGetters('capability', {
            'roles': 'getRoles',
        })
    },
    created() {
        this.isLoading = true;
        this.fetchRoles().then(() => {
            this.isLoading = false;
        });
    },
    methods: {
        ...mapActions('capability', [
            'fetchRoles',
            'addCapabilityToRole',
            'removeCapabilityFromRole'
        ]),
        closeModal() {
            this.$emit('beforeClose');
            this.$emit('close');
        },
        updateRole(role, value) {
            if (value)
                this.addCapabilityToRole({ capabilityKey: this.capabilityKey.replace('%d', 'all'), role: role })
            else 
                this.removeCapabilityFromRole({ capabilityKey: this.capabilityKey.replace('%d', 'all'), role: role })
        }
    }
}
</script>

<style lang="scss" scoped>
 
    .capabilities-edit-form {
        min-height: 120px;
        vertical-align: top;
        display: flex;

        &>div {
            padding: 24px;
            &:first-of-type {
                flex-grow: 3;
                flex-shrink: 1;
            }    
            &:last-of-type {
                background-color: var(--tainacan-yellow1);
                color: var(--tainacan-yellow1);

                &:has(.roles-list:empty) {
                    display: none;
                }

                .label,
                .control-label,
                .check {
                    color: var(--tainacan-yellow2);
                }
            }
        }
        .roles-list {
            height: 100%;
            column-count: 2;
            break-inside: avoid;

            label {
                margin-inline-start: 12px;

                :deep(.control-label) {
                    white-space: normal;
                }
            }
        }
        @media screen and (max-width: 1024px) {
            .roles-list {
                column-count: 1;
            }
        }
        @media screen and (max-width: 768px) {
            flex-wrap: wrap;
        }
    }
</style>
