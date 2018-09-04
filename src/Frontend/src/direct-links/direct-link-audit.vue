<template>
    <loading-cover :loading="!auditEntries">
        <div class="list-group"
            v-if="auditEntries">
            <div :class="'list-group-item list-group-item-' + (entry.event.name == 'DIRECT_LINK_EXECUTION' ? 'success' : 'danger')"
                v-for="entry in auditEntries">
                <div v-if="entry.event.name == 'DIRECT_LINK_EXECUTION'">
                    {{ $t('Executed action')}}
                    <strong>{{ functionLabel(entry.textParam) }}</strong>
                </div>
                <div v-else>
                    {{ entry.textParam }}
                </div>
                <div class="text-muted small">
                    {{ entry.createdAt | moment('LLL') }}
                    {{ $t('from IP') }}
                    {{ entry.ipv4 | intToIp }}
                </div>
            </div>
        </div>
    </loading-cover>
</template>

<script>
    export default {
        props: ['directLink'],
        data() {
            return {
                auditEntries: undefined,
            };
        },
        mounted() {
            this.$http.get(`direct-links/${this.directLink.id}/audit`).then(response => {
                this.auditEntries = response.body;
            });
        },
        methods: {
            functionLabel(functionId) {
                return this.$t(this.possibleActions.filter(action => action.id == functionId)[0].caption);
            }
        },
        computed: {
            possibleActions() {
                if (this.directLink) {
                    return [{
                        id: 1000,
                        name: 'READ',
                        caption: 'Read',
                        nameSlug: 'read'
                    }].concat(this.directLink.subject.function.possibleActions);
                }
            },
        }
    };
</script>
