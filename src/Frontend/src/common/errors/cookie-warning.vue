<template>
    <transition name="fade">
        <div v-if="shown"
            class="alert alert-warning cookie-warning">
            <component :is="warningText"
                @click="regulationsShown = true"></component>
            <a @click="agree()"
                class="btn btn-default btn-xs pull-right">{{ $t('I agree') }}</a>
            <regulations-modal v-if="regulationsShown"
                @confirm="regulationsShown = false"></regulations-modal>
        </div>
    </transition>
</template>

<script>
    import RegulationsModal from "./regulations-modal";

    export default {
        components: {RegulationsModal},
        data() {
            return {
                shown: true,
                regulationsShown: false
            };
        },
        methods: {
            agree() {
                this.shown = false;
                this.$http.patch('users/current', {action: 'agree:cookies'});
            }
        },
        computed: {
            warningText() {
                const template = this.$t('We store some data (e.g. cookies) in your browser to remember your preferences and to make the application usage easier [Please read the Terms and Conditions].')
                    .replace(/\[(.+?)\]/g, `<a @click.prevent="$emit('click')">$1</a>`);
                return {template: `<span>${template}</span>`};
            }
        }
    };
</script>

<style scoped
    lang="scss">
    @import "../../styles/variables";

    .alert {
        width: 90%;
        max-width: 300px;
        margin: 0 auto;
        position: fixed;
        bottom: 5px;
        right: 5px;
        background: $supla-yellow;
        border-color: darken($supla-yellow, 10%);
    }
</style>

<style>
    .hide-cookies-warning .cookie-warning {
        display: none;
    }
</style>
