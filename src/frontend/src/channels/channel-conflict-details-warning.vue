<template>
    <div class="alert alert-danger" v-if="channel.conflictDetails">
        <span v-if="channel.conflictDetails.missing">{{ $t('The device skipped this channel during registration.') }}</span>
        <span v-else-if="channel.conflictDetails.type">
            {{
                $t(
                    'The device is trying to register a channel with type {typeCaption} instead of the current one.',
                    {typeCaption: typeCaption}
                )
            }}
        </span>
        <span v-else>{{ $t('The device has conflict and cannot work.') }}</span>
    </div>
</template>

<script>
    export default {
        props: {
            channel: Object,
        },
        data() {
            return {
                channelTypes: undefined,
            }
        },
        mounted() {
            this.$http.get('enum/channel-types').then(response => this.channelTypes = response.body);
        },
        computed: {
            typeCaption() {
                const type = this.channelTypes?.find((type) => type.id == this.channel.conflictDetails.type);
                return type ? this.$t(type.caption) : this.$t('Unknown');
            }
        }
    }
</script>
