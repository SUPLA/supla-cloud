<template>
    <div>
        <div v-if="this.clientApps" class="row">
            <div class="col-lg-4"
                v-for="app in clientApps">
                <square-link class="clearfix">
                    <h3>{{app.name}}</h3>
                    <dl>
                        <dd>Zarejestrowano</dd>
                        <dt>{{ app.regDate | moment("LLL") }} z adresu {{ app.regIpv4 | intToIp }}</dt>
                        <dd>Ostatnia aktywność</dd>
                        <dt>{{ app.lastAccessDate | moment("LLL") }} z adresu {{ app.lastAccessIpv4 | intToIp }}</dt>
                        <dd>Wersja oprogramowania / protokołu</dd>
                        <dt>{{ app.softwareVersion }} / {{ app.protocolVersion }} </dt>
                    </dl>
                    <div class="separator"></div>
                    <dl>
                        <dd>Identyfikator dostępu</dd>
                        <dt>{{ app.accessId.caption }} </dt>
                    </dl>
                    <span class="badge pull-right">{{ app.enabled ? 'Aktywne' : 'Nieaktywne' }}</span>
                </square-link>
            </div>
        </div>
        <loader-dots v-else></loader-dots>
    </div>
</template>

<script>
    import LoaderDots from "../common/loader-dots.vue";
    import SquareLink from "../common/square-link.vue";

    export default {
        components: {LoaderDots, SquareLink},
        data() {
            return {
                clientApps: undefined,
            };
        },
        mounted() {
            this.$http.get('client-apps').then(({body}) => {
                this.clientApps = body;
            });
        }
    };
</script>
