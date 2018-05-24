<template>
    <div class="table-container">
         <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <!-- Title -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_event_title') }}</div>
                        </th>
                        <!-- Who and When -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_who_when') }}</div>
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
                            <p>{{ event.title }}</p>
                        </td>
                        <!-- Who and When -->
                        <td
                                class="table-creation column-small-width" 
                                @click="goToEventPage(event.id)"
                                :label="$i18n.get('label_who_when')" 
                                :aria-label="$i18n.get('label_who_when') + ': ' + event.by">
                            <p v-html="event.by" />
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
                        <b-icon
                                icon="inbox"
                                size="is-large"/>
                    </p>
                    <p>{{ $i18n.get('info_no_events') }}</p>
                </div>
            </section>
        </div> 
    </div>
</template>

<script>
    // import { mapActions } from 'vuex'

    export default {
        name: 'EventsList',
        data(){
            return {
                selectedEvents: []
            }
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

<style>

</style>
