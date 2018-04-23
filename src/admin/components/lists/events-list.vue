<template>
    <div>
        <!--<b-field-->
                <!--grouped-->
                <!--group-multiline>-->
            <!--<button-->
                    <!--v-if="selectedEvents.length > 0"-->
                    <!--class="button field is-danger"-->
                    <!--@click="deleteSelectedEvents()"><span>{{ $i18n.get('instruction_delete_selected_events') }} </span><b-icon icon="delete"/></button>-->
        <!--</b-field>-->

        <b-table
                v-if="totalEvents > 0"
                ref="eventsTable"
                :data="events"
                :checked-rows.sync="selectedEvents"
                :loading="isLoading"
                hoverable
                striped
                backend-sorting>
            <template slot-scope="props">

                <b-table-column
                        tabindex="0"
                        :label="$i18n.get('label_event_title')"
                        :aria-label="$i18n.get('label_event_title')"
                        field="props.row.title">
                    <router-link
                            class="clickable-row"
                            tag="span"
                            router-link-active
                            :active="props.row.log_diffs.length > 0"
                            :to="{path: $routerHelper.getEventPath(props.row.id)}">
                        {{ props.row.title }}
                    </router-link>
                </b-table-column>

                <b-table-column
                        class="row-creation"
                        tabindex="0"
                        :aria-label="$i18n.get('label_who_when') + ': ' + props.row.creation"
                        :label="$i18n.get('label_who_when')"
                        property="by"
                        show-overflow-tooltip
                        field="props.row.by">
                    <router-link
                            class="clickable-row"
                            v-html="props.row.by"
                            tag="span"
                            :active="props.row.log_diffs.length > 0"
                            :to="{path: $routerHelper.getEventPath(props.row.id)}"/>
                </b-table-column>

                <b-table-column
                        tabindex="0"
                        :label="$i18n.get('label_actions')"
                        width="78"
                        :aria-label="$i18n.get('label_actions')">

                    <a
                            v-if="props.row.status === 'pending'"
                            id="button-approve"
                            :aria-label="$i18n.get('approve_item')"
                            @click.prevent.stop="approveEvent(props.row.id)">
                        <b-icon
                            icon="check" />
                    </a>

                    <a
                            v-if="props.row.status === 'pending'"
                            id="button-not-approve"
                            class="delete"
                            :aria-label="$i18n.get('not_approve_item')"
                            @click.prevent.stop="notApproveEvent(props.row.id)" />

                    <small v-if="props.row.status !== 'pending'"> Approved </small>

                </b-table-column>
            </template>

        </b-table>

        <section
                v-if="totalEvents <= 0 || !totalEvents"
                class="hero is-bold is-large">
            <div class="hero-body">
                <div class="container has-text-centered">
                    <h1 class="title">
                        <b-icon icon="notifications-none" />
                        {{ this.$i18n.get('info_no_events') }}
                    </h1>
                </div>
            </div>
        </section>

    </div>
</template>

<script>
    import { mapActions } from 'vuex'

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
            ...mapActions('event', [
                'approve',
                'notApprove'
            ]),
            approveEvent(eventId){
               this.approve(eventId);
            },
            notApproveEvent(eventId){
                this.notApprove(eventId);
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .table-thumb {
        max-height: 38px !important;
        vertical-align: middle !important;
    }

    .row-creation span {
        color: $gray-light;
        font-size: 0.75em;
        line-height: 1.5
    }

    .clickable-row{ cursor: pointer !important; }

</style>
