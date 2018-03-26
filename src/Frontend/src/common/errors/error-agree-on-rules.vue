<template>
    <div class="container text-center">
        <h1>{{ $t('You need to agree on rules') }}</h1>
        <i class="pe-7s-note2"
            style="font-size: 160px"></i>
        <h5>{{ $t('Some of our policies has been changed and you need to agree on them.') }}</h5>
        <div class="checkbox">
            <input type="checkbox">
            <component :is="regulationsText"
                @click="rulesShown = true"></component>
        </div>
        <div class="form-group">

            <button class="btn btn-yellow">
                <i class="pe-7s-back"></i>
                Disagree, take me out of here
            </button>
            <button class="btn btn-green">
                <i class="pe-7s-simple-check"></i>
                Agree, take me to the app
            </button>
        </div>
        <modal
            v-if="rulesShown"
            @confirm="rulesShown = false"
            class="text-left modal-800 display-newlines">
            <div slot
                v-html="rules"></div>
        </modal>
    </div>
</template>

<script>
    import Vue from "vue";

    export default {
        data() {
            return {
                rules: '',
                rulesShown: false
            };
        },
        mounted() {
            if (this.$user.agreements.rules) {
                this.$router.push('/');
            }
            const rulesLang = Vue.config.external.locale == 'pl' ? 'pl' : 'en';
            this.$http.get(`/rules/rules_${rulesLang}.html`).then(response => this.rules = response.body);
        },
        computed: {
            regulationsText() {
                const template = '<span>I have read the <a @click="$emit(\'click\')">rules</a> and <router-link :to="{name: \'locations\'}">blabla</router-link></span>';
                return {template};
            }
        }
    };
</script>
