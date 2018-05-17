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
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_status') }}</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr     
                            :key="index"
                            v-for="(event, index) of events">
                        <!-- Name -->
                        <td 
                                class="column-default-width"
                                @click="goToEventPage(event.id)"
                                :label="$i18n.get('label_event_title')" 
                                :aria-label="$i18n.get('label_event_title') + ': ' + event.title">
                            <p>{{ event.title }}</p>
                        </td>
                        <!-- Who and When -->
                        <td
                                class="table-creation column-default-width" 
                                @click="goToEventPage(event.id)"
                                :label="$i18n.get('label_who_when')" 
                                :aria-label="$i18n.get('label_who_when') + ': ' + event.by">
                            <p v-html="event.by" />
                        </td>
                        <!-- Status -->
                        <td
                                @click="goToEventPage(event.id)"
                                class="status-cell" 
                                :label="$i18n.get('label_status')" 
                                :aria-label="$i18n.get('label_status') + ': ' + event.status">
                            <p>
                                <a
                                        v-if="event.status === 'pending'"
                                        id="button-approve"
                                        :aria-label="$i18n.get('approve_item')"
                                        @click.prevent.stop="approveEvent(event.id)">
                                    <b-icon
                                        icon="check" />
                                </a>

                                <a
                                        v-if="event.status === 'pending'"
                                        id="button-not-approve"
                                        class="delete"
                                        :aria-label="$i18n.get('not_approve_item')"
                                        @click.prevent.stop="notApproveEvent(event.id)" />

                                <small v-if="event.status !== 'pending'">{{ $i18n.get('label_approved') }}</small>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
<!--
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
-->
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
            },
            goToEventPage(eventId) {
                this.$router.push(this.$routerHelper.getEventPath(eventId));
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .table {
        width: 100%;

        .status-cell {
            width: 78px;
        }
        
        .checkbox-cell {
            width: 44px;
            height: 58px;
            padding: 0;
            position: absolute !important;
            left: 82px;
            visibility: hidden;
            display: flex;
            justify-content: space-around;
            z-index: 9;

            &::before {
                box-shadow: inset 53px 0 10px -12px #222;
                content: " ";
                width: 64px;
                height: 100%;
                position: absolute;
                left: 0;
            }

            .checkbox {  
                border-radius: 0px;
                background-color: white;
                padding: 10px 10px 10px 14px;
                width: 100%;
                height: 100%; 
            }
            &.is-selecting {
                visibility: visible; 
            }
        }
        // Only to be used in case we can implement Column resizing
        // th:not(:last-child) {
        //     border-right: 1px solid $tainacan-input-background !important;
        // }

        .thumbnail-cell {
            width: 58px;
            padding-left: 54px;
        }
  
        tbody {
            tr {
                cursor: pointer;
                background-color: transparent;

                &.selected-row { 
                    background-color: $primary-lighter !important; 
                    .checkbox-cell .checkbox, .actions-cell .actions-container {
                        background-color: $primary-lighter !important;
                    }
                }
                td {
                    height: 58px;
                    max-height: 58px;
                    padding: 10px;
                    vertical-align: middle;
                    line-height: 12px;
                    p { 
                        font-size: 14px; 
                        margin: 0px;
                    }
                    
                }
                td.column-default-width{
                    max-width: 350px;
                    p, {
                        text-overflow: ellipsis;
                        overflow-x: hidden;
                        white-space: nowrap;
                    }
                }
                img.table-thumb {
                    max-height: 38px !important;
                    border-radius: 3px;
                }

                td.table-creation p {
                    color: $gray-light;
                    font-size: 11px;
                    line-height: 1.5;
                }

                td.actions-cell {
                    padding: 0px;
                    visibility: hidden;
                    position: absolute;
                    right: 82px;
                    display: none;
                    
                    .actions-container {
                        position: relative;
                        padding: 10px;
                        height: 100%;
                        z-index: 9;
                        background-color: $tainacan-input-background; 
                     }

                    a .icon {
                        margin: 8px;
                    }

                     &::before {
                        box-shadow: inset -113px 0 17px -17px #222;
                        content: " ";
                        width: 125px;
                        height: 100%;
                        position: absolute;
                        right: 0;
                        top: 0;
                    }
                }

                &:hover {
                    background-color: $tainacan-input-background;
                    cursor: pointer;

                    .checkbox-cell {
                        visibility: visible; 
                        .checkbox { background-color: $tainacan-input-background; }
                    }
                    .actions-cell {
                        visibility: visible;
                        display: block;
                    }
                }
            }
        }
    }
   
</style>
