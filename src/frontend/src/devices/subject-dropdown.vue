<template>
    <div :class="'subject-dropdown ' + (subjectType == 'channel' ? 'first-selected' : '')">
        <div class="panel-group panel-accordion m-0">
            <transition-expand>
                <div :class="`panel panel-${subjectType === 'channel' ? 'success' : 'default'}`"
                    v-if="!subjectType || subjectType === 'channel'">
                    <div class="panel-heading d-flex" @click="changeSubjectType('channel')">
                        <a role="button" tabindex="0" class="text-inherit flex-grow-1">
                            {{ $t('Channels') }}
                        </a>
                        <fa :icon="subjectType === 'channel' ? 'chevron-down' : 'chevron-right'"/>
                    </div>
                    <transition-expand>
                        <div class="panel-body" v-if="subjectType == 'channel'">
                            <div class="d-flex">
                                <label class="flex-grow-1">{{ $t('Choose the channel') }}</label>
                                <router-link v-if="subject" :to="{name: 'channel', params: {id: subject.id}}" target="_blank">
                                    {{ $t('Go to the channel') }}
                                    <fa icon="arrow-right" class="ml-2"/>
                                </router-link>
                            </div>
                            <ChannelsDropdown v-model="subject"
                                :hide-none="true"
                                @input="subjectChanged"
                                :filter="filter"
                                :params="channelsDropdownParams"/>
                            <slot></slot>
                        </div>
                    </transition-expand>
                </div>
            </transition-expand>
            <transition-expand>
                <div :class="`panel panel-${subjectType === 'channelGroup' ? 'success' : 'default'}`"
                    v-if="!subjectType || subjectType === 'channelGroup'">
                    <div class="panel-heading d-flex" @click="changeSubjectType('channelGroup')">
                        <a role="button" tabindex="0" class="text-inherit flex-grow-1">
                            {{ $t('Channel groups') }}
                        </a>
                        <fa :icon="subjectType === 'channelGroup' ? 'chevron-down' : 'chevron-right'"/>
                    </div>
                    <transition-expand>
                        <div class="panel-body" v-if="subjectType == 'channelGroup'">
                            <div class="d-flex">
                                <label class="flex-grow-1">{{ $t('Choose the channel group') }}</label>
                                <router-link v-if="subject" :to="{name: 'channelGroup', params: {id: subject.id}}" target="_blank">
                                    {{ $t('Go to the channel group') }}
                                    <fa icon="arrow-right" class="ml-2"/>
                                </router-link>
                            </div>
                            <ChannelGroupsDropdown @input="subjectChanged"
                                :filter="filter"
                                :params="channelsDropdownParams"
                                v-model="subject"/>

                            <slot></slot>
                        </div>
                    </transition-expand>
                </div>
            </transition-expand>
            <transition-expand>
                <div :class="`panel panel-${subjectType === 'scene' ? 'success' : 'default'}`"
                    v-if="!subjectType || subjectType === 'scene'">
                    <div class="panel-heading d-flex" @click="changeSubjectType('scene')">
                        <a role="button" tabindex="0" class="text-inherit flex-grow-1">
                            {{ $t('Scenes') }}
                        </a>
                        <fa :icon="subjectType === 'scene' ? 'chevron-down' : 'chevron-right'"/>
                    </div>
                    <transition-expand>
                        <div class="panel-body" v-if="subjectType == 'scene'">
                            <div class="d-flex">
                                <label class="flex-grow-1">{{ $t('Choose the scene') }}</label>
                                <router-link v-if="subject" :to="{name: 'scene', params: {id: subject.id}}" target="_blank">
                                    {{ $t('Go to the scene') }}
                                    <fa icon="arrow-right" class="ml-2"/>
                                </router-link>
                            </div>
                            <ScenesDropdown
                                @input="subjectChanged"
                                :filter="filter"
                                v-model="subject"/>
                            <slot></slot>
                        </div>
                    </transition-expand>
                </div>
            </transition-expand>
            <transition-expand>
                <div :class="`panel panel-${subjectType === 'schedule' ? 'success' : 'default'}`"
                    v-if="!disableSchedules && (!subjectType || subjectType === 'schedule')">
                    <div class="panel-heading d-flex" @click="changeSubjectType('schedule')">
                        <a role="button" tabindex="0" class="text-inherit flex-grow-1">
                            {{ $t('Schedules') }}
                        </a>
                        <fa :icon="subjectType === 'schedule' ? 'chevron-down' : 'chevron-right'"/>
                    </div>
                    <transition-expand>
                        <div class="panel-body" v-if="subjectType == 'schedule'">
                            <div class="d-flex">
                                <label class="flex-grow-1">{{ $t('Choose the schedule') }}</label>
                                <router-link v-if="subject" :to="{name: 'schedule', params: {id: subject.id}}" target="_blank">
                                    {{ $t('Go to the schedule') }}
                                    <fa icon="arrow-right" class="ml-2"/>
                                </router-link>
                            </div>
                            <SchedulesDropdown
                                @input="subjectChanged"
                                :filter="filter"
                                v-model="subject"/>
                            <slot></slot>
                        </div>
                    </transition-expand>
                </div>
            </transition-expand>
            <transition-expand>
                <div :class="`panel panel-${subjectType === 'notification' ? 'success' : 'default'}`"
                    v-if="notificationsEnabled && (!subjectType || subjectType === 'notification')">
                    <div class="panel-heading d-flex" @click="changeSubjectType('notification')">
                        <a role="button" tabindex="0" class="text-inherit flex-grow-1">
                            {{ $t('Send notification') }}
                        </a>
                        <fa :icon="subjectType === 'notification' ? 'chevron-down' : 'chevron-right'"/>
                    </div>
                    <transition-expand>
                        <div class="panel-body" v-if="subjectType == 'notification'">
                            <slot></slot>
                        </div>
                    </transition-expand>
                </div>
            </transition-expand>
            <transition-expand>
                <div :class="`panel panel-${subjectType === 'other' ? 'success' : 'default'}`"
                    v-if="hasOthersSlot && (!subjectType || subjectType === 'other')">
                    <div class="panel-heading d-flex" @click="changeSubjectType('other')">
                        <a role="button" tabindex="0" class="text-inherit flex-grow-1">
                            {{ $t('Other') }}
                        </a>
                        <fa :icon="subjectType === 'other' ? 'chevron-down' : 'chevron-right'"/>
                    </div>
                    <transition-expand>
                        <div class="panel-body" v-if="subjectType == 'other'">
                            <slot name="other"
                                :subject="subject"
                                :on-input="subjectChanged"
                                v-if="subjectType == 'other'"></slot>
                        </div>
                    </transition-expand>
                </div>
            </transition-expand>
        </div>
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
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {mapState} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

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
        },
        components: {TransitionExpand, SchedulesDropdown, ScenesDropdown, ChannelGroupsDropdown, ChannelsDropdown},
        data() {
            return {
                subject: undefined,
                subjectType: undefined,
            };
        },
        mounted() {
            this.updateBasedOnValue();
        },
        methods: {
            changeSubjectType(subjectType) {
                this.subjectType = subjectType === this.subjectType ? undefined : subjectType;
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
                    Vue.nextTick(() => this.subject = this.subjectType = undefined);
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
                        } else {
                            this.subjectType = undefined;
                        }
                    });
                }
            },
        },
        computed: {
            hasOthersSlot() {
                return !!this.$slots['other'] || !!this.$scopedSlots['other'];
            },
            notificationsEnabled() {
                return !this.disableNotifications && this.frontendConfig.notificationsEnabled;
            },
            ...mapState(useFrontendConfigStore, {frontendConfig: 'config'}),
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
