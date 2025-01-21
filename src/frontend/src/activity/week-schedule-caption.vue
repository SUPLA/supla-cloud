<template>
    <span>
        <span v-if="hasSomethingSet">
            <span v-for="(block, $index) in weekScheduleCaption" :key="$index" class="schedule-block">
                {{ block.weekdays }}:
                {{ block.blocks }}</span>
        </span>
        <span v-else>{{ emptyCaption }}</span>
    </span>
</template>

<script>
    import {DateTime} from "luxon";

    export default {
        props: {
            schedule: {
                type: Object,
                required: false,
            },
            emptyCaption: {
                type: String,
                default: 'No schedule',
            }
        },
        data() {
            return {
                weekdayLabels: [
                    '',
                    DateTime.fromFormat('1', 'c').toFormat('ccc'),
                    DateTime.fromFormat('2', 'c').toFormat('ccc'),
                    DateTime.fromFormat('3', 'c').toFormat('ccc'),
                    DateTime.fromFormat('4', 'c').toFormat('ccc'),
                    DateTime.fromFormat('5', 'c').toFormat('ccc'),
                    DateTime.fromFormat('6', 'c').toFormat('ccc'),
                    DateTime.fromFormat('7', 'c').toFormat('ccc'),
                ]
            }
        },
        methods: {
            hourLabelStart(hour) {
                const start = DateTime.fromFormat(('0' + hour).substr(-2), 'HH');
                return start.toLocaleString(DateTime.TIME_SIMPLE);
            },
            hourLabelEnd(hour) {
                if (hour !== 24) {
                    return this.hourLabelStart(hour);
                }
                const end = DateTime.fromFormat('23', 'HH').endOf('hour');
                return end.toLocaleString(DateTime.TIME_SIMPLE);
            },
        },
        mounted() {
            this.$emit('hasSomethingSet', this.hasSomethingSet);
        },
        computed: {
            hasSomethingSet() {
                if (this.schedule) {
                    const entries = Object.entries(this.schedule);
                    return entries.length !== 0 && (entries.length !== 7 || entries.reduce((has, [, hours]) => {
                        return has || (hours.length > 0 && hours.length < 24);
                    }, false));
                } else {
                    return false;
                }
            },
            weekScheduleCaption() {
                const captionBlocks = Object.entries(this.schedule).reduce((weekdayBlocks, [weekday, hours]) => {
                    if (hours?.length) {
                        let blocks = [];
                        let currentBlock;
                        hours.forEach((hour) => {
                            if (!currentBlock || currentBlock.end !== hour) {
                                if (currentBlock) {
                                    blocks.push(currentBlock);
                                }
                                currentBlock = {start: hour};
                            }
                            currentBlock.end = hour + 1;
                        });
                        blocks.push(currentBlock);
                        blocks = blocks.map(({start, end}) => `${this.hourLabelStart(start)} - ${this.hourLabelEnd(end)}`).join(', ');
                        if (weekdayBlocks.length && weekdayBlocks[weekdayBlocks.length - 1].blocks === blocks) {
                            weekdayBlocks[weekdayBlocks.length - 1].weekdays.push(this.weekdayLabels[weekday]);
                        } else {
                            weekdayBlocks.push({
                                weekdays: [this.weekdayLabels[weekday]],
                                blocks,
                            });
                        }
                    }
                    return weekdayBlocks;
                }, []);
                return captionBlocks.map(({weekdays, blocks}) => ({blocks, weekdays: weekdays.join(', ')}));
            }
        },
    }
</script>

<style lang="scss" scoped>
    .schedule-block {
        &::after {
            content: ', ';
        }

        &:last-child::after {
            content: '';
        }
    }
</style>
