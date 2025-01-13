<template>
    <modal class="account-limits-modal modal-800"
        :header="$t('Your account limits')">
        <loading-cover :loading="fetching">
            <div v-if="limits">
                <div class="form-group">
                    <ul class="nav nav-tabs">
                        <li :class="{active: currentTab === 'features'}">
                            <a @click="currentTab = 'features'">
                                {{ $t('Feature limits') }}
                            </a>
                        </li>
                        <li :class="{active: currentTab === 'data'}">
                            <a @click="currentTab = 'data'">
                                {{ $t('Data limits') }}
                            </a>
                        </li>
                        <li :class="{active: currentTab === 'api'}"
                            v-if="apiRateStatus">
                            <a @click="currentTab = 'api'">
                                {{ $t('API rate limits') }}
                            </a>
                        </li>
                        <li :class="{active: currentTab === 'other'}">
                            <a @click="currentTab = 'other'">
                                {{ $t('Other') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div v-if="currentTab === 'features'">
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
                        <dt>{{ $t('Scenes') }}</dt>
                        <dd>
                            <account-limit-progressbar :limit="limits.scene"
                                :value="relationsCount.scenes"></account-limit-progressbar>
                        </dd>
                        <dt>{{ $t('Direct links') }}</dt>
                        <dd>
                            <account-limit-progressbar :limit="limits.directLink"
                                :value="relationsCount.directLinks"></account-limit-progressbar>
                        </dd>
                        <dt>{{ $t('Notifications (defined)') }}</dt>
                        <dd>
                            <account-limit-progressbar :limit="limits.pushNotifications"
                                :value="relationsCount.pushNotifications"></account-limit-progressbar>
                        </dd>
                        <dt>{{ $t('Notifications (sent per hour)') }}</dt>
                        <dd>
                            <account-limit-progressbar :limit="limits.pushNotificationsPerHour.limit"
                                :value="limits.pushNotificationsPerHour.limit - limits.pushNotificationsPerHour.left"/>
                        </dd>
                        <dt>{{ $t('Reactions') }}</dt>
                        <dd>
                            <account-limit-progressbar :limit="limits.valueBasedTriggers"
                                :value="relationsCount.valueBasedTriggers"></account-limit-progressbar>
                        </dd>
                        <dt>{{ $t('OAuth apps') }}</dt>
                        <dd>
                            <account-limit-progressbar :limit="limits.oauthClient"
                                :value="relationsCount.apiClients"></account-limit-progressbar>
                        </dd>
                    </dl>
                </div>
                <div v-if="currentTab === 'data'">
                    <p>{{ $t('Some of the data collected for your account will be automatically cleared after certain period of time. Please find the details below.') }}</p>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>{{ $t('Data') }}</th>
                            <th>{{ $t('Will be deleted after') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th scope="row">{{ $t('Electricity meter voltage history') }}</th>
                            <td>{{ $t('{days} days', {days: frontendConfigStore.config.measurementLogsRetention?.em_voltage || 90}) }}</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ $t('Electricity meter voltage aberrations history') }}</th>
                            <td>{{ $t('{days} days', {days: frontendConfigStore.config.measurementLogsRetention?.em_voltage_aberrations || 180}) }}</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ $t('Electricity meter current history') }}</th>
                            <td>{{ $t('{days} days', {days: frontendConfigStore.config.measurementLogsRetention?.em_current || 90}) }}</td>
                        </tr>
                        <tr>
                            <th scope="row">{{ $t('Electricity meter active power history') }}</th>
                            <td>{{ $t('{days} days', {days: frontendConfigStore.config.measurementLogsRetention?.em_power_active || 950}) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="currentTab === 'api'">
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
                                {{ $t('Next limit renewal: {date}', {date: apiRateStatusReset}) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div v-if="currentTab === 'other'">
                    <p>{{ $t('Your channel groups are allowed to have a maximum of {max} channels.', {max: limits.channelPerGroup}) }}</p>
                    <p>{{ $t('Your schedules are allowed to have a maximum of {max} actions.', {max: limits.actionsPerSchedule}) }}</p>
                    <p>{{ $t('Your scenes are allowed to have a maximum of {max} operations.', {max: limits.operationsPerScene}) }}</p>
                </div>
            </div>
        </loading-cover>
        <div v-if="showChangingInfo && currentTab !== 'data'"
            class="alert alert-info my-3">
            <p class="mb-2">
                <i18n-t keypath="Use the {0} server command to change this account's limits. For example:">
                    <code>supla:user:change-limits</code>
                </i18n-t>
            </p>
            <pre><code>docker-compose exec -u www-data supla-cloud php bin/console supla:user:change-limits {{ user.email }}</code></pre>
        </div>
        <template #footer>
            <a @click="fetchLimits()"
                class="cancel small">
                <i class="pe-7s-refresh-2"></i>
            </a>
            <a @click="$emit('confirm')"
                class="confirm">
                <i class="pe-7s-check"></i>
            </a>
        </template>
    </modal>
</template>

<script>
    import AccountLimitProgressbar from "./account-limit-progressbar";
    import Vue from "vue";
    import {DateTime} from "luxon";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";
    import {mapStores} from "pinia";

    export default {
        components: {AccountLimitProgressbar},
        props: ['user'],
        data() {
            return {
                fetching: false,
                limits: undefined,
                relationsCount: undefined,
                apiRateStatus: undefined,
                currentTab: 'features',
                showChangingInfo: !Vue.config.external.actAsBrokerCloud,
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
                return DateTime.fromSeconds(this.limits.apiRateLimit.status.reset).toLocaleString(DateTime.DATETIME_SHORT_WITH_SECONDS);
            },
            ...mapStores(useFrontendConfigStore),
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
