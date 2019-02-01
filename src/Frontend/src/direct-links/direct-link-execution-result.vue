<template>
    <div>
        <div class="container text-center">
            <h1 class="nocapitalize"
                v-if="failureReason">{{ $t(failureReason) }}</h1>
            <h1 class="nocapitalize"
                v-else>{{ $t('Direct link has been executed') }}</h1>
            <div v-if="failureReason == 'directLinkExecutionFailureReason_invalidActionParameters'">
                <i class="pe-7s-edit"
                    style="font-size: 160px"></i>
                <p>{{ $t('This direct link requires further parameters in order to be executed properly. See the examples below to get an idea of what you might do.')}}</p>
                <div style="font-size: 1.3em">
                    <div v-if="action == 'SET_RGBW_PARAMETERS'">
                        <div><code>{{currentUrl}}?brightness=40</code></div>
                        <div><code>{{currentUrl}}?brightness=100</code></div>
                    </div>
                </div>
            </div>
        </div>
        <login-footer></login-footer>
    </div>
</template>

<!--icon="pe-7s-edit">-->
<!--<div style="font-size: 1.3em">-->
<!--{% if action.name == 'REVEAL_PARTIALLY' %}-->
<!--<div><code>{{ app.request.schemeAndHttpHost }}{{ app.request.pathinfo }}?percentage=40</code></div>-->
<!--<div><code>{{ app.request.schemeAndHttpHost }}{{ app.request.pathinfo }}?percentage=60</code></div>-->
<!--{% elseif action.name == 'SET_RGBW_PARAMETERS' %}-->
<!--{% if directLink.subject.function.name in ['DIMMER', 'DIMMERANDRGBLIGHTING'] %}-->
<!--<div><code>{{ app.request.schemeAndHttpHost }}{{ app.request.pathinfo }}?brightness=40</code></div>-->
<!--<div><code>{{ app.request.schemeAndHttpHost }}{{ app.request.pathinfo }}?brightness=100</code></div>-->
<!--{% endif %}-->
<!--{% if directLink.subject.function.name in ['RGBLIGHTING', 'DIMMERANDRGBLIGHTING'] %}-->
<!--<div><code>{{ app.request.schemeAndHttpHost }}{{ app.request.pathinfo }}?color=0xFF6600</code></div>-->
<!--<div>-->
<!--<code>{{ app.request.schemeAndHttpHost }}{{ app.request.pathinfo }}?color_brightness=40&color=16711935</code>-->
<!--</div>-->
<!--<div>-->
<!--<code>{{ app.request.schemeAndHttpHost }}{{ app.request.pathinfo }}?color_brightness=60&color=0x00FF00</code>-->
<!--</div>-->
<!--<div><code>{{ app.request.schemeAndHttpHost }}{{ app.request.pathinfo }}?color=random</code></div>-->
<!--{% endif %}-->
<!--{% endif %}-->
<!--</div>-->
<!--</error-page>-->
<!--{% else %}-->
<!--<error-page header-i18n="{{ failureReason.value }}"-->
<!--message-i18n="Check the direct link you used and try again."-->
<!--icon="pe-7s-close-circle">-->
<!--</error-page>-->
<!--{% endif %}-->
<!--{% elseif details %}-->
<!--<error-page>-->
<!--<function-icon :model='{functionId: {{ directLink.subject.function.id }}, state: {{ details|json_encode|raw }}}'-->
<!--width="100"></function-icon>-->
<!--<channel-state-table :single-state='{{ details|json_encode|raw }}'></channel-state-table>-->
<!--</error-page>-->
<!--{% else %}-->
<!--<error-page header-i18n="Direct link has been executed"-->
<!--icon="pe-7s-rocket">-->
<!--</error-page>-->
<!--{% endif %}-->
<!--</div>-->

<script type="text/babel">
    import LoginFooter from "../login/login-footer";

    export default {
        props: ['failureReason', 'action'],
        components: {LoginFooter},
        computed: {
            currentUrl() {
                return window.location.protocol + "://" + window.location.host + "/" + window.location.pathname;
            }
        }
    };
</script>
