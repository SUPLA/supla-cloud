<template>
    <loading-cover :loading="!executions">
        <ul class="schedule-times"
            v-if="executions">
            <li v-for="(execution, $index) in executions.past"
                :key="'past' + $index"
                :class="'past past' + (executions.past.length - $index) + (execution.failed ? ' failed' : '')">
                <div>
                    <span class="label label-default">{{ actionCaption(execution.action, schedule.subject) }}</span>
                </div>
                {{ formatDateTimeLong(execution.resultTimestamp || execution.plannedTimestamp) }}
                <div class="small"
                    v-if="execution.id != 1">
                    {{ $t(execution.result.caption) }}
                </div>
            </li>
            <template v-if="schedule.enabled">
                <li v-for="(execution, $index) in executions.future"
                    :key="'future' + $index"
                    :class="'future future' + $index">
                    <div>
                        <span class="label label-default">{{ actionCaption(execution.action, schedule.subject) }}</span>
                    </div>
                    {{ formatDateTimeLong(execution.plannedTimestamp) }}
                </li>
            </template>
        </ul>
    </loading-cover>
</template>

<script>
  import {actionCaption} from "../../channels/channel-helpers";
  import {api} from "@/api/api.js";
  import LoadingCover from "@/common/gui/loaders/loading-cover.vue";
  import {formatDateTimeLong} from "@/common/filters-date.js";

  export default {
      components: {LoadingCover},
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
          formatDateTimeLong,
            actionCaption,
            fetch() {
                api.get(`schedules/${this.schedule.id}?include=closestExecutions`).then(({body}) => {
                    this.executions = body.closestExecutions;
                });
            }
        },
        beforeUnmount() {
            clearInterval(this.timer);
        }
    };
</script>

<style lang="scss">
    @use '../../styles/variables' as *;

    .schedule-times {
        list-style-type: none;
        padding: 0;
        text-align: center;
        font-size: 1.2em;

        li {
            margin-bottom: .7em;
        }

        .past3 {
            opacity: .6;
            font-size: 0.8em;
        }

        .past2 {
            opacity: .8;
            font-size: 0.9em;
        }

        .past1 {
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
