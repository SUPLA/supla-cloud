<template>
    <div>
        <a class="btn btn-danger" @click="deleteConfirm = true" v-if="canDelete">
            {{ $t('Delete') }}
        </a>

        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteChannel()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure you want to delete this channel?')"
            :loading="loading">
        </modal-confirm>
        <dependencies-warning-modal
            header-i18n="Some features depend on this channel"
            deleting-header-i18n="The items below rely on this channel, so they will be deleted."
            removing-header-i18n="Reference to the channel will be removed from the items below."
            v-if="dependenciesThatPreventsDeletion"
            :dependencies="dependenciesThatPreventsDeletion"
            @confirm="deleteChannel('no')"
            @cancel="dependenciesThatPreventsDeletion = undefined"/>
    </div>
</template>

<script>
    import DependenciesWarningModal from "@/channels/dependencies/dependencies-warning-modal.vue";
    import {successNotification} from "@/common/notifier";
    import {mapStores} from "pinia";
    import {useChannelsStore} from "@/stores/channels-store";

    export default {
        components: {DependenciesWarningModal},
        props: {
            channel: Object,
        },
        data() {
            return {
                deleteConfirm: false,
                loading: false,
                dependenciesThatPreventsDeletion: undefined,
            };
        },
        methods: {
            deleteChannel(safe = 'yes') {
                this.loading = true;
                this.$http.delete(`channels/${this.channel.id}?safe=${safe}`, {skipErrorHandler: [409]})
                    .then(() => {
                        this.dependenciesThatPreventsDeletion = undefined;
                        this.channelsStore.refetchAll();
                        successNotification(this.$t('Successful'), this.$t('The channel has been deleted.'));
                        this.$router.push({name: 'device', params: {id: this.channel.iodeviceId}});
                    })
                    .catch(({body, status}) => {
                        if (status === 409) {
                            this.dependenciesThatPreventsDeletion = body;
                        }
                    })
                    .finally(() => this.loading = this.deleteConfirm = false);
            }
        },
        computed: {
            canDelete() {
                return this.channel.conflictDetails || this.channel.iodevice?.channelDeletionAvailable;
            },
            ...mapStores(useChannelsStore),
        }
    };
</script>
