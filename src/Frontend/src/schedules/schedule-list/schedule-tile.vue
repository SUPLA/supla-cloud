<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.enabled ? 'green' : 'grey')"
        @click="$emit('click')">
        <router-link :to="{name: 'schedule', params: {id: model.id}}">
            <div class="clearfix">
                <h2 class="pull-left">ID<strong>{{ model.id }} </strong></h2>
                <function-icon v-if="model.subject"
                    :model="model.subject"
                    class="pull-right"
                    width="60"></function-icon>
            </div>
            <dl>
                <dd>{{ $t(model.mode) }}</dd>
                <dt></dt>
                <dd>{{ scheduleLabel }}</dd>
                <dt></dt>
                <dd>{{ $t('Subject type') }}</dd>
                <dt>{{ $t(model.subjectType == 'channel' ? 'Channel' : 'Channel group') }}</dt>
                <dd>{{ $t('The latest execution') }}</dd>
                <dt v-if="latestExecution && latestExecution.resultTimestamp"
                    :class="latestExecution.failed ? 'text-danger' : ''"
                    :title="$t(latestExecution.result.caption)">
                    {{ latestExecution.resultTimestamp | moment('LLL') }}
                </dt>
                <dt v-else>-</dt>
                <dd>{{ $t('Next run date') }}</dd>
                <dt v-if="nearestExecution">
                    {{ nearestExecution.plannedTimestamp | moment('LLL') }}
                </dt>
                <dt v-else>-</dt>
            </dl>
            <div v-if="model.caption">
                <div class="separator"></div>
                {{ model.caption }}
            </div>
            <div class="square-link-label">
            </div>
        </router-link>
    </square-link>
</template>

<script>
    import FunctionIcon from "../../channels/function-icon.vue";
    import {channelTitle} from "../../common/filters";

    export default {
        components: {FunctionIcon},
        props: ['model'],
        computed: {
            scheduleLabel() {
                return this.$t(this.model.action.caption) + ' ' + channelTitle(this.model.subject, this);
            },
            nearestExecution() {
                if (this.model.closestExecutions.future.length) {
                    return this.model.closestExecutions.future[0];
                }
            },
            latestExecution() {
                if (this.model.closestExecutions.past.length) {
                    return this.model.closestExecutions.past[this.model.closestExecutions.past.length - 1];
                }
            },
        }
    };
</script>
