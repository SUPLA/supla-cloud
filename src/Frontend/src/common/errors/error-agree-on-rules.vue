<template>
    <loading-cover class="container text-center"
        :loading="loading">
        <h1>{{ $t('The Terms and Conditions have changed') }}</h1>
        <i class="pe-7s-note2"
            style="font-size: 160px"></i>
        <h5>{{ $t('The Terms and Conditions have changed – please read them and accept.') }}</h5>
        <regulations-checkbox v-model="agreed"></regulations-checkbox>
        <div class="form-group">
            <a class="btn btn-yellow"
                :href="'/auth/logout' | withBaseUrl">
                <i class="pe-7s-back"></i>
                {{ $t('I do not agree, get me out of here') }}
            </a>
            <button class="btn btn-green"
                :disabled="!agreed"
                @click="agree()">
                <i class="pe-7s-simple-check"></i>
                {{ $t('I aree, taske me to the app') }}
            </button>
        </div>
    </loading-cover>
</template>

<script>
    import RegulationsCheckbox from "./regulations-checkbox";

    export default {
        components: {RegulationsCheckbox},
        data() {
            return {
                agreed: false,
                loading: false,
            };
        },
        mounted() {
            if (this.$user.agreements.rules) {
                this.$router.push('/');
            }
        },
        methods: {
            agree() {
                this.loading = true;
                this.$http.patch('users/current', {action: 'agree:rules'})
                    .finally(() => window.location.assign(window.location.toString()));
            }
        }
    };
</script>
