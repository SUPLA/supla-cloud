<template>
    <loading-cover class="container text-center"
        :loading="loading">
        <h1 class="nocapitalize">{{ $t('The Terms and Conditions have changed') }}</h1>
        <i class="pe-7s-note2 error-page-icon"
            style="font-size: 160px"></i>
        <h5>{{ $t('The Terms and Conditions have changed – please read them and accept.') }}</h5>
        <regulations-checkbox :implicit-agreement="true"></regulations-checkbox>
        <div class="form-group">
            <a class="btn btn-yellow"
                @click="disagree()">
                <i class="pe-7s-back"></i>
                {{ $t('I do not agree, get me out of here') }}
            </a>
            <button class="btn btn-green"
                type="submit"
                @click="agree()">
                <i class="pe-7s-check"></i>
                {{ $t('I agree, take me to the app') }}
            </button>
        </div>
    </loading-cover>
</template>

<script>
    import RegulationsCheckbox from "./regulations-checkbox";
    import {mapState} from "pinia";
    import {useCurrentUserStore} from "@/stores/current-user-store";

    export default {
        components: {RegulationsCheckbox},
        data() {
            return {
                loading: false,
            };
        },
        mounted() {
            if (this.userData.agreements.rules) {
                this.$router.push('/');
            }
        },
        methods: {
            agree() {
                this.loading = true;
                this.$http.patch('users/current', {action: 'agree:rules'})
                    .finally(() => window.location.assign(window.location.toString()));
            },
            disagree() {
                document.getElementById('logoutButton').dispatchEvent(new MouseEvent('click'));
            }
        },
        computed: {
            ...mapState(useCurrentUserStore, ['userData']),
        },
    };
</script>
