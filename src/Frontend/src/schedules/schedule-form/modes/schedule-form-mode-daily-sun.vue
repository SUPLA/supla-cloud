<template>
    <div>
        <div class="form-group">
            <span class="input-group">
                <input type="number"
                    class="form-control"
                    step="5"
                    min="0"
                    max="200"
                    maxlength="3"
                    v-model="sunMinute"
                    @change="updateTimeExpression()">
                <span class="input-group-addon">
                    {{ $t('minutes') }}
                </span>
            </span>
        </div>
        <div class="form-group">
            <div class="text-right">
                <div class="btn-group">
                    <a class="btn btn-default"
                        @click="sunBefore = !sunBefore; updateTimeExpression()"
                        :disabled="!sunMinute">
                        <span v-show="sunBefore">{{ $t('before') }}</span>
                        <span v-show="!sunBefore">{{ $t('after') }}</span>
                    </a>
                    <a class="btn btn-default"
                        @click="sunrise = !sunrise; updateTimeExpression()">
                        <span v-show="sunrise && sunBefore">{{ $t('sunrise-before') }}</span>
                        <span v-show="!sunrise && sunBefore">{{ $t('sunset-before') }}</span>
                        <span v-show="sunrise && !sunBefore">{{ $t('sunrise-after') }}</span>
                        <span v-show="!sunrise && !sunBefore">{{ $t('sunset-after') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import {mapState} from "vuex";

    export default {
        name: 'schedule-form-mode-daily-sun',
        props: ['weekdays'],
        watch: {
            weekdays() {
                this.updateTimeExpression();
            }
        },
        data() {
            return {
                sunrise: true,
                sunBefore: false,
                sunMinute: 0
            };
        },
        methods: {
            updateTimeExpression() {
                var sunTimeEncoded = 'S' + (this.sunrise ? 'R' : 'S') + (this.sunBefore ? '-' : '') + (this.sunMinute || 0);
                var sunExpression = [sunTimeEncoded, 0, '*', '*', this.weekdays].join(' ');
                this.$store.dispatch('updateTimeExpression', sunExpression);
            }
        },
        mounted() {
            if (this.timeExpression) {
                let current = this.timeExpression.match(/^S([SR])(-?)([0-9]+)/);
                if (current) {
                    this.sunrise = current[1] == 'R';
                    this.sunBefore = current[2];
                    this.sunMinute = current[3];
                }
            }
            this.updateTimeExpression();
        },
        computed: mapState(['timeExpression']),
    };
</script>
