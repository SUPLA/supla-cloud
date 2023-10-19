<template>
    <div>
        <div class="radio mt-0" v-show="!hideNoTimer">
            <label>
                <input type="radio" value="none" v-model="countdownMode" @change="onChange()">
                {{ $t('Until the next change') }}
            </label>
        </div>
        <div class="radio" v-if="withCalendar || !hideNoTimer">
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
                            <span v-if="multiplier === 1">{{ $t('milliseconds') }}</span>
                            <span v-if="multiplier === 1000">{{ $t('seconds') }}</span>
                            <span v-if="multiplier === 60000">{{ $t('minutes') }}</span>
                            <span v-if="multiplier === 3600000">{{ $t('hours') }}</span>
                            <span v-if="multiplier === 86400000">{{ $t('days') }}</span>
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
            hideNoTimer: Boolean,
            disableMs: Boolean,
        },
        data() {
            return {
                countdownMode: 'none',
                multiplier: 60000,
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
                for (const multiplier of [86400000, 3600000, 60000, 1000, 1]) {
                    if (value % multiplier === 0) {
                        this.multiplier = multiplier;
                        this.countdownValue = Math.round(value / multiplier);
                        break;
                    }
                }
                if (this.value || this.hideNoTimer) {
                    this.countdownMode = 'delay';
                }
                if (!this.value && this.countdownMode === 'delay') {
                    this.onChange();
                }
            },
            onChange() {
                if (this.countdownMode === 'delay' && !this.countdownValue) {
                    this.countdownValue = 15;
                    this.multiplier = 60000;
                } else if (this.countdownMode === 'none') {
                    this.countdownValue = 0;
                }
                this.$emit('input', this.countdownValue * this.multiplier);
            },
            changeMultiplier() {
                if (this.multiplier === 1) {
                    this.multiplier = 1000;
                } else if (this.multiplier === 1000) {
                    this.multiplier = 60000;
                } else if (this.multiplier === 60000) {
                    this.multiplier = 3600000;
                } else if (this.multiplier === 3600000) {
                    this.multiplier = 86400000;
                } else {
                    this.multiplier = this.disableMs ? 1000 : 1;
                }
                this.onChange();
            },
            onDateChange() {
                this.multiplier = 60000;
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
