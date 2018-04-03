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
                ref="eventsTable"
                :data="events"
                :checked-rows.sync="selectedEvents"
                checkable
                :loading="isLoading"
                hoverable
                striped
                selectable
                backend-sorting>
            <template slot-scope="props">

                <b-table-column
                        tabindex="0"
                        :label="$i18n.get('label_name')"
                        :aria-label="$i18n.get('label_name')"
                        field="props.row.title">
                    <router-link
                            class="clickable-row"
                            tag="span"
                            :to="{path: $routerHelper.getEventPath(props.row.id)}">
                        {{ props.row.title }}
                    </router-link>
                </b-table-column>

                <b-table-column
                        tabindex="0"
                        :aria-label="$i18n.get('label_description')"
                        :label="$i18n.get('label_description')"
                        property="description"
                        show-overflow-tooltip
                        field="props.row.description">
                    <router-link
                            class="clickable-row"
                            tag="span"
                            :to="{path: $routerHelper.getEventPath(props.row.id)}">
                        {{ props.row.description }}
                    </router-link>
                </b-table-column>

                <b-table-column
                        class="row-creation"
                        tabindex="0"
                        :aria-label="$i18n.get('label_creation') + ': ' + props.row.creation"
                        :label="$i18n.get('label_creation')"
                        property="by"
                        show-overflow-tooltip
                        field="props.row.by">
                    <router-link
                            class="clickable-row"
                            v-html="props.row.by"
                            tag="span"
                            :to="{path: $routerHelper.getEventPath(props.row.id)}"/>
                </b-table-column>

                <b-table-column
                        tabindex="0"
                        :label="$i18n.get('label_actions')"
                        width="78"
                        :aria-label="$i18n.get('label_actions')">

                    <a
                            id="button-approve"
                            :aria-label="$i18n.get('approve_item')"
                            @click.prevent.stop="approve(props.row.id)">
                        <b-icon
                            icon="check" />
                    </a>

                    <a
                            id="button-not-approve"
                            :aria-label="$i18n.get('not_approve_item')"
                            @click.prevent.stop="notApprove(props.row.id)">
                        <b-icon
                            type="is-danger"
                            icon="close-circle-outline" />
                    </a>


                </b-table-column>
            </template>

        </b-table>

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
            ...mapActions('events', [
                'approve',
                'notApprove'
            ])
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
