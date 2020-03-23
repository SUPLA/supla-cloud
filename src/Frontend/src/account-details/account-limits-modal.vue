<template>
    <modal class="account-limits-modal"
        @confirm="$emit('confirm')"
        :header="$t('Your account limits')">
        <loading-cover :loading="!limits">
            <div v-if="limits">
                <dl>
                    <dt>{{ $t('Access Identifiers') }}</dt>
                    <dd>{{ limits.accessId }}</dd>
                    <dt>{{ $t('Channel groups') }}</dt>
                    <dd>{{ limits.channelGroup }}</dd>
                    <dt>{{ $t('Channels in channel group') }}</dt>
                    <dd>{{ limits.channelGroup }}</dd>
                    <dt>{{ $t('Locations') }}</dt>
                    <dd>{{ limits.location }}</dd>
                    <dt>{{ $t('Schedules') }}</dt>
                    <dd>{{ limits.schedule }}</dd>
                    <dt>{{ $t('Direct links') }}</dt>
                    <dd>{{ limits.directLink }}</dd>
                    <dt>{{ $t('OAuth apps') }}</dt>
                    <dd>{{ limits.oauthClient }}</dd>
                </dl>
                <h4>{{ $t('API rate limits') }}</h4>
                <dl>
                    <dt>{{ $t('Requests limit') }}</dt>
                    <dd>{{ limits.apiRateLimit.rule.limit }} / {{ apiRateLimitPeriodLabel }}</dd>
                    <dt>{{ $t('Remaining') }}</dt>
                    <dd>
                        <span v-if="limits.apiRateLimit.status.remaining != limits.apiRateLimit.rule.limit">
                            {{ limits.apiRateLimit.status.remaining }} do {{ apiRateStatusReset }}
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

    export default {
        components: {ButtonLoadingDots},
        props: ['user'],
        data() {
            return {
                limits: undefined,
            };
        },
        mounted() {
            this.$http.get('users/current?include=limits').then(response => {
                this.limits = {
                    ...response.body.limits,
                    apiRateLimit: response.body.apiRateLimit,
                };
            });
        },
        computed: {
            apiRateLimitPeriodLabel() {
                if (!this.limits) {
                    return;
                }
                const period = this.limits.apiRateLimit.rule.period;
                if (period === 60) {
                    return this.$t('per_minute_unit');
                } else if (period === 3600) {
                    return this.$t('per_hour_unit');
                } else if (period === 86400) {
                    return this.$t('per_24h_unit');
                } else {
                    return period + ' ' + this.$t('sec.');
                }
            },
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
