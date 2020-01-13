<template>
    <b-field>
        <div class="radio-wrapper">
            <b-radio-button
                    size="is-small"
                    v-model="state"
                    :native-value="eventsName[1]"
                    type="is-danger">
                <b-icon
                        size="is-small"
                        icon="close"/>
            </b-radio-button>

            <b-radio-button
                    size="is-small"
                    v-model="state"
                    :native-value="eventsName[2]">
                <b-icon
                        size="is-small"
                        icon="exclamation"/>
            </b-radio-button>

            <b-radio-button
                    size="is-small"
                    v-model="state"
                    :native-value="eventsName[0]"
                    type="is-success">
                <b-icon
                        size="is-small"
                        icon="check"/>
            </b-radio-button>
        </div>
    </b-field>
</template>

<script>
    export default {
        name: "ThreeStateToggleButton",
        props: {
            parent: {
                type: Object,
                default: new Object({})
            },
            events: {
                type: Object,
                default: new Object({})
            },
            otherProp: {
                type: Object,
                default: new Object({})
            },
            state: {
                type: String,
                default: 'neutral_3tgbtn'
            },
        },
        methods: {
            yes_3tgbtn() {
                this.parent.$emit(this.eventsName[0], this.otherProp);
            },
            no_3tgbtn() {
                this.parent.$emit(this.eventsName[1], this.otherProp);
            },
            neutral_3tgbtn() {
                this.parent.$emit(this.eventsName[2], this.otherProp);
            }
        },
        data() {
            return {
                eventsName: ['yes_3tgbtn', 'no_3tgbtn', 'neutral_3tgbtn'],
            }
        },
        watch: {
            state(functionName) {
                this[`${functionName}`]();
            }
        },
        created() {
            if (Object.keys(this.events).length && !Object.keys(this.parent._events).length) {
                for (let [event, callback] of Object.entries(this.events)) {
                    if (this.eventsName.includes(event)) {
                        this.parent.$on(event, callback);
                    } else {
                        this.$console.error(
                            `Invalid event name, the event [${event} | ${callback}] not in events list [${this.eventsName.toString()}]`
                        );
                    }
                }
            }
        }
    }
</script>

<style lang="scss">
    .radio-wrapper {
        display: flex;
        border: solid 0.5px lightgray;
        border-radius: 50px;
        height: 20.49px;
        width: 57.5px;
        justify-content: center;

        label.is-small {
            height: 18px !important;
            border: none;
            border-radius: 50px !important;
            width: 18px;
            padding: 0 !important;
        }
    }

    .wp-core-ui p .button {
        vertical-align: top !important;
    }

    .button .icon:first-child:not(:last-child) {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
</style>