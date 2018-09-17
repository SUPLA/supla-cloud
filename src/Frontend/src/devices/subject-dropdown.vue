<template>
    <div class="subject-dropdown">
        <ul class="nav nav-tabs">
            <li :class="subjectType == 'channel' ? 'active' : ''">
                <a @click="changeSubjectType('channel')">{{$t('Channels')}}</a>
            </li>
            <li :class="subjectType == 'channelGroup' ? 'active' : ''">
                <a @click="changeSubjectType('channelGroup')">{{$t('Channel groups')}}</a>
            </li>
        </ul>
        {{ subjectType }}
        <channels-dropdown v-model="subject"
            v-if="subjectType == 'channel'"
            @input="subjectChanged()"
            :params="channelsDropdownParams"></channels-dropdown>
        <channel-groups-dropdown @input="subjectChanged"
            v-if="subjectType == 'channelGroup'"
            v-model="subject"></channel-groups-dropdown>
    </div>
</template>

<script>
    import ChannelsDropdown from "./channels-dropdown";
    import ChannelGroupsDropdown from "../channel-groups/channel-groups-dropdown";

    export default {
        props: ['value', 'channelsDropdownParams'],
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
                this.$emit('input', {subject: this.subject, type: this.subjectType});
            }
        },
        watch: {
            value() {
                if (this.value && this.value.type) {
                    this.subjectType = this.value.type;
                    this.subject = this.value.subject;
                } else {
                    this.changeSubjectType('channel');
                }
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .subject-dropdown {

        .nav-tabs {
            li > a {
                padding: 3px 5px;
            }
        }
    }
</style>
