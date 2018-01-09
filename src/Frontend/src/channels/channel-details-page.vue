<template>
    <div>
        HEJ HEJ!
    </div>
</template>

<script>
    export default {
        props: ['channelId'],
        mounted() {
            this.$http.get(`channels/${this.channelId}?include=iodevice,location,type,function`).then(response => {

            });
        }
    };
</script>
