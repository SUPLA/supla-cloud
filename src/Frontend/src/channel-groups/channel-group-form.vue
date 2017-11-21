<template>
    <div>
        <h2>{{ $t(channelGroup.id ? 'Channel group ID' + channelGroup.id : 'New channel group') }}</h2>
        <form @submit.prevent="saveChannelGroup()">
            <input type="text"
                v-model="channelGroup.caption">
            <button type="submit">Zapisz</button>
        </form>
    </div>
</template>

<script>
    export default {
        props: ['channelGroup'],
        methods: {
            saveChannelGroup() {
                this.$http.post('channel-groups', this.channelGroup);
            }
        }
    };
</script>
