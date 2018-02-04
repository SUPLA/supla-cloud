<template>
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="nav nav-tabs">
                    <li :class="currentTab == 'schedules' ? 'active' : ''">
                        <a @click="currentTab = 'schedules'">{{ $t('Schedules') }}</a>
                    </li>
                    <li :class="currentTab == 'channelGroups' ? 'active' : ''">
                        <a @click="currentTab = 'channelGroups'">{{ $t('Channel groups') }}</a>
                    </li>
                    <li :class="currentTab == 'temperatureHistory' ? 'active' : ''">
                        <a @click="currentTab = 'temperatureHistory'">{{ $t('Temperature history') }}</a>
                    </li>
                </ul>
                <schedule-list v-if="currentTab == 'schedules'"
                    :channel-id="channel.id"></schedule-list>
            </div>
        </div>
    </div>
</template>

<script>
    import ScheduleList from "../schedules/schedule-list/schedule-list";

    export default {
        props: ['channel'],
        components: {
            ScheduleList,
        },
        data() {
            return {
                currentTab: 'schedules',
            };
        },
    };
</script>
