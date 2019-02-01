<template>
    <div>
        <div class="container text-center"
            v-if="directLink">
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
            <div v-else>
                <div v-if="directLink.state">
                    <function-icon :model="{functionId: directLink.subject.functionId, state: directLink.state}"
                        width="100"></function-icon>
                    <channel-state-table :single-state="directLink.state"></channel-state-table>
                </div>
                <div v-else>
                    <h1 class="nocapitalize">{{ $t('Direct link has been executed') }}</h1>
                    <i class="pe-7s-rocket"
                        style="font-size: 160px"></i>
                </div>
            </div>
        </div>
        <login-footer></login-footer>
    </div>
</template>

<script type="text/babel">
    import LoginFooter from "../login/login-footer";
    import FunctionIcon from "../channels/function-icon";
    import ChannelStateTable from "../channels/channel-state-table";

    export default {
        props: ['failureReason', 'action'],
        components: {ChannelStateTable, FunctionIcon, LoginFooter},
        data() {
            return {
                directLink: undefined
            };
        },
        mounted() {
            this.directLink = window.directLink;
        },
        computed: {
            currentUrl() {
                return window.location.protocol + "//" + window.location.host + window.location.pathname;
            }
        }
    };
</script>
