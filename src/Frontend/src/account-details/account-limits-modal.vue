<template>
    <modal class="account-limits-modal"
        @confirm="$emit('confirm')"
        :header="$t('Your account limits')">
        <loading-cover :loading="!limits">
            <div v-if="limits">
                <dl>
                    <dt>{{ $t('Access Identifiers') }}</dt>
                    <dd>
                        <account-limit-progressbar :limit="limits.accessId"
                            :value="relationsCount.accessIds"></account-limit-progressbar>
                    </dd>
                    <dt>{{ $t('Channel groups') }}</dt>
                    <dd>
                        <account-limit-progressbar :limit="limits.channelGroup"
                            :value="relationsCount.channelGroups"></account-limit-progressbar>
                    </dd>
                    <!--<dt>{{ $t('Channels in channel group') }}</dt>-->
                    <!--<dd>{{ limits.channelGroup }}</dd>-->
                    <dt>{{ $t('Locations') }}</dt>
                    <dd>
                        <account-limit-progressbar :limit="limits.location"
                            :value="relationsCount.locations"></account-limit-progressbar>
                    </dd>
                    <dt>{{ $t('Schedules') }}</dt>
                    <dd>
                        <account-limit-progressbar :limit="limits.schedule"
                            :value="relationsCount.schedules"></account-limit-progressbar>
                    </dd>
                    <dt>{{ $t('Direct links') }}</dt>
                    <dd>
                        <account-limit-progressbar :limit="limits.directLink"
                            :value="relationsCount.directLinks"></account-limit-progressbar>
                    </dd>
                    <dt>{{ $t('OAuth apps') }}</dt>
                    <dd>
                        <account-limit-progressbar :limit="limits.oauthClient"
                            :value="relationsCount.apiClients"></account-limit-progressbar>
                    </dd>
                </dl>
                <h4>{{ $t('API rate limits') }}</h4>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="well well-sm clearfix">
                            <account-limit-progressbar :limit="limits.apiRateLimit.rule.limit"
                                :value="limits.apiRateLimit.rule.limit - limits.apiRateLimit.status.remaining"></account-limit-progressbar>
                        </div>
                    </div>
                </div>
                <dl>
                    <dt>{{ $t('Requests limit') }}</dt>
                    <dd>
                        <span v-if="limits.apiRateLimit.rule.period === 60">
                            {{ $t('{requests} per minute', {requests: limits.apiRateLimit.rule.limit }) }}
                        </span>
                        <span v-else-if="limits.apiRateLimit.rule.period === 3600">
                            {{ $t('{requests} per hour', {requests: limits.apiRateLimit.rule.limit }) }}
                        </span>
                        <span v-else-if="limits.apiRateLimit.rule.period === 86400">
                            {{ $t('{requests} per day', {requests: limits.apiRateLimit.rule.limit }) }}
                        </span>
                        <span v-else>
                            {{ $t('{requests} per {seconds} sec.', {requests: limits.apiRateLimit.rule.limit, seconds: limits.apiRateLimit.rule.period }) }}
                        </span>
                    </dd>
                    <dt>{{ $t('Remaining') }}</dt>
                    <dd>
                        <span v-if="limits.apiRateLimit.status.remaining != limits.apiRateLimit.rule.limit">
                            {{ $t('{remainingRequests} to {date}', {remainingRequests: limits.apiRateLimit.status.remaining, date: apiRateStatusReset }) }}
                        </span>
                        <span v-else>
                            {{ $t('No current API usages') }}
                        </span>
                    </dd>
                </dl>
            </div>
        </loading-cover>
    </modal>
</template>

<script>
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import AccountLimitProgressbar from "./account-limit-progressbar";

    export default {
        components: {AccountLimitProgressbar, ButtonLoadingDots},
        props: ['user'],
        data() {
            return {
                limits: undefined,
                relationsCount: undefined,
            };
        },
        mounted() {
            this.$http.get('users/current?include=limits,relationsCount').then(response => {
                this.limits = {
                    ...response.body.limits,
                    apiRateLimit: response.body.apiRateLimit,
                };
                this.relationsCount = response.body.relationsCount;
            });
        },
        computed: {
            apiRateStatusReset() {
                if (!this.limits) {
                    return;
                }
                return moment.unix(this.limits.apiRateLimit.status.reset).format('LTS L');
            }
        }
    };
</script>

<style lang="scss"
    scoped>
    @import "../styles/variables";

    dl {
        display: flex;
        flex-flow: row wrap;

        dt {
            padding: 4px 4px;
            text-align: right;
        }
        dd {
            flex-grow: 1;
            margin: 0;
            padding: 4px 4px;
        }
        dt, dd {
            flex-basis: 50%;
            border-bottom: 1px solid $supla-grey-light;
            &:last-of-type {
                border: 0;
            }
        }
    }
</style>
