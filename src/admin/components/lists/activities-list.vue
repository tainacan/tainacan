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
                    <!-- Status -->
                    <th>
                        <div class="th-wrap">{{ $i18n.get('label_status') }}</div>
                    </th>
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
                                        content: activity.title,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }">{{ activity.title }}</p>
                    </td>
                    <!-- User -->
                    <td
                            class="table-creation column-small-width"
                            @click="openActivityDetailsModal(activity)"
                            :label="$i18n.get('label_created_by')"
                            :aria-label="$i18n.get('label_created_by') + ': ' + activity.user_name">
                        <p
                                v-tooltip="{
                                        content: activity.user_name,
                                        autoHide: false,
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
                                        content: activity.log_date,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                v-html="activity.log_date"/>
                    </td>
                    <!-- Status -->
                    <td
                            class="status-cell"
                            :label="$i18n.get('label_status')"
                            :aria-label="$i18n.get('label_status') + ': ' + activity.status">
                        <p>
                            <three-state-toggle-button
                                    v-if="activity.status === 'pending'"
                                    :other-prop="activity"
                                    :parent="getThis()"
                                    :events="stateEvents"/>
                            <!--<a-->
                            <!--v-if="activity.status === 'pending'"-->
                            <!--id="button-approve"-->
                            <!--:aria-label="$i18n.get('approve_item')"-->
                            <!--@click.prevent.stop="approveActivity(activity.id)">-->
                            <!--<b-icon-->
                            <!--icon="check" />-->
                            <!--</a>-->

                            <!--<a-->
                            <!--v-if="activity.status === 'pending'"-->
                            <!--id="button-not-approve"-->
                            <!--class="delete"-->
                            <!--:aria-label="$i18n.get('not_approve_item')"-->
                            <!--@click.prevent.stop="notApproveActivity(activity.id)" />-->

                            <a v-if="activity.status !== 'pending'">
                                <b-icon
                                        type="is-blue5"
                                        custom-class="mdi-flip-h"
                                        icon="share"/>
                            </a>
                        </p>
                    </td>
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
                console.info('approve');
                this.approve(activity.id)
                    .then(data => {
                        console.info('opa!', data);
                    });
            },
            notApproveActivity(activity) {
                console.info('not approve');
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
