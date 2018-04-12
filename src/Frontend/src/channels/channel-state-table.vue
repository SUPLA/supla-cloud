<template>
    <div v-if="channel.state">
        <dl v-if="channel.state.temperature !== undefined">
            <dd>{{ $t('Temperature') }}</dd>
            <dt>{{ channel.state.temperature }}&deg;C</dt>
        </dl>
        <dl v-if="channel.state.humidity !== undefined">
            <dd>{{ $t('Humidity') }}</dd>
            <dt>{{ channel.state.humidity }}%</dt>
        </dl>
        <dl v-if="channel.state.depth !== undefined">
            <dd>{{ $t('Depth') }}</dd>
            <dt>{{ channel.state.depth }}m</dt>
        </dl>
        <dl v-if="channel.state.distance !== undefined">
            <dd>{{ $t('Distance') }}</dd>
            <dt>{{ channel.state.distance }}m</dt>
        </dl>
        <dl v-if="channel.state.color_brightness">
            <dd>{{ $t('Color') }}</dd>
            <dt>
                <span class="rgb-color-preview"
                    :style="{'background-color': cssColor(channel.state.color)}"></span>
            </dt>
            <dd>{{ $t('Color brightness') }}</dd>
            <dt>{{channel.state.color_brightness}}%</dt>
        </dl>
        <dl v-if="channel.state.brightness">
            <dd>{{ $t('Brightness') }}</dd>
            <dt>{{channel.state.brightness}}%</dt>
        </dl>
        <dl v-if="channel.state.is_calibrating">
            <dd>{{ $t('Calibration') }}</dd>
            <dt></dt>
        </dl>
        <dl v-if="channel.state.shut !== undefined">
            <dd>{{ $t('Percentage of closing') }}</dd>
            <dt>{{channel.state.shut}}%</dt>
        </dl>
    </div>
</template>

<script>
    export default {
        props: ['channel'],
        data() {
            return {
                timer: undefined,
            };
        },
        mounted() {
            this.fetchState();
            this.timer = setInterval(() => this.fetchState(), 7000);
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
