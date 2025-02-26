<template>
    <div class="container">
        <loading-cover :loading="loading">
            <p>
                {{ $t('Your device supports extending its behavior by executing any desired action on any device that you own. Depending on the firmware, it may support different triggers (often limited by the hardware).') }}
            </p>
            <pending-changes-page
                @cancel="loadActionTriggers()"
                @save="saveChanges()"
                :is-pending="hasPendingChanges">
                <div class="form-group"></div>
                <div class="row" v-if="actionTriggers">
                    <div :class="{'col-sm-6 col-sm-offset-3': actionTriggers.length === 1, 'col-sm-6': actionTriggers.length > 1}"
                        v-for="(actionTrigger, index) in actionTriggers"
                        :key="actionTrigger.id">
                        <h4 v-if="actionTriggers.length > 1">
                            {{ $t('Action trigger no. #{index}', {index: index + 1}) }}
                        </h4>
                        <action-trigger-panel :channel="actionTrigger"
                            @change="hasPendingChanges = true"></action-trigger-panel>
                    </div>
                </div>
            </pending-changes-page>
        </loading-cover>
    </div>
</template>

<script>
    import ActionTriggerPanel from "@/channels/action-trigger/action-trigger-panel";
    import PendingChangesPage from "@/common/pages/pending-changes-page";
    import ChannelFunction from "@/common/enums/channel-function";
    import {mapStores} from "pinia";
    import {useChannelsStore} from "@/stores/channels-store";
    import {deepCopy} from "@/common/utils";

    export default {
        components: {PendingChangesPage, ActionTriggerPanel},
        props: {
            subject: Object,
        },
        data() {
            return {
                loading: false,
                hasPendingChanges: false,
                actionTriggers: [],
            };
        },
        mounted() {
            this.actionTriggers = deepCopy(this.actionTriggersForChannel);
        },
        methods: {
            loadActionTriggers() {
                this.loading = true;
                const promises = this.actionTriggers.map(trigger => this.channelsStore.fetchChannel(trigger.id));
                Promise.all(promises).then(() => {
                    this.actionTriggers = deepCopy(this.actionTriggersForChannel);
                    this.hasPendingChanges = false;
                    this.loading = false;
                })
            },
            saveChanges() {
                this.loading = true;
                const promises = this.actionTriggers
                    .map((actionTrigger) => this.$http.put(`channels/${actionTrigger.id}?safe=1`, actionTrigger));
                Promise.all(promises)
                    .then(() => this.loadActionTriggers())
                    .finally(() => this.loading = false);
            }
        },
        computed: {
            ...mapStores(useChannelsStore),
            actionTriggersForChannel() {
                return this.channelsStore.list
                    .filter((ch) => ch.functionId === ChannelFunction.ACTION_TRIGGER)
                    .filter((ch) => ch.config.relatedChannelId === this.subject.id || this.subject.id === ch.id);
            },
        }
    }
</script>
