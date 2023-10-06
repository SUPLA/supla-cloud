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
                <div class="row"
                    v-if="actionTriggers">
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

    export default {
        components: {PendingChangesPage, ActionTriggerPanel},
        props: {
            subject: Object,
        },
        data() {
            return {
                actionTriggers: undefined,
                loading: false,
                hasPendingChanges: false,
            };
        },
        mounted() {
            this.loadActionTriggers();
        },
        methods: {
            loadActionTriggers() {
                this.loading = true;
                const promises = this.subject.actionTriggersIds.map((actionTriggerId) => this.$http.get(`channels/${actionTriggerId}`));
                if (this.subject.functionId === ChannelFunction.ACTION_TRIGGER) {
                    promises.push(Promise.resolve({body: this.subject}));
                }
                Promise.all(promises).then((responses) => {
                    this.actionTriggers = responses.map((response) => response.body);
                    this.loading = false;
                    this.hasPendingChanges = false;
                });
            },
            saveChanges() {
                this.loading = true;
                const promises = this.actionTriggers
                    .map((actionTrigger) => this.$http.put(`channels/${actionTrigger.id}?safe=1`, actionTrigger));
                Promise.all(promises).finally(() => this.loadActionTriggers());
            }
        }
    }
</script>
