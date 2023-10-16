<template>
    <div>
        <div class="radio">
            <label>
                <input type="radio" value="none" v-model="countdownMode" @change="onChange()">
                {{ $t('Until the next change') }}
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" value="delay" v-model="countdownMode" @change="onChange()">
                {{ $t('For a period') }}
            </label>
        </div>
        <transition-expand>
            <div class="form-group my-1" v-if="countdownMode === 'delay'">
                <span class="input-group">
                    <input type="number" class="form-control" min="0" v-model="countdownValue" @change="onChange()">
                    <span class="input-group-btn">
                        <a class="btn btn-white" @click="changeMultiplier()">
                            <span v-if="multiplier === 1">{{ $t('seconds') }}</span>
                            <span v-if="multiplier === 60">{{ $t('minutes') }}</span>
                            <span v-if="multiplier === 3600">{{ $t('hours') }}</span>
                            <span v-if="multiplier === 86400">{{ $t('days') }}</span>
                        </a>
                    </span>
                </span>
            </div>
        </transition-expand>
        <div class="radio" v-if="withCalendar">
            <label>
                <input type="radio" value="calendar" v-model="countdownMode" @change="onDateChange()">
                {{ $t('Until a date and time') }}
            </label>
        </div>
        <transition-expand>
            <div class="form-group" v-if="countdownMode === 'calendar'">
                <input type="datetime-local" v-model="countdownDate" @change="onDateChange()" class="form-control"
                    :min="minDate">
            </div>
        </transition-expand>
    </div>
</template>

<script>

    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {formatDateForHtmlInput} from "@/common/filters-date";
    import {DateTime} from "luxon";

    export default {
        components: {TransitionExpand},
        props: {
            value: Number,
            withCalendar: Boolean,
        },
        data() {
            return {
                countdownMode: 'none',
                multiplier: 60,
                countdownValue: 15,
                countdownDate: undefined,
                dateUpdateInterval: undefined,
            };
        },
        beforeMount() {
            this.dateUpdateInterval = setInterval(() => {
                if (this.countdownMode === 'calendar' && this.countdownDate) {
                    this.onDateChange();
                }
            }, 60000);
            this.setValueAndMultiplier(this.value);
        },
        beforeDestroy() {
            clearInterval(this.dateUpdateInterval);
        },
        methods: {
            setValueAndMultiplier(value) {
                for (const multiplier of [86400, 3600, 60, 1]) {
                    if (value % multiplier === 0) {
                        this.multiplier = multiplier;
                        this.value = Math.round(value / multiplier);
                        break;
                    }
                }
                if (this.value) {
                    this.countdownMode = 'delay';
                }
            },
            onChange() {
                console.log('blablabla')
                if (this.countdownMode === 'delay' && !this.countdownValue) {
                    this.countdownValue = 15;
                    this.multiplier = 60;
                } else if (this.countdownMode === 'none') {
                    this.countdownValue = 0;
                }
                this.$emit('input', this.countdownValue * this.multiplier);
            },
            changeMultiplier() {
                if (this.multiplier === 1) {
                    this.multiplier = 60;
                } else if (this.multiplier === 60) {
                    this.multiplier = 3600;
                } else if (this.multiplier === 3600) {
                    this.multiplier = 86400;
                } else {
                    this.multiplier = 1;
                }
                this.onChange();
            },
            onDateChange() {
                this.multiplier = 60;
                const targetDate = DateTime.fromISO(this.countdownDate).startOf('minute');
                const now = DateTime.now().startOf('minute');
                if (now < targetDate) {
                    this.countdownValue = targetDate.diff(now).as('minutes');
                } else {
                    this.countdownValue = 0;
                }
                this.onChange();
            }
        },
        computed: {
            minDate() {
                return formatDateForHtmlInput(DateTime.now().toISO());
            }
        },
        watch: {
            value() {
                if (this.value !== this.multiplier * this.countdownValue) {
                    this.setValueAndMultiplier(this.value);
                }
            }
        }
    };
</script>
