<template>
    <div :class="'subject-dropdown ' + (subjectType == 'channel' ? 'first-selected' : '')">
        <div class="form-group" v-if="useDropdownForTypes">
            <select class="form-control" v-model="subjectType" @change="changeSubjectType()">
                <option value="channel">{{ $t('Channels') }}</option>
                <option value="channelGroup">{{ $t('Channel groups') }}</option>
                <option value="scene">{{ $t('Scenes') }}</option>
                <option value="schedule" v-if="!disableSchedules">{{ $t('Schedules') }}</option>
                <option value="notification" v-if="!disableNotifications">{{ $t('Send notification') }}</option>
                <option value="other">{{ $t('Other') }}</option>
            </select>
        </div>

        <ul class="nav nav-tabs" v-else>
            <li :class="subjectType == 'channel' ? 'active' : ''">
                <a @click="changeSubjectType('channel')">{{ $t('Channels') }}</a>
            </li>
            <li :class="subjectType == 'channelGroup' ? 'active' : ''">
                <a @click="changeSubjectType('channelGroup')">{{ $t('Channel groups') }}</a>
            </li>
            <li :class="subjectType == 'scene' ? 'active' : ''">
                <a @click="changeSubjectType('scene')">{{ $t('Scenes') }}</a>
            </li>
            <li :class="subjectType == 'schedule' ? 'active' : ''" v-if="!disableSchedules">
                <a @click="changeSubjectType('schedule')">{{ $t('Schedules') }}</a>
            </li>
            <li :class="subjectType == 'notification' ? 'active' : ''" v-if="!disableNotifications">
                <a @click="changeSubjectType('notification')">{{ $t('Send notification') }}</a>
            </li>
            <li :class="subjectType == 'other' ? 'active' : ''" v-if="hasOthersSlot">
                <a @click="changeSubjectType('other')">{{ $t('Other') }}</a>
            </li>
        </ul>

        <channels-dropdown v-model="subject"
            v-if="subjectType == 'channel'"
            @input="subjectChanged"
            :dropdownContainer="dropdownContainer"
            :filter="filter"
            :params="channelsDropdownParams"></channels-dropdown>
        <channel-groups-dropdown @input="subjectChanged"
            v-if="subjectType == 'channelGroup'"
            :dropdownContainer="dropdownContainer"
            :filter="filter"
            :params="channelsDropdownParams"
            v-model="subject"></channel-groups-dropdown>
        <scenes-dropdown
            @input="subjectChanged"
            v-if="subjectType == 'scene'"
            :dropdownContainer="dropdownContainer"
            :filter="filter"
            v-model="subject"></scenes-dropdown>
        <schedules-dropdown
            @input="subjectChanged"
            v-if="subjectType == 'schedule'"
            :dropdownContainer="dropdownContainer"
            :filter="filter"
            v-model="subject"></schedules-dropdown>
        <slot name="other"
            :subject="subject"
            :on-input="subjectChanged"
            v-if="subjectType == 'other'"></slot>
    </div>
</template>

<script>
    import ChannelsDropdown from "./channels-dropdown";
    import ChannelGroupsDropdown from "../channel-groups/channel-groups-dropdown";
    import ScenesDropdown from "../scenes/scenes-dropdown";
    import Vue from "vue";
    import SchedulesDropdown from "@/schedules/schedules-dropdown.vue";
    import ActionableSubjectType from "@/common/enums/actionable-subject-type";
    import ChannelFunctionAction from "@/common/enums/channel-function-action";

    export default {
        props: {
            value: Object,
            channelsDropdownParams: String,
            filter: {
                type: Function,
                default: () => true,
            },
            clearOnSelect: Boolean,
            disableSchedules: Boolean,
            disableNotifications: Boolean,
            useDropdownForTypes: Boolean,
            dropdownContainer: String,
        },
        components: {SchedulesDropdown, ScenesDropdown, ChannelGroupsDropdown, ChannelsDropdown},
        data() {
            return {
                subject: undefined,
                subjectType: 'channel'
            };
        },
        mounted() {
            this.updateBasedOnValue();
        },
        methods: {
            changeSubjectType(subjectType = this.subjectType) {
                this.subjectType = subjectType;
                this.subject = undefined;
                if (this.subjectType === ActionableSubjectType.NOTIFICATION) {
                    this.subject = {
                        id: -1,
                        ownSubjectType: ActionableSubjectType.NOTIFICATION,
                        possibleActions: [{id: ChannelFunctionAction.SEND, name: 'SEND', caption: 'Send notification'}],
                    };
                }
                this.subjectChanged(this.subject);
            },
            subjectChanged(subject) {
                if (this.subject != subject) {
                    this.subject = subject;
                }
                this.$emit('input', this.subject);
                if (this.clearOnSelect && this.subject) {
                    Vue.nextTick(() => this.subject = undefined);
                }
            },
            updateBasedOnValue() {
                if (this.value && this.value.ownSubjectType) {
                    this.subjectType = this.value.ownSubjectType;
                    this.subject = this.value;
                } else if (this.subject) {
                    Vue.nextTick(() => {
                        this.subject = undefined;
                        if (this.subjectType === ActionableSubjectType.NOTIFICATION) {
                            this.changeSubjectType(ActionableSubjectType.NOTIFICATION);
                        }
                    });
                }
            },
        },
        computed: {
            hasOthersSlot() {
                return !!this.$slots['other'] || !!this.$scopedSlots['other'];
            },
        },
        watch: {
            value() {
                this.updateBasedOnValue();
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .subject-dropdown {
        .nav-tabs {
            border: 0;
            z-index: 2;
            position: relative;
            top: 1px;
            li > a {
                padding: 3px 5px;
                &:hover {
                    background: inherit;
                    border-bottom-color: transparent;
                }
            }
            li.active > a {
                border-color: $supla-green;
                border-bottom: 0;
                background: $supla-white;
            }
        }
        &.first-selected .btn.dropdown-toggle {
            border-top-left-radius: 0;
        }
        .btn.dropdown-toggle {
            background: $supla-white !important;
            box-shadow: none !important;

            &:focus {
                outline-color: $supla-green !important;
            }
        }
    }
</style>
