<template>
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
            v-for="execution, $index in executions.future">
            {{ execution.plannedTimestamp|moment('LLLL') }}
        </li>
        <!--{% endfor %}-->
        <!--{% for execution in closestExecutions['future'] %}-->
        <!--<li class="future future{{ loop.index }}">{{ execution.plannedTimestamp|localizeddate('full', 'medium', app.request.locale, app.user.timezone ) }}</li>-->
        <!--{% endfor %}-->
    </ul>
</template>

<script>
    export default {
        props: ['schedule'],
        data() {
            return {
                executions: undefined
            };
        },
        mounted() {
            const endpoint = `schedules/${this.schedule.id}?include=closestExecutions`;
            this.$http.get(endpoint).then(({body}) => {
                this.executions = body.closestExecutions;
            });
        }
    };
</script>

<style lang="scss">
    .schedule-times {
        list-style-type: none;
        padding: 0;
        text-align: center;
        font-size: 1.2em;
    }

    .schedule-times li {
        margin-bottom: 5px;
    }

    .schedule-times .past2 {
        opacity: .6;
        font-size: 0.8em;
    }

    .schedule-times .past1 {
        opacity: .8;
        font-size: 0.9em;
    }

    .schedule-times .past0 {
        opacity: .9;
        font-size: 1em;
    }

    .schedule-times .past {
        color: darkgreen;
    }

    .schedule-times .failed {
        color: #f00;
    }

    .schedule-times .future0 {
        opacity: 1;
        font-size: 1.1em;
        font-weight: bold;
    }

    .schedule-times .future1 {
        opacity: .9;
        font-size: 1em;
    }

    .schedule-times .future2 {
        opacity: .8;
        font-size: .9em;
    }

    .schedule-times .future3 {
        opacity: .6;
        font-size: .8em;
    }
</style>
