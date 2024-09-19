<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.enabled ? 'green' : 'grey')"
        @click="$emit('click')">
        <router-link :to="{name: 'schedule', params: {id: model.id}}">
            <div class="clearfix">
                <function-icon v-if="model.subject"
                    :model="model.subject"
                    class="pull-right"
                    width="60"></function-icon>
                <h3>{{ caption }}</h3>
            </div>
            <dl>
                <dd>{{ $t(`scheduleMode_${model.mode}`) }}</dd>
                <dt></dt>
                <dd>{{ scheduleLabel }}</dd>
                <dt></dt>
                <dd>ID</dd>
                <dt>{{ model.id }}</dt>
                <dd>{{ $t('Subject type') }}</dd>
                <dt>{{ model.subjectType == 'channel' ? $t('Channel') : $t('Channel group') }}</dt>
            </dl>
            <dl v-if="model.closestExecutions">
                <dd>{{ $t('The latest execution') }}</dd>
                <dt v-if="latestExecution && latestExecution.resultTimestamp"
                    :class="latestExecution.failed ? 'text-danger' : ''"
                    :title="$t(latestExecution.result.caption)">
                    {{ latestExecution.resultTimestamp | formatDateTime }}
                </dt>
                <dt v-else>-</dt>
                <dd>{{ $t('Next run date') }}</dd>
                <dt v-if="nearestExecution">
                    {{ nearestExecution.plannedTimestamp | formatDateTime }}
                </dt>
                <dt v-else>-</dt>
            </dl>
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
            caption() {
                return this.model.caption || this.$t('Schedule') + ' ID' + this.model.id;
            },
            scheduleLabel() {
                return this.model.subject ? channelTitle(this.model.subject) : '';
            },
            nearestExecution() {
                if (this.model.closestExecutions?.future.length) {
                    return this.model.closestExecutions.future[0];
                }
                return undefined;
            },
            latestExecution() {
                if (this.model.closestExecutions?.past.length) {
                    return this.model.closestExecutions.past[this.model.closestExecutions.past.length - 1];
                }
                return undefined;
            },
        }
    };
</script>
