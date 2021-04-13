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
                    v-model="sections">
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
    import $ from "jquery";
    import Vue from "vue";
    import moment from "moment";
    import 'eonasdan-bootstrap-datetimepicker';
    import 'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css';

    export default {
        props: ['channel'],
        computed: {
            sections: {
                set(value) {
                    this.channel.param1 = +value;
                    this.$emit('change');
                },
                get() {
                    return this.channel.param1;
                }
            },
            regenerationTime: {
                set(value) {
                    const parts = value.split(':');
                    const minuteInDay = +parts[0] * 60 + (+parts[1]);
                    if (minuteInDay !== this.channel.param2) {
                        this.channel.param2 = minuteInDay;
                        this.$emit('change');
                    }
                },
                get() {
                    const hour = Math.floor(this.channel.param2 / 60);
                    const minutes = this.channel.param2 % 60;
                    return `${hour}:${minutes}`;
                }
            },
        },
        mounted() {
            $(this.$refs.clockpicker).datetimepicker({
                format: 'LT',
                inline: true,
                locale: Vue.config.lang,
                stepping: 5,
            }).on("dp.change", () => this.updateTime());
            if (this.regenerationTime) {
                const parts = this.regenerationTime.split(':');
                const currentDateFromExpression = moment().add(1, 'day').hour(parts[0]).minute(parts[1]);
                $(this.$refs.clockpicker).data('DateTimePicker').date(currentDateFromExpression);
            }
        },
        methods: {
            updateTime() {
                const date = $(this.$refs.clockpicker).data('DateTimePicker').date();
                this.regenerationTime = moment(date).format('H:m');
            },
        }
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
