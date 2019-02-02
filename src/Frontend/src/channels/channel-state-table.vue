<template>
    <div v-if="currentState">
        <dl v-if="currentState.temperature !== undefined">
            <dd>{{ $t('Temperature') }}</dd>
            <dt>{{ currentState.temperature }}&deg;C</dt>
        </dl>
        <dl v-if="currentState.humidity !== undefined">
            <dd>{{ $t('Humidity') }}</dd>
            <dt>{{ currentState.humidity }}%</dt>
        </dl>
        <dl v-if="currentState.depth !== undefined">
            <dd>{{ $t('Depth') }}</dd>
            <dt>{{ currentState.depth }}m</dt>
        </dl>
        <dl v-if="currentState.distance !== undefined">
            <dd>{{ $t('Distance') }}</dd>
            <dt>{{ currentState.distance }}m</dt>
        </dl>
        <dl v-if="currentState.color_brightness">
            <dd>{{ $t('Color') }}</dd>
            <dt>
                <span class="rgb-color-preview"
                    :style="{'background-color': cssColor(currentState.color)}"></span>
            </dt>
            <dd>{{ $t('Color brightness') }}</dd>
            <dt>{{currentState.color_brightness}}%</dt>
        </dl>
        <dl v-if="currentState.brightness">
            <dd>{{ $t('Brightness') }}</dd>
            <dt>{{currentState.brightness}}%</dt>
        </dl>
        <dl v-if="currentState.is_calibrating">
            <dd>{{ $t('Calibration') }}</dd>
            <dt></dt>
        </dl>
        <dl v-if="currentState.shut !== undefined">
            <dd>{{ $t('Percentage of closing') }}</dd>
            <dt>{{currentState.shut}}%</dt>
        </dl>
    </div>
</template>

<script>
    export default {
        props: ['channel', 'state'],
        data() {
            return {
                timer: undefined,
            };
        },
        mounted() {
            if (!this.state) {
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
            currentState() {
                return this.state || this.channel.state;
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
