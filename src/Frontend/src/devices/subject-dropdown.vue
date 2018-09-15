<template>
    <div class="subject-dropdown">
        <ul class="nav nav-tabs">
            <li :class="subjectType == 'channel' ? 'active' : ''">
                <a @click="subjectType = 'channel'">{{$t('Channels')}}</a>
            </li>
            <li :class="subjectType == 'channelGroup' ? 'active' : ''">
                <a @click="subjectType = 'channelGroup'">{{$t('Channel groups')}}</a>
            </li>
        </ul>
        <channels-dropdown v-model="subject"
            v-if="subjectType == 'channel'"
            @input="subjectChanged()"
            :params="channelsDropdownParams"></channels-dropdown>
    </div>
</template>

<script>
    import ChannelsDropdown from "./channels-dropdown";

    export default {
        props: ['value', 'channelsDropdownParams'],
        components: {ChannelsDropdown},
        data() {
            return {
                subject: undefined,
                subjectType: 'channel'
            };
        },
        mounted() {

        },
        methods: {
            subjectChanged() {
                this.$emit('input', {subject: this.subject, type: this.subjectType});
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
