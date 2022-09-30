<template>
    <div>
        <dl>
            <dd>{{ $t('Number of sections') }}</dd>
            <dt>
                <input type="number"
                    step="1"
                    min="1"
                    max="7"
                    class="form-control text-center"
                    v-model="channel.config.sectionsCount"
                    @input="$emit('change')">
            </dt>
            <dd>{{ $t('Regeneration starts at') }}</dd>
            <dt class="digiglass-rest-timepicker">
                <div class="clockpicker"
                    ref="clockpicker"></div>
            </dt>
        </dl>
    </div>
</template>

<script>
    import moment from "moment";
    import {Namespace, TempusDominus} from "@eonasdan/tempus-dominus";
    import "@eonasdan/tempus-dominus/dist/css/tempus-dominus.min.css";

    export default {
        props: ['channel'],
        data() {
            return {
                picker: undefined,
            };
        },
        computed: {
            regenerationTime: {
                set(value) {
                    const parts = value.split(':');
                    const minuteInDay = +parts[0] * 60 + (+parts[1]);
                    if (minuteInDay !== this.channel.config.regenerationTimeStart) {
                        this.channel.config.regenerationTimeStart = minuteInDay;
                        this.$emit('change');
                    }
                },
                get() {
                    const hour = Math.floor(this.channel.config.regenerationTimeStart / 60);
                    const minutes = this.channel.config.regenerationTimeStart % 60;
                    return `${hour}:${minutes}`;
                }
            },
        },
        mounted() {
            this.picker = new TempusDominus(this.$refs.clockpicker, {
                localization: {
                    locale: this.$i18n.locale,
                    format: 'LT',
                },
                stepping: 5,
                useCurrent: false,
                defaultDate: moment().startOf('day').toDate(),
                display: {
                    components: {calendar: false},
                    inline: true,
                    viewMode: 'clock',
                    icons: {
                        type: 'icons',
                        time: 'pe-7s-clock',
                        date: 'glyphicon glyphicon-calendar',
                        up: 'glyphicon glyphicon-chevron-up',
                        down: 'glyphicon glyphicon-chevron-down',
                        previous: 'glyphicon glyphicon-chevron-left',
                        next: 'glyphicon glyphicon-chevron-right',
                        today: 'fa-solid fa-calendar-check',
                        clear: 'glyphicon glyphicon-trash',
                        close: 'glyphicon glyphicon-remove'
                    },
                }
            });

            this.picker.subscribe(Namespace.events.change, () => {
                const date = this.picker.viewDate;
                this.regenerationTime = moment(date).format('H:m');
            });

            if (this.regenerationTime) {
                const parts = this.regenerationTime.split(':');
                const currentDateFromExpression = moment().add(1, 'day').hour(parts[0]).minute(parts[1]);
                this.picker.dates.setFromInput(currentDateFromExpression.toDate());
            }
        },
    };
</script>

<style lang="scss">
    .digiglass-rest-timepicker {
        .bootstrap-datetimepicker-widget {
            table {
                width: auto;
                margin: 0 auto;
                td {
                    &, & span {
                        height: auto;
                        line-height: normal;
                        width: auto !important;
                    }
                    &.separator {
                        padding: 0 1em;
                    }
                }
            }
        }
        .table-condensed {
            .minute, .hour {
                padding: 2px 5px;
            }
        }
    }
</style>
