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
        <dl v-if="channel.state.on !== undefined">
            <dd>{{ $t('On') }}</dd>
            <dt>{{ $t(channel.state.on ? 'yes' : 'no') }}</dt>
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
            }
        },
        beforeDestroy() {
            clearInterval(this.timer);
        }
    };
</script>
