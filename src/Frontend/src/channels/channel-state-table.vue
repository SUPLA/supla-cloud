<template>
    <div v-if="state">
        <dl v-if="state.temperature !== undefined">
            <dd>{{ $t('Temperature') }}</dd>
            <dt>{{ state.temperature }}&deg;C</dt>
        </dl>
        <dl v-if="state.humidity !== undefined">
            <dd>{{ $t('Humidity') }}</dd>
            <dt>{{ state.humidity }}%</dt>
        </dl>
        <dl v-if="state.depth !== undefined">
            <dd>{{ $t('Depth') }}</dd>
            <dt>{{ state.depth }}m</dt>
        </dl>
        <dl v-if="state.distance !== undefined">
            <dd>{{ $t('Distance') }}</dd>
            <dt>{{ state.distance }}m</dt>
        </dl>
        <dl v-if="state.color_brightness">
            <dd>{{ $t('Color') }}</dd>
            <dt>
                <span class="rgb-color-preview"
                    :style="{'background-color': cssColor(state.color)}"></span>
            </dt>
            <dd>{{ $t('Color brightness') }}</dd>
            <dt>{{state.color_brightness}}%</dt>
        </dl>
        <dl v-if="state.brightness">
            <dd>{{ $t('Brightness') }}</dd>
            <dt>{{state.brightness}}%</dt>
        </dl>
        <dl v-if="state.is_calibrating">
            <dd>{{ $t('Calibration') }}</dd>
            <dt></dt>
        </dl>
        <dl v-if="state.shut !== undefined">
            <dd>{{ $t('Percentage of closing') }}</dd>
            <dt>{{state.shut}}%</dt>
        </dl>
    </div>
</template>

<script>
    export default {
        props: ['channel', 'singleState'],
        data() {
            return {
                timer: undefined,
            };
        },
        mounted() {
            if (!this.singleState) {
                this.fetchState();
                this.timer = setInterval(() => this.fetchState(), 7000);
            }
        },
        methods: {
            fetchState() {
                this.$http.get(`channels/${this.channel.id}?include=state`).then(({body}) => {
                    this.$set(this.channel, 'state', body.state);
                });
            },
            cssColor(hexStringColor) {
                return hexStringColor.replace('0x', '#');
            }
        },
        computed: {
            state() {
                return this.singleState || this.channel.state;
            }
        },
        beforeDestroy() {
            clearInterval(this.timer);
        }
    };
</script>

<style scoped
    lang="scss">
    dl {
        margin-bottom: 0;
        dd, dt {
            display: inline;
        }
        dt:after {
            display: block;
            content: '';
        }
    }

    .rgb-color-preview {
        display: inline-block;
        height: 10px;
        width: 40px;
        border-radius: 5px;
    }
</style>
