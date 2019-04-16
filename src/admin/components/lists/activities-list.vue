<template>
    <div class="table-container">
        <div class="table-wrapper">
            <table class="tainacan-table is-narrow">
                <thead>
                <tr>
                    <!-- Title -->
                    <th>
                        <div class="th-wrap">{{ $i18n.get('label_activity_title') }}</div>
                    </th>
                    <!-- Created by -->
                    <th>
                        <div class="th-wrap">{{ $i18n.get('label_created_by') }}</div>
                    </th>
                    <!-- Activity date -->
                    <th>
                        <div class="th-wrap">{{ $i18n.get('label_activity_date') }}</div>
                    </th>
                    <!--&lt;!&ndash; Approbation &ndash;&gt;-->
                    <!--<th>-->
                        <!--<div class="th-wrap">{{ $i18n.get('label_approbation') }}</div>-->
                    <!--</th>-->
                </tr>
                </thead>
                <tbody>
                <tr
                        :key="index"
                        v-for="(activity, index) of activities">
                    <!-- Name -->
                    <td
                            class="column-default-width column-main-content"
                            @click="openActivityDetailsModal(activity)"
                            :label="$i18n.get('label_activity_title')"
                            :aria-label="$i18n.get('label_activity_title') + ': ' + activity.title">
                        <p
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: activity.title,
                                    autoHide: false,
                                    classes: ['tooltip', 'repository-tooltip'],
                                    placement: 'auto-start'
                                }">
                            {{ activity.title }}
                        </p>
                </td>
                    <!-- User -->
                    <td
                            class="table-creation column-small-width"
                            @click="openActivityDetailsModal(activity)"
                            :label="$i18n.get('label_created_by')"
                            :aria-label="$i18n.get('label_created_by') + ': ' + activity.user_name">
                        <p
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: activity.user_name,
                                    autoHide: false,
                                    classes: ['tooltip', 'repository-tooltip'],
                                    placement: 'auto-start'
                                }"
                                v-html="activity.user_name"/>
                    </td>
                    <!-- Activity Date -->
                    <td
                            class="table-creation column-small-width"
                            @click="openActivityDetailsModal(activity)"
                            :label="$i18n.get('label_activity_date')"
                            :aria-label="$i18n.get('label_activity_date') + ': ' + activity.log_date">
                        <p
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: activity.log_date,
                                    autoHide: false,
                                    classes: ['tooltip', 'repository-tooltip'],
                                    placement: 'auto-start'
                                }"
                                v-html="activity.log_date"/>
                    </td>
                    <!-- Approbation -->
                    <!--<td-->
                            <!--class="status-cell"-->
                            <!--:label="$i18n.get('label_approbation')"-->
                            <!--:aria-label="$i18n.get('label_approbation') + ': ' + activity.status">-->
                        <!--<p>-->
                            <!--<three-state-toggle-button-->
                                    <!--:other-prop="activity"-->
                                    <!--:parent="getThis()"-->
                                    <!--:events="stateEvents"-->
                                    <!--:state=" activity.status === 'publish' ?-->
                                     <!--'yes_3tgbtn' : (activity.status === 'pending' ? 'neutral_3tgbtn' : 'no_3tgbtn')"/>-->

                            <!--&lt;!&ndash;<a v-if="activity.status !== 'pending'">&ndash;&gt;-->
                                <!--&lt;!&ndash;<b-icon&ndash;&gt;-->
                                        <!--&lt;!&ndash;type="is-blue5"&ndash;&gt;-->
                                        <!--&lt;!&ndash;custom-class="mdi-flip-h"&ndash;&gt;-->
                                        <!--&lt;!&ndash;icon="share"/>&ndash;&gt;-->
                            <!--&lt;!&ndash;</a>&ndash;&gt;-->
                        <!--</p>-->
                    <!--</td>-->
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty state image -->
        <div v-if="(totalActivities <= 0 || !totalActivities) && !isLoading">
            <section class="section">
                <div class="content has-text-grey has-text-centered">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-activities"/>
                    </span>
                    <p>{{ $i18n.get('info_no_activities') }}</p>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
    import {mapActions} from 'vuex';

    import ActivityDetailsModal from '../other/activity/activity-details-modal.vue';
    import ThreeStateToggleButton from '../other/three-state-toggle-button.vue';

    export default {
        name: 'ActivitiesList',
        data() {
            return {
                selectedActivities: [],
                stateEvents: {
                    yes_3tgbtn: (a) => this.approveActivity(a),
                    //neutral_3tgbtn: (otherProp) => this.approveActivity(otherProp),
                    no_3tgbtn: (a) => this.notApproveActivity(a)
                }
            }
        },
        props: {
            isLoading: false,
            totalActivities: 0,
            page: 1,
            activitiesPerPage: 12,
            activities: Array
        },
        methods: {
            ...mapActions('activity', [
                'approve',
                'notApprove'
            ]),
            getThis(){
                return this;
            },
            approveActivity(activity) {
                this.approve(activity.id)
                    .then(data => {
                        this.$console.info('approved!', data);
                    })
                    .catch(error => this.$console.error(error));
            },
            notApproveActivity(activity) {
                this.notApprove(activity.id);
            },
            openActivityDetailsModal(activity) {
                this.$modal.open({
                    parent: this,
                    component: ActivityDetailsModal,
                    props: {
                        activity: activity,
                    },
                    events: {
                        approveActivity: (activityId) => this.approveActivity(activityId),
                        notApproveActivity: (activityId) => this.notApproveActivity(activityId)
                    }
                });
            },
        },
        components: {
            ThreeStateToggleButton
        }
    }
</script>

<style scoped>
    .activities-icon {
        height: 24px;
        width: 24px;
    }
</style>
