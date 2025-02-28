<template>
    <div>
        <a class="btn btn-danger" @click="deleteConfirm = true" v-if="canDelete">
            {{ $t('Delete') }}
        </a>

        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteChannel()"
            @cancel="deleteConfirm = false"
            :header="deletingSubdevice ? $t('Are you sure you want to delete this subdevice?') : $t('Are you sure you want to delete this channel?')"
            :loading="loading">
        </modal-confirm>
        <dependencies-warning-modal
            header-i18n="Some features depend on this channel"
            deleting-header-i18n="The items below depend on the deleted channels, so they will be deleted, too."
            removing-header-i18n="Reference to the deleted channels will be removed from the items below."
            v-if="dependenciesThatPreventsDeletion"
            :dependencies="dependenciesThatPreventsDeletion"
            @confirm="deleteChannel('no')"
            :loading="loading"
            @cancel="dependenciesThatPreventsDeletion = undefined"/>
    </div>
</template>

<script>
    import DependenciesWarningModal from "@/channels/dependencies/dependencies-warning-modal.vue";
    import {successNotification} from "@/common/notifier";
    import {mapState, mapStores} from "pinia";
    import {useChannelsStore} from "@/stores/channels-store";
    import {useDevicesStore} from "@/stores/devices-store";

    export default {
        components: {DependenciesWarningModal},
        props: {
            channel: Object,
            deletingSubdevice: Boolean,
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
                return this.$http.delete(`channels/${this.channel.id}?safe=${safe}`, {skipErrorHandler: [409]})
                    .then(() => this.channelsStore.refetchAll())
                    .then(() => {
                        this.dependenciesThatPreventsDeletion = undefined;
                        successNotification(
                            this.$t('Successful'),
                            this.deletingSubdevice ? this.$t('The subdevice has been deleted.') : this.$t('The channel has been deleted.')
                        );
                        if (this.$route.name !== 'device.channels') {
                            this.$router.push({name: 'device.channels', params: {id: this.channel.iodeviceId}});
                        }
                    })
                    .catch(({body, status}) => {
                        if (status === 409) {
                            if (this.deletingSubdevice) {
                                const existingDependencies = Object.keys(body.dependencies)
                                    .filter((key) => body.dependencies[key].length > 0);
                                if (!existingDependencies.length) {
                                    return this.deleteChannel('no');
                                }
                                body.channelsToRemove = undefined;
                            }
                            this.dependenciesThatPreventsDeletion = body;
                        }
                    })
                    .finally(() => this.loading = this.deleteConfirm = false);
            }
        },
        computed: {
            canDelete() {
                return this.channel.deletable;
            },
            device() {
                return this.allDevices[this.channel.iodeviceId];
            },
            ...mapStores(useChannelsStore),
            ...mapState(useDevicesStore, {allDevices: 'all'}),
        }
    };
</script>
