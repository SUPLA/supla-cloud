<template>
    <loading-cover :loading="!executions">
        <ul class="schedule-times"
            v-if="executions">
            <li v-for="execution, $index in executions.past"
                :class="'past past' + (executions.past.length - $index) + (execution.failed ? ' failed' : '')">
                {{ (execution.resultTimestamp || execution.plannedTimestamp) | moment('LLLL') }}
                <div class="small"
                    v-if="execution.failed">
                    {{ $t(execution.result.caption) }}
                </div>
            </li>
            <li :class="'future future' + $index"
                v-if="schedule.enabled"
                v-for="execution, $index in executions.future">
                {{ execution.plannedTimestamp|moment('LLLL') }}
            </li>
        </ul>
    </loading-cover>
</template>

<script>
    export default {
        props: ['schedule'],
        data() {
            return {
                executions: undefined,
                timer: undefined
            };
        },
        mounted() {
            this.timer = setInterval(() => this.fetch(), 60000);
            this.fetch();
        },
        methods: {
            fetch() {
                this.$http.get(`schedules/${this.schedule.id}?include=closestExecutions`).then(({body}) => {
                    this.executions = body.closestExecutions;
                });
            }
        },
        beforeDestroy() {
            clearInterval(this.timer);
        }
    };
</script>

<style lang="scss">
    @import '../../styles/variables';

    .schedule-times {
        list-style-type: none;
        padding: 0;
        text-align: center;
        font-size: 1.2em;
        li {
            margin-bottom: 5px;
        }
        .past2 {
            opacity: .6;
            font-size: 0.8em;
        }
        .past1 {
            opacity: .8;
            font-size: 0.9em;
        }
        .past0 {
            opacity: .9;
            font-size: 1em;
        }
        .past {
            color: darkgreen;
        }
        .failed {
            color: $supla-red;
        }
        .future0 {
            opacity: 1;
            font-size: 1.1em;
            font-weight: bold;
        }
        .future1 {
            opacity: .9;
            font-size: 1em;
        }
        .future2 {
            opacity: .8;
            font-size: .9em;
        }
        .future3 {
            opacity: .6;
            font-size: .8em;
        }
    }
</style>
