<template>
    <div :class="'subject-dropdown ' + (subjectType == 'channel' ? 'first-selected' : '')">
        <ul class="nav nav-tabs">
            <li :class="subjectType == 'channel' ? 'active' : ''">
                <a @click="changeSubjectType('channel')">{{$t('Channels')}}</a>
            </li>
            <li :class="subjectType == 'channelGroup' ? 'active' : ''">
                <a @click="changeSubjectType('channelGroup')">{{$t('Channel groups')}}</a>
            </li>
        </ul>
        <channels-dropdown v-model="subject"
            v-if="subjectType == 'channel'"
            @input="subjectChanged()"
            :filter="filter"
            :params="channelsDropdownParams"></channels-dropdown>
        <channel-groups-dropdown @input="subjectChanged"
            v-if="subjectType == 'channelGroup'"
            :filter="filter"
            v-model="subject"></channel-groups-dropdown>
    </div>
</template>

<script>
    import ChannelsDropdown from "./channels-dropdown";
    import ChannelGroupsDropdown from "../channel-groups/channel-groups-dropdown";
    import Vue from "vue";

    export default {
        props: ['value', 'channelsDropdownParams', 'filter', 'clearOnSelect'],
        components: {ChannelGroupsDropdown, ChannelsDropdown},
        data() {
            return {
                subject: undefined,
                subjectType: 'channel'
            };
        },
        methods: {
            changeSubjectType(subjectType) {
                this.subjectType = subjectType;
                this.subject = undefined;
                this.subjectChanged();
            },
            subjectChanged() {
                this.$emit('input', this.subject);
                if (this.clearOnSelect && this.subject) {
                    Vue.nextTick(() => this.subject = undefined);
                }
            },
            updateBasedOnValue() {
                if (this.value && this.value.subjectType) {
                    this.subjectType = this.value.subjectType;
                    this.subject = this.value;
                } else if (this.subject) {
                    Vue.nextTick(() => this.subject = undefined);
                }
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
