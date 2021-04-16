<template>
    <modal class="account-limits-modal"
        :header="$t('Your account limits')">
        <loading-cover :loading="fetching">
            <div v-if="limits">
                <dl>
                    <dt>{{ $t('I/O Devices') }}</dt>
                    <dd>
                        <account-limit-progressbar :limit="limits.ioDevice"
                            :value="relationsCount.ioDevices"></account-limit-progressbar>
                    </dd>
                    <dt>{{ $t('Access Identifiers') }}</dt>
                    <dd>
                        <account-limit-progressbar :limit="limits.accessId"
                            :value="relationsCount.accessIds"></account-limit-progressbar>
                    </dd>
                    <dt>{{ $t('Client’s Apps') }}</dt>
                    <dd>
                        <account-limit-progressbar :limit="limits.clientApp"
                            :value="relationsCount.clientApps"></account-limit-progressbar>
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
                <div v-if="apiRateStatus">
                    <h4>{{ $t('API rate limits') }}</h4>
                    <div class="row">
                        <div class="col-xs-12 api-rate-limit-progress">
                            <div class="well well-sm no-margin">
                                <div class="clearfix">
                                    <account-limit-progressbar :limit="apiRateStatus.limit"
                                        :value="apiRateStatus.requests">
                                        <span v-if="apiRateStatus.seconds === 60">
                                            {{ $t('{requests} out of {limit} / min', apiRateStatus) }}
                                        </span>
                                        <span v-else-if="apiRateStatus.seconds === 3600">
                                            {{ $t('{requests} out of {limit} / h', apiRateStatus) }}
                                        </span>
                                        <span v-else-if="apiRateStatus.seconds === 86400">
                                            {{ $t('{requests} out of {limit} / day', apiRateStatus) }}
                                        </span>
                                        <span v-else>
                                            {{ $t('{requests} out of {limit} / {seconds} sec.', apiRateStatus) }}
                                        </span>
                                    </account-limit-progressbar>
                                </div>
                            </div>
                            <p class="text-right text-muted small"
                                v-if="apiRateStatus.requests > 0">
                                {{ $t('Next limit renewal: {date}', { date: apiRateStatusReset }) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </loading-cover>
        <div slot="footer">
            <a @click="fetchLimits()"
                class="cancel small">
                <i class="pe-7s-refresh-2"></i>
            </a>
            <a @click="$emit('confirm')"
                class="confirm">
                <i class="pe-7s-check"></i>
            </a>
        </div>
    </modal>
</template>

<script>
    import AccountLimitProgressbar from "./account-limit-progressbar";
    import moment from "moment";

    export default {
        components: {AccountLimitProgressbar},
        props: ['user'],
        data() {
            return {
                fetching: false,
                limits: undefined,
                relationsCount: undefined,
                apiRateStatus: undefined,
            };
        },
        mounted() {
            this.fetchLimits();
        },
        methods: {
            fetchLimits() {
                if (this.fetching) {
                    return;
                }
                this.fetching = true;
                this.$http.get('users/current?include=limits,relationsCount').then(response => {
                    this.limits = {
                        ...response.body.limits,
                        apiRateLimit: response.body.apiRateLimit,
                    };
                    this.relationsCount = response.body.relationsCount;
                    if (this.limits.apiRateLimit) {
                        this.apiRateStatus = {
                            requests: this.limits.apiRateLimit.rule.limit - this.limits.apiRateLimit.status.remaining,
                            limit: this.limits.apiRateLimit.rule.limit,
                            seconds: this.limits.apiRateLimit.rule.period,
                        };
                    }
                }).finally(() => this.fetching = false);
            },
        },
        computed: {
            apiRateStatusReset() {
                if (!this.apiRateStatus) {
                    return;
                }
                return moment.unix(this.limits.apiRateLimit.status.reset).format('LTS L');
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .account-limits-modal {
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

        .api-rate-limit-progress {
            .progress-bar {
                min-width: 10em;
            }
            p.text-muted {
                margin-top: 3px;
            }
        }
    }
</style>
