<template>
    <div>
        <loading-cover :loading="!schedule">
            <div class="container">
                <div class="clearfix left-right-header"
                    v-if="schedule">
                    <div>
                        <h1>
                            {{ $t('Schedule') }} ID{{ schedule.id }}
                            <span class="small"
                                v-if="schedule.caption">{{ schedule.caption }}</span>

                        </h1>
                    </div>
                    <div>
                        <!--<a class="btn btn-green btn-lg"-->
                        <!--:href="'/schedules/new' + (channelId ? '?channelId=' + channelId : '') | withBaseUrl">-->
                        <!--<i class="pe-7s-plus"></i>-->
                        <!--{{ $t('Create New Schedule') }}-->
                        <!--</a>-->
                    </div>
                </div>
            </div>
        </loading-cover>
    </div>
</template>

<script type="text/babel">
    export default {
        components: {},
        props: ['scheduleId'],
        data() {
            return {
                schedule: undefined
            };
        },
        mounted() {
            let endpoint = `schedules/${this.scheduleId}?include=channel,iodevice,location,closestExecutions`;
            this.$http.get(endpoint).then(({body}) => {
                this.schedule = body;
            });
        },
        methods: {}
    };
</script>
