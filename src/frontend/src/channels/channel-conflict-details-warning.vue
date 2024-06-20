<template>
    <div class="alert alert-danger" v-if="channel.conflictDetails">
        <span>{{ $t('Conflict') }}:{{ ' ' }}</span>
        <span v-if="channel.conflictDetails.missing">{{ $t('This channel was missing during last device registration attempt.') }}</span>
        <!-- i18n: ['Invalid channel type ({typeCaption} - {typeId}) received in registration.'] -->
        <span v-else-if="channel.conflictDetails.type">
            {{
                $t(
                    'Invalid channel type ({typeCaption} - {typeId}) received in registration.',
                    {typeCaption: typeCaption, typeId: channel.conflictDetails.type}
                )
            }}
        </span>
        <span v-else>{{ $t('The device has channel conflict and cannot work.') }}</span>
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
