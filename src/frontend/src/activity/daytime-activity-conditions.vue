<template>
    <div>
        <h4 class="text-center">
            {{ $t('Daytime criteria') }}
        </h4>
        <div>
            <div v-for="(t, $index) in times" :key="t.id">
                <div>
                    <DaytimeActivityCondition v-model="t.times" @input="updateModel()" class="flex-grow-1"/>
                    <div class="text-right">
                        <a class="text-default small" @click="removeTime($index)">
                            <fa icon="trash" class="text-muted"/>
                            {{ $t('Delete') }}
                        </a>
                    </div>
                </div>
                <div class="or-hr" v-if="$index < times.length - 1">{{ $t('OR') }}</div>
            </div>
        </div>
        <a class="btn btn-white" @click="addTime()" v-if="times.length < 2">
            <fa icon="plus"/>
            {{ $t('Add') }}
        </a>
    </div>
</template>

<script>
    import DaytimeActivityCondition from "@/activity/daytime-activity-condition.vue";
    import {isEqual, startCase} from "lodash";

    export default {
        components: {DaytimeActivityCondition},
        props: {
            value: Array,
        },
        data() {
            return {
                times: [],
                timeIdCounter: 0,
            };
        },
        mounted() {
            this.initTimesFromModel();
        },
        methods: {
            initTimesFromModel() {
                this.times = this.conditionsToTimes(this.value || []).map(times => ({id: ++this.timeIdCounter, times}));
            },
            conditionsToTimes(conditions) {
                const times = [];
                conditions.forEach(condition => {
                    if (condition.length === 2) {
                        const time = [-120, 120];
                        condition.forEach(t => {
                            const key = Object.keys(t)[0];
                            const timeIndex = key.startsWith('after') ? 0 : 1;
                            const delta = key.endsWith('rise') ? -60 : 60;
                            time[timeIndex] = t[key] + delta;
                        });
                        times.push(time);
                    } else if (condition.length === 1) {
                        const time = [-120, 120];
                        const t = condition[0];
                        const key = Object.keys(t)[0];
                        const timeIndex = key.startsWith('after') ? 0 : 1;
                        const delta = key.endsWith('rise') ? -60 : 60;
                        time[timeIndex] = t[key] + delta;
                        times.push(time);
                    }
                });
                const finalTimes = [];
                for (let i = 0; i < times.length; i++) {
                    const t = times[i];
                    const nextT = times[i + 1];
                    if (t[1] === 120 && nextT?.[0] === -120) {
                        finalTimes.push([t[0], nextT[1]]);
                        ++i;
                    } else {
                        finalTimes.push(t);
                    }
                }
                return finalTimes;
            },
            timesToConditions(times) {
                const conditions = [];
                times.forEach(time => {
                    const timeLeft = this.timeToSun(time[0]);
                    const timeRight = this.timeToSun(time[1]);
                    if (time[0] < time[1]) {
                        const condition = [];
                        if (timeLeft) {
                            condition.push({[`after${startCase(timeLeft.mode)}`]: timeLeft.offset});
                        }
                        if (timeRight) {
                            condition.push({[`before${startCase(timeRight.mode)}`]: timeRight.offset});
                        }
                        if (condition.length) {
                            conditions.push(condition);
                        }
                    } else if (time[0] > time[1]) {
                        if (timeLeft) {
                            conditions.push([{[`after${startCase(timeLeft.mode)}`]: timeLeft.offset}])
                        }
                        if (timeRight) {
                            conditions.push([{[`before${startCase(timeRight.mode)}`]: timeRight.offset}]);
                        }
                    }
                });
                return conditions;
            },
            timeToSun(time) {
                if (time >= 120 || time <= -120) {
                    return undefined;
                } else if (time < 0) {
                    return {mode: 'sunrise', offset: time + 60};
                } else {
                    return {mode: 'sunset', offset: time - 60};
                }
            },
            addTime() {
                this.times.push({times: [-60, 60]});
                this.updateModel();
            },
            removeTime(index) {
                this.times.splice(index, 1);
                this.updateModel();
            },
            updateModel() {
                this.$emit('input', this.modelValue);
            },
        },
        computed: {
            modelValue() {
                return this.timesToConditions(this.times.map(({times}) => times));
            }
        },
        watch: {
            value(value) {
                if (!isEqual(value, this.modelValue)) {
                    this.initTimesFromModel();
                }
            }
        }
    }
</script>
