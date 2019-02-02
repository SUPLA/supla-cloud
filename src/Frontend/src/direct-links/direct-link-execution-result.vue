<template>
    <div v-if="directLink">
        <div class="container text-center">
            <div v-if="failureReason">
                <h1 class="nocapitalize">{{ $t(failureReason) }}</h1>
                <div v-if="failureReason == 'directLinkExecutionFailureReason_invalidActionParameters'">
                    <i class="pe-7s-edit"
                        style="font-size: 160px"></i>
                    <p>{{ $t('This direct link requires further parameters in order to be executed properly. See the examples below to get an idea of what you might do.')}}</p>
                    <div style="font-size: 1.3em">
                        <div v-if="action == 'REVEAL_PARTIALLY'">
                            <div><code>{{currentUrl}}?percentage=40</code></div>
                            <div><code>{{currentUrl}}?percentage=60</code></div>
                        </div>
                        <div v-if="action == 'SET_RGBW_PARAMETERS'">
                            <div v-if="['DIMMER', 'DIMMERANDRGBLIGHTING'].indexOf(directLink.subject.function.name) !== -1">
                                <div><code>{{currentUrl}}?brightness=40</code></div>
                                <div><code>{{currentUrl}}?brightness=100</code></div>
                            </div>
                            <div v-if="['RGBLIGHTING', 'DIMMERANDRGBLIGHTING'].indexOf(directLink.subject.function.name) !== -1">
                                <div><code>{{currentUrl}}?color=0xFF6600</code></div>
                                <div><code>{{currentUrl}}?color_brightness=40&color=16711935</code></div>
                                <div><code>{{currentUrl}}?color_brightness=60&color=0x00FF00</code></div>
                                <div><code>{{currentUrl}}?color=random</code></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <i class="pe-7s-close-circle"
                        style="font-size: 160px"></i>
                    <h5>{{ $t('Check the direct link you used and try again.') }}</h5>
                </div>
            </div>
            <div v-else
                class="form-group">
                <div v-if="!action">
                    <h1>{{ directLink.caption }}</h1>

                    <div v-if="directLink.state"
                        class="form-group">
                        <h3>{{ stateCaption }}</h3>
                        <div class="form-group">
                            <function-icon :model="{functionId: directLink.subject.functionId, state: directLink.state}"
                                width="100"></function-icon>
                            <channel-state-table :state="directLink.state"></channel-state-table>
                        </div>
                        <button type="button"
                            :disabled="refreshingState"
                            @click="refreshState()"
                            class="btn btn-xs btn-default">
                            <i class="pe-7s-refresh-2"></i>
                            {{ $t('Refresh') }}
                        </button>
                    </div>

                    <div style="max-width: 600px; margin: 0 auto"
                        class="text-left">
                        <div v-for="allowedAction in directLink.allowedActions"
                            class="form-group">
                            <div class="flex-left-full-width">
                                <pre><code>{{ currentUrl }}/{{ allowedAction.nameSlug }}</code></pre>
                                <div class="btn-group">
                                    <copy-button :text="currentUrl"></copy-button>
                                    <button class="btn btn-success"
                                        type="button"
                                        v-if="['READ', 'SET_RGBW_PARAMETERS', 'REVEAL_PARTIALLY'].indexOf(allowedAction.name) === -1"
                                        @click="executeAction(allowedAction)">
                                        Wykonaj
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else-if="directLink.state">
                    <h1>{{ stateCaption }}</h1>
                    <div class="form-group">
                        <function-icon :model="{functionId: directLink.subject.functionId, state: directLink.state}"
                            width="100"></function-icon>
                        <channel-state-table :state="directLink.state"></channel-state-table>
                    </div>
                    <button type="button"
                        :disabled="refreshingState"
                        @click="refreshState()"
                        class="btn btn-xs btn-default">
                        <i class="pe-7s-refresh-2"></i>
                        {{ $t('Refresh') }}
                    </button>
                </div>
                <div v-else>
                    <h1 class="nocapitalize">{{ $t('Direct link has been executed') }}</h1>
                    <i class="pe-7s-rocket"
                        style="font-size: 160px"></i>
                </div>
            </div>
            <button type="button"
                class="btn btn-link"
                @click="jsonHintVisible = true"
                v-if="!jsonHintVisible">
                <i class="pe-7s-help1"></i>
            </button>
            <div class="well"
                style="max-width: 600px; margin: 0 auto"
                v-if="jsonHintVisible">
                <h3 class="no-margin-top">{{ $t('Where is the JSON?') }}</h3>
                <p>{{ $t('The link result is presented as a website respone, because you (or your browser) asked for HTML output.') }}</p>
                <p>{{ $t('If you want to use the link programmatically, add an appropriate request header to obtain an output you can easily parse.') }}</p>
                <pre><code>Accept: application/json</code></pre>
            </div>
        </div>
        <login-footer v-if="action !== 'READ'"></login-footer>
    </div>
</template>

<script type="text/babel">
    import LoginFooter from "../login/login-footer";
    import FunctionIcon from "../channels/function-icon";
    import ChannelStateTable from "../channels/channel-state-table";
    import {channelTitle} from "../common/filters";
    import CopyButton from "../common/copy-button";

    export default {
        props: ['failureReason', 'action'],
        components: {CopyButton, ChannelStateTable, FunctionIcon, LoginFooter},
        data() {
            return {
                directLink: undefined,
                jsonHintVisible: false,
                refreshingState: false
            };
        },
        mounted() {
            this.directLink = window.directLink;
            if (!this.action && this.directLink.allowedActions.filter(f => f.name === 'READ').length) {
                this.refreshState();
            }
        },
        methods: {
            refreshState() {
                this.refreshingState = true;
                this.$http.get(this.readStateUrl)
                    .then(response => this.directLink.state = response.body)
                    .finally(() => this.refreshingState = false);
            },
            executeAction(action) {
                this.$http.get(this.currentUrl + '/' + action.nameSlug);
            }
        },
        computed: {
            currentUrl() {
                return window.location.protocol + "//" + window.location.host + window.location.pathname;
            },
            readStateUrl() {
                return this.currentUrl.indexOf('/read') > 0 ? this.currentUrl : this.currentUrl + '/read';
            },
            stateCaption() {
                return channelTitle(this.directLink.subject, this, false).replace(/^ID[0-9]+/, '');
            }
        }
    };
</script>
