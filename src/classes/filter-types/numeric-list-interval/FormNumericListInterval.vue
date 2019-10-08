<template>
    <b-field :addons="false">
        <label class="label is-inline">
            {{ $i18n.getHelperTitle('tainacan-filter-numeric-list-interval', 'intervals') }}<span>&nbsp;</span>
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-filter-numeric-list-interval', 'intervals')"
                    :message="$i18n.getHelperMessage('tainacan-filter-numeric-list-interval', 'intervals')"/>
        </label>

        <div>
            <b-field>
                <b-checkbox v-model="showIntervalOnTag">
                    {{ $i18n.get('info_show_interval_on_tag') }}
                </b-checkbox>
            </b-field>
        </div>

        <div v-if="intervals.length == 0" >
            <br>
            <a
                    role="button"
                    @click="addInterval()"
                    class="is-inline add-link">
                <span class="icon is-small">
                    <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                </span>
                &nbsp;{{ $i18n.get('add_value') }}
            </a>
        </div>
        <transition-group name="filter-item">
        <div
                class="options-input"
                v-for="(interval, index) of intervals"
                :key="index">
            <b-field>
                <b-input
                        expanded="true"
                        :placeholder="$i18n.get('label')"
                        @input="onUpdate(interval)"
                        v-model="interval.label" />
            </b-field>
            <b-field>
                <b-input
                        type="number"
                        step="0.01"
                        :placeholder="$i18n.get('info_initial_value')"
                        @input="onUpdate(interval)"
                        v-model="interval.from" />
                <b-input
                        type="number"
                        step="0.01"
                        :placeholder="$i18n.get('info_final_value')"
                        @input="onUpdate(interval)"
                        v-model="interval.to" />
            </b-field>
            <p class="control">
                <a
                        role="button"
                        @click="addInterval(index)"
                        class="is-inline add-link"
                        :title="$i18n.get('add_value')">
                    <span class="icon is-small">
                        <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                    </span>
                        &nbsp;{{ $i18n.get('add_value') }}
                </a>
            </p>
            <p class="control">
                <a
                        role="button"
                        @click="removeInterval(index)"
                        class="is-inline add-link"
                        :title="$i18n.get('remove_value')">
                    <span class="icon is-small">
                        <i class="tainacan-icon has-text-secondary tainacan-icon-repprovedcircle"/>
                    </span>
                    &nbsp;{{ $i18n.get('remove_value') }}
                </a>
            </p>
        </div>
        </transition-group>
    </b-field>
</template>

<script>

    export default {
        props: {
            filter: Object,
            value: [String, Number, Array],
            id: '',
            disabled: false,
        },
        data() {
            return {
                showIntervalOnTag: true,
                intervals: [],
                isValid: true,
            }
        },
        methods: {
            onUpdate(interval) {
                if (interval.to == null || interval.from == null ||
                    interval.to == "" || interval.from == "" ||
                    Number(interval.to) < Number(interval.from)) {
                    if(this.isValid) {
                        this.isValid = false;
                        this.error_message()
                    }
                } else {
                    this.isValid = true;
                    this.$emit('input', {
                        intervals: this.intervals,
                        showIntervalOnTag: this.showIntervalOnTag
                    });
                }
            },
            error_message() {
                this.$buefy.toast.open({
                    duration: 3000,
                    message: this.$i18n.get('info_error_first_value_greater'),
                    position: 'is-bottom',
                    type: 'is-danger'
                })
            },
            removeInterval(index) {
                this.intervals.splice(index, 1);
            },
            addInterval(index) {
                if (index != undefined) {
                    this.intervals.splice(index + 1, 0, {
                        label: '',
                        to: null,
                        from: null
                    })
                } else {
                    this.intervals.push({
                        label: '',
                        to: null,
                        from: null
                    });
                }
            }
        },
        created() {
            this.intervals = this.value && this.value.intervals ? this.value.intervals : [];
            this.showIntervalOnTag = this.value && this.value.showIntervalOnTag != undefined ? this.value.showIntervalOnTag : true;
        }
    }
</script>

<style scoped lang="scss">
    @import '../../scss/_variables.scss';

    .options-input {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;

        .field:first-child {
            width: 100%;
            margin-top: 1rem;
            margin-bottom: -1px;
        }
        .field.has-addons {
            margin-bottom: 0.125rem;
        }
    }

</style>
