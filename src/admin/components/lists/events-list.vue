<template>
    <div class="table-container">
         <div class="table-wrapper">
            <table class="tainacan-table is-narrow">
                <thead>
                    <tr>
                        <!-- Title -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_event_title') }}</div>
                        </th>
                        <!-- Created by -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_created_by') }}</div>
                        </th>
                        <!-- Event date -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_event_date') }}</div>
                        </th>
                        <!-- Status -->
                        <!--<th>-->
                            <!--<div class="th-wrap">{{ $i18n.get('label_status') }}</div>-->
                        <!--</th>-->
                    </tr>
                </thead>
                <tbody>
                    <tr     
                            :key="index"
                            v-for="(event, index) of events">
                        <!-- Name -->
                        <td 
                                class="column-default-width column-main-content"
                                @click="goToEventPage(event.id)"
                                :label="$i18n.get('label_event_title')" 
                                :aria-label="$i18n.get('label_event_title') + ': ' + event.title">
                            <p
                                    v-tooltip="{
                                        content: event.title,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }">{{ event.title }}</p>
                        </td>
                        <!-- User -->
                        <td
                                class="table-creation column-small-width" 
                                @click="goToEventPage(event.id)"
                                :label="$i18n.get('label_created_by')" 
                                :aria-label="$i18n.get('label_created_by') + ': ' + event.user_name">
                            <p 
                                    v-tooltip="{
                                        content: event.user_name,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }" 
                                    v-html="event.user_name" />
                        </td>
                        <!-- Event Date -->
                        <td
                                class="table-creation column-small-width" 
                                @click="goToEventPage(event.id)"
                                :label="$i18n.get('label_event_date')" 
                                :aria-label="$i18n.get('label_event_date') + ': ' + event.log_date">
                            <p 
                                    v-tooltip="{
                                        content: event.log_date,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }" 
                                    v-html="event.log_date" />
                        </td>
                        <!-- Status -->
                        <!--<td-->
                                <!--@click="goToEventPage(event.id)"-->
                                <!--class="status-cell" -->
                                <!--:label="$i18n.get('label_status')" -->
                                <!--:aria-label="$i18n.get('label_status') + ': ' + event.status">-->
                            <!--<p>-->
                                <!--<a-->
                                        <!--v-if="event.status === 'pending'"-->
                                        <!--id="button-approve"-->
                                        <!--:aria-label="$i18n.get('approve_item')"-->
                                        <!--@click.prevent.stop="approveEvent(event.id)">-->
                                    <!--<b-icon-->
                                        <!--icon="check" />-->
                                <!--</a>-->

                                <!--<a-->
                                        <!--v-if="event.status === 'pending'"-->
                                        <!--id="button-not-approve"-->
                                        <!--class="delete"-->
                                        <!--:aria-label="$i18n.get('not_approve_item')"-->
                                        <!--@click.prevent.stop="notApproveEvent(event.id)" />-->

                                <!--<small v-if="event.status !== 'pending'">{{ $i18n.get('label_approved') }}</small>-->
                            <!--</p>-->
                        <!--</td>-->
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty state image -->
        <div v-if="(totalEvents <= 0 || !totalEvents) && !isLoading">
            <section class="section">
                <div class="content has-text-grey has-text-centered">
                    <p>
                        <activities-icon />
                    </p>
                    <p>{{ $i18n.get('info_no_events') }}</p>
                </div>
            </section>
        </div> 
    </div>
</template>

<script>
    import ActivitiesIcon from '../other/activities-icon.vue';

    export default {
        name: 'EventsList',
        data(){
            return {
                selectedEvents: []
            }
        },
        components: {
            ActivitiesIcon
        },
        props: {
            isLoading: false,
            totalEvents: 0,
            page: 1,
            eventsPerPage: 12,
            events: Array
        },
        methods: {
            // ...mapActions('event', [
            //     'approve',
            //     'notApprove'
            // ]),
            // approveEvent(eventId){
            //    this.approve(eventId);
            // },
            // notApproveEvent(eventId){
            //     this.notApprove(eventId);
            // },
            goToEventPage(eventId) {
                this.$router.push(this.$routerHelper.getEventPath(eventId));
            }
        }
    }
</script>

<style scoped>
    .activities-icon {
        height: 24px;
        width: 24px;
    }
</style>
