<template>
    <div class="activities-timeline-container">
        <div
                v-if="activities.length > 0"
                class="activities-timeline">
            <template
                    v-for="(activity, index) of activities"
                    :key="activity.id != null ? activity.id : index">
                <div
                        v-if="isNewDay(activity, index)"
                        class="activities-timeline-day-separator">
                    <div class="activities-timeline-day-separator__line-wrap">
                        <span
                                class="activities-timeline-day-separator__dot"
                                aria-hidden="true" />
                    </div>
                    <span
                            class="activities-timeline-day-separator__label"
                            :aria-label="$i18n.getWithVariables('label_changes_on_%s', [formatActivityDay(activity.date)])">
                        {{ $i18n.getWithVariables('label_changes_on_%s', [formatActivityDay(activity.date)]) }}
                    </span>
                </div>
                <article class="activities-timeline-item">
                    <div class="activities-timeline-item__line-wrap">
                        <span
                                class="activities-timeline-item__icon-wrap"
                                :class="'activities-timeline-item__icon-wrap--' + getActionIconType(activity.action)"
                                aria-hidden="true">
                            <i
                                    :class="[
                                        'tainacan-icon',
                                        'tainacan-icon-1em',
                                        'activities-timeline-item__icon',
                                        getActionIconClass(activity.action)
                                    ]" />
                        </span>
                    </div>
                    <div class="activities-timeline-item__content">
                        <p
                                v-if="!filterByItemMetadatum"
                                v-tooltip="{
                                    delay: { show: 500, hide: 300 },
                                    content: activity.title,
                                    autoHide: false,
                                    popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                    placement: 'auto-start'
                                }"
                                class="activities-timeline-item__title column-main-content">
                            {{ activity.title }}
                        </p>
                        <div
                                v-else
                                class="activities-timeline-item__diff-content">
                            <div 
                                    v-for="(attributeValue, attributeName, index) in activity.new_value"
                                    :key="index">
                                <div v-if="activity.action == 'update'">

                                    <div v-if="attributeName == 'metadata_type_options'">
                                        <p 
                                                v-for="(innerValue, innerName, innerIndex) of attributeValue"
                                                :key="innerIndex">
                                            <strong>{{ innerName + ': ' }}</strong>{{ innerValue ? innerValue : infoEmpty }}
                                            <br>
                                        </p>
                                    </div>

                                    <div v-else-if="attributeName == 'header_image_id'">
                                        <p class="log-diff-content log-diff-content--after">
                                            {{ attributeValue ? attributeValue : infoEmpty }}
                                            <br>
                                            <img 
                                                    v-if="activity.object && activity.object.header_image"
                                                    style="max-width: 160px;"
                                                    :alt="$i18n.get('label_header_image')"
                                                    :src="activity.object.header_image">
                                        </p>
                                    </div>

                                    <p
                                            v-else
                                            v-html="(!attributeValue || (attributeValue instanceof Array && !attributeValue.length)) ? infoEmpty : (attributeValue instanceof Array ? attributeValue.join(`<span class='multivalue-separator'>|</span>`) : attributeValue)" />
                                </div>
                            </div>
                            <p
                                    v-if="activity.action == 'update-metadata-value'"
                                    v-html="!activity.new_value ? infoEmpty : (activity.new_value instanceof Array ? activity.new_value.join(`<span class='multivalue-separator'>|</span>`) : activity.new_value)" />
                        </div>
                        <div class="activities-timeline-item__meta">
                            <span
                                    v-tooltip="{
                                        delay: { show: 500, hide: 300 },
                                        content: activity.user_name,
                                        autoHide: false,
                                        popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                        placement: 'auto-start'
                                    }"
                                    class="activities-timeline-item__meta-item">
                                <i
                                        class="tainacan-icon tainacan-icon-userfill activities-timeline-item__meta-icon"
                                        aria-hidden="true" />
                                <span v-html="activity.user_name" />
                            </span>
                            <span
                                    v-tooltip="{
                                        delay: { show: 500, hide: 300 },
                                        content: formatActivityDate(activity.date),
                                        autoHide: false,
                                        popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                        placement: 'auto-start'
                                    }"
                                    class="activities-timeline-item__meta-item">
                                <i
                                        class="tainacan-icon tainacan-icon-waiting activities-timeline-item__meta-icon"
                                        aria-hidden="true" />
                                <span>{{ formatActivityTime(activity.date) }}</span>
                            </span>
                            <span
                                    v-if="!isItemLevel && getRelatedToLink(activity)"
                                    class="activities-timeline-item__meta-item activities-timeline-item__meta-item--link"
                                    v-html="getRelatedToLink(activity)" />
                            <span
                                    v-if="canViewChanges(activity)"
                                    class="activities-timeline-item__meta-item">
                                <button
                                        type="button"
                                        class="activities-timeline-item__view-changes link-style"
                                        :aria-label="$i18n.get('label_view_changes') + ': ' + activity.title"
                                        @click.stop="openActivityDetailsModal(activity)">
                                    <i
                                            class="tainacan-icon tainacan-icon-see activities-timeline-item__meta-icon"
                                            aria-hidden="true" />
                                    {{ $i18n.get('label_view_changes') }}
                                </button>
                            </span>
                        </div>
                    </div>
                </article>
            </template>
        </div>

        <!-- Empty state -->
        <div
                v-if="(totalActivities <= 0 || !totalActivities) && !isLoading"
                class="activities-timeline-empty">
            <section class="section">
                <div class="content has-text-dark has-text-centered">
                    <p>
                        <span
                                aria-hidden="true"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-30px tainacan-icon-activities" />
                        </span>
                    </p>
                    <p>{{ $i18n.get('info_no_activities') }}</p>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
    import moment from 'moment';
    import ActivityDetailsModal from '../modals/activity-details-modal.vue';

    export default {
        name: 'ActivitiesList',
        props: {
            isLoading: false,
            totalActivities: 0,
            page: 1,
            activitiesPerPage: 12,
            activities: Array,
            isItemLevel: false,
            filterByItemMetadatum: false
        },
        data() {
            return {
                infoEmpty: this.$i18n.get('info_empty'),
                dateFormat: '',
                dayFormat: '',
                timeFormat: ''
            };
        },
        created() {
            const locale = navigator.language;
            moment.locale(locale);
            const localeData = moment.localeData();
            this.dateFormat = localeData.longDateFormat('LLL');
            this.dayFormat = localeData.longDateFormat('LL');
            this.timeFormat = localeData.longDateFormat('LT');
        },
        methods: {
            isEditionAction(activity) {
                const action = activity.action;
                const editionActions = [
                    'update-metadata-value',
                    'update',
                    'update-metadata-order',
                    'update-filters-order',
                    'update-document',
                    'update-thumbnail'
                ];
                return action && editionActions.includes(action);
            },
            hasActivityChanges(activity) {
                const oldVal = activity.old_value;
                const newVal = activity.new_value;
                if (oldVal === undefined && newVal === undefined) return false;
                if (oldVal === null && newVal === null) return false;
                try {
                    return JSON.stringify(oldVal) !== JSON.stringify(newVal);
                } catch {
                    return oldVal !== newVal;
                }
            },
            canViewChanges(activity) {
                return this.isEditionAction(activity) && this.hasActivityChanges(activity);
            },
            formatActivityDate(date) {
                if (!date) return this.$i18n.get('info_unknown_date');
                const formatted = moment(date).format(this.dateFormat);
                return formatted !== 'Invalid date' ? formatted : this.$i18n.get('info_unknown_date');
            },
            formatActivityDay(date) {
                if (!date) return this.$i18n.get('info_unknown_date');
                const formatted = moment(date).format(this.dayFormat);
                return formatted !== 'Invalid date' ? formatted : this.$i18n.get('info_unknown_date');
            },
            formatActivityTime(date) {
                if (!date) return this.$i18n.get('info_unknown_date');
                const formatted = moment(date).format(this.timeFormat);
                return formatted !== 'Invalid date' ? formatted : this.$i18n.get('info_unknown_date');
            },
            isNewDay(activity, index) {
                if (index === 0) return true;
                const prev = this.activities[index - 1];
                if (!prev || !prev.date || !activity.date) return false;
                return !moment(activity.date).isSame(moment(prev.date), 'day');
            },
            openActivityDetailsModal(activity) {
                const modalTrigger = this.$modalFocusA11y.captureTrigger();
                this.$buefy.modal.open({
                    component: ActivityDetailsModal,
                    props: {
                        activityId: activity.id,
                    },
                    events: {
                        beforeClose: () => this.$modalFocusA11y.restoreFocus(modalTrigger, this)
                    },
                    width: 840,
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    canCancel: ['escape', 'outside']
                });
            },
            getActionIconType(action) {
                if (!action) return 'edit';
                if (['create', 'new-attachment'].includes(action)) return 'add';
                if (['trash', 'delete', 'delete-attachment'].includes(action)) return 'delete';
                return 'edit';
            },
            getActionIconClass(action) {
                const type = this.getActionIconType(action);
                if (type === 'add') return 'tainacan-icon-add';
                if (type === 'delete') return 'tainacan-icon-delete';
                return 'tainacan-icon-edit';
            },
            getRelatedToLink(activity) {
                const type = activity.object_type;
                const objectId = activity.object_id;
                const collectionId = activity.collection_id;
                const itemId = activity.item_id;
                const base = this.$routerHelper.getAbsoluteAdminPath();

                if (!type || !objectId) return '';

                let href;
                let labelKey;
                let iconClass;

                switch (type) {
                    case 'Tainacan\\Entities\\Collection':
                        href = base + this.$routerHelper.getCollectionPath(objectId);
                        labelKey = 'label_go_to_collection';
                        iconClass = 'tainacan-icon-collections';
                        break;
                    case 'Tainacan\\Entities\\Taxonomy':
                        href = base + this.$routerHelper.getTaxonomyPath(objectId);
                        labelKey = 'label_go_to_taxonomy';
                        iconClass = 'tainacan-icon-taxonomies';
                        break;
                    case 'Tainacan\\Entities\\Metadatum':
                        href = base + (collectionId === 'default' || !collectionId
                            ? this.$routerHelper.getMetadataEditPath(objectId)
                            : this.$routerHelper.getCollectionMetadataEditPath(collectionId, objectId));
                        labelKey = 'label_go_to_metadatum';
                        iconClass = 'tainacan-icon-metadata';
                        break;
                    case 'Tainacan\\Entities\\Filter':
                        href = base + (collectionId === 'default' || !collectionId
                            ? this.$routerHelper.getFilterEditPath(objectId)
                            : this.$routerHelper.getCollectionFilterEditPath(collectionId, objectId));
                        labelKey = 'label_go_to_filter';
                        iconClass = 'tainacan-icon-filters';
                        break;
                    case 'Tainacan\\Entities\\Term':
                        return '';
                    case 'Tainacan\\Entities\\Item':
                        if (collectionId != null && itemId != null) {
                            href = base + this.$routerHelper.getItemEditPath(collectionId, itemId);
                        } else if (collectionId != null && objectId != null) {
                            href = base + this.$routerHelper.getItemEditPath(collectionId, objectId);
                        } else {
                            return '';
                        }
                        labelKey = 'label_go_to_item';
                        iconClass = 'tainacan-icon-items';
                        break;
                    case 'Tainacan\\Entities\\Item_Metadata_Entity':
                        if (collectionId != null && itemId != null) {
                            href = base + this.$routerHelper.getItemEditPath(collectionId, itemId);
                        } else {
                            return '';
                        }
                        labelKey = 'label_go_to_item';
                        iconClass = 'tainacan-icon-items';
                        break;
                    default:
                        return '';
                }

                const text = this.$i18n.get(labelKey);
                return `<a href="${href}" class="activities-timeline-item__related-link" target="_blank" rel="noopener noreferrer"><i class="tainacan-icon ${iconClass} activities-timeline-item__meta-icon" aria-hidden="true"></i> ${text} ↗</a>`;
            },
        }
    }
</script>

<style scoped lang="scss">
    .activities-timeline-container {
        padding: 0 var(--tainacan-one-column);
        position: relative;
    }

    .activities-timeline {
        list-style: none;
        padding: 0;
        margin: 0;
        animation-name: appear;
        animation-duration: 0.5s;
    }

    .activities-timeline-day-separator {
        display: flex;
        align-items: center;
        padding: 0;
        margin-top: 0.5em;
        min-height: 2.25rem;
        position: sticky;
        top: calc(var(--tainacan-container-padding) + var(--tainacan-button-min-height, 2.571em) + 3.75em);
        background-color: var(--tainacan-gray1);
        border-radius: var(--tainacan-item-border-radius, 4px);
        z-index: 3;

        &__line-wrap {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 40px;
            margin-inline-end: 12px;
            min-height: 2.25rem;

            &::after {
                content: '';
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                width: 2px;
                background-color: var(--tainacan-input-border-color);
                border-radius: 1px;
            }

            &::after {
                bottom: 0;
                height: calc(50% - 6px);
            }
        }

        &__dot {
            position: relative;
            z-index: 1;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
            background-color: var(--tainacan-input-border-color);
        }

        &__label {
            font-size: 0.75em;
            line-height: 1.25;
            color: var(--tainacan-info-color);
            font-style: italic;
        }
    }

    .activities-timeline-item {
        display: flex;
        align-items: stretch;
        min-height: 60px;
        border-radius: var(--tainacan-item-border-radius, 0px);
        background-color: var(--tainacan-item-background-color);
        transition: background-color 0.15s ease;

        &__line-wrap {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
            width: 40px;
            margin-inline-end: 12px;

            &::before,
            &::after {
                content: '';
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                width: 2px;
                background-color: var(--tainacan-input-border-color);
                border-radius: 1px;
            }

            &::before {
                top: 0;
                height: 0.5rem;
            }

            &::after {
                bottom: 0;
                height: calc(100% - 0.5rem - 1.625rem);
            }
        }

        /* Hide line below when item is last or next sibling is day separator */
        &:last-child &__line-wrap::after,
        &:has(+ .activities-timeline-day-separator) &__line-wrap::after {
            display: none;
        }

        &__icon-wrap {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 0.5rem;
            width: 1.625rem;
            height: 1.625rem;
            border-radius: 50%;
            flex-shrink: 0;
            color: var(--tainacan-secondary);

            &--edit {
                background-color: var(--tainacan-primary, var(--tainacan-info-color));
            }

            &--add {
                color: var(--tainacan-green2);
                background-color: var(--tainacan-green1);
            }

            &--delete {
                color: var(--tainacan-red2);
                background-color: var(--tainacan-red1);
            }
        }

        &__icon {
            color: inherit;
        }

        &__content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0.875rem 1rem 0.875rem 0;
            border-radius: var(--tainacan-item-border-radius, 0px);

            .activities-timeline-item:not(:last-child):not(:has(+ .activities-timeline-day-separator)) & {
                border-bottom: 1px solid var(--tainacan-lists-separator-color, var(--tainacan-item-hover-background-color));
            }
        }

        &__title {
            font-size: 0.875em !important;
            line-height: 1.125em;
            color: var(--tainacan-input-color) !important;
            margin: 0 0 0.25rem 0 !important;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            max-height: 2.5em;
        }

        &__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem 1rem;
            align-items: center;
        }

        &__meta-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: var(--tainacan-info-color);
            font-size: 0.75em;
            line-height: 1.03125em;
        }

        &__meta-icon {
            flex-shrink: 0;
            font-size: 1em;
            opacity: 0.85;
        }

        &__meta-item--link {
            display: inline-flex;
            align-items: center;
            gap: 4px;

            :deep(.activities-timeline-item__related-link) {
                color: var(--tainacan-info-color);
                text-decoration: none;

                &:hover {
                    color: var(--tainacan-secondary);
                    text-decoration: underline;
                }
            }

            :deep(.icon) {
                vertical-align: middle;
            }
        }

        &__view-changes {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 0;
            margin: 0;
            border: none;
            background: none;
            font-size: inherit;
            line-height: inherit;
            color: var(--tainacan-info-color);
            text-decoration: none;
            cursor: pointer;

            &:hover {
                color: var(--tainacan-secondary);
                text-decoration: underline;
            }

            &:focus-visible {
                outline: 2px solid var(--tainacan-secondary);
                outline-offset: 2px;
            }
        }

        &__diff-content {
            word-break: break-word;
            border-radius: var(--tainacan-input-border-radius);
            padding: 6px;
            margin-bottom: 0.5rem;
            max-height: 40vh;
            overflow-y: auto;
            background-color: var(--tainacan-gray0);
        }
    }

    .column-main-content {
        min-width: 0;
    }

    .activities-timeline-empty {
        .section {
            padding: var(--tainacan-container-padding) 0;
        }
    }
</style>
