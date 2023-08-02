<template>
    <div v-if="channel.config.resetCountersAvailable">
        <button @click="resetConfirm = true"
            type="button"
            class="btn btn-default btn-block">
            <i class="pe-7s-disk"></i>
            {{ $t('Reset counter') }}
        </button>
        <modal-confirm v-if="resetConfirm"
            class="modal-warning"
            @confirm="resetCounters()"
            @cancel="resetConfirm = false"
            :header="$t('Are you sure you want to reset the counter state?')">
            <p>{{ $t('After this operation, the counter will start counting from 0 regardless of its current state. This action cannot be reverted.') }}</p>
        </modal-confirm>
    </div>
</template>

<script>
    import {successNotification} from "@/common/notifier";

    export default {
        components: {},
        props: ['channel'],
        data: function () {
            return {
                resetConfirm: false,
            };
        },
        methods: {
            resetCounters() {
                this.resetConfirm = false;
                this.$http.patch('channels/' + this.channel.id + '/settings', {action: 'resetCounters'})
                    .then(() => successNotification(this.$t('Success'), this.$t('The counter has been reset.')));
            },
        }
    };
</script>
