<template>
    <div>
        <div v-if="this.clientApps"
            class="row">
            <div class="col-lg-4 col"
                v-for="app in clientApps">
                <flipper :flipped="app.editing">
                    <square-link class="clearfix"
                        slot="front"
                        @click="app.editing = true">
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
                    <square-link class="yellow"
                        slot="back">
                        <div class="form-group">
                            <label>Nazwa</label>
                            <input type="text"
                                class="form-control"
                                v-model="app.name">
                        </div>

                        <div class="form-group">
                            <label>Identyfikator dostępu</label>
                            <select class="form-control"></select>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"
                                    v-model="app.enabled">
                                Aktywne?</label>
                        </div>
                        <div class="form-group text-right">
                            <button class="btn btn-danger btn-sm pull-left">Usuń</button>
                            <button class="btn btn-default btn-sm"
                                @click="app.editing = false">Anuluj
                            </button>
                            <button class="btn btn-green btn-sm"
                                @click="app.editing = false">OK
                            </button>
                        </div>
                    </square-link>
                </flipper>
            </div>
        </div>
        <loader-dots v-else></loader-dots>
    </div>
</template>

<style scoped>
    .col {
        padding: 10px 5px;
    }
</style>


<script>
    import LoaderDots from "../common/loader-dots.vue";
    import SquareLink from "../common/square-link.vue";
    import Flipper from "../common/flipper.vue";

    export default {
        components: {LoaderDots, SquareLink, Flipper},
        data() {
            return {
                clientApps: undefined,
            };
        },
        mounted() {
            this.$http.get('client-apps').then(({body}) => {
                body.forEach((app) => app.editing = false);
                this.clientApps = body;
            });
        }
    };
</script>
