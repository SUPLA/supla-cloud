<template>
    <transition name="fade">
        <div v-if="shown"
            class="alert alert-warning cookie-warning">
            {{ $t('We store some data (e.g. cookies) in your browser to remember your preferences and to make the application usage easier.') }}
            <a @click="regulationsShown = true">{{ $t('Please read the Terms and Conditions.') }}</a>
            <a @click="agree()"
                class="btn btn-default btn-xs pull-right">{{ $t('I agree') }}</a>
            <regulations-modal v-if="regulationsShown"
                @confirm="regulationsShown = false"></regulations-modal>
        </div>
    </transition>
</template>

<script>
  import RegulationsModal from "./regulations-modal.vue";
  import {api} from "@/api/api.js";

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
              api.patch('users/current', {action: 'agree:cookies'});
            }
        },
    };
</script>

<style scoped lang="scss">
  @use "../../styles/variables" as *;
  @use 'sass:color';

    .alert {
        width: 90%;
        max-width: 300px;
        margin: 0 auto;
        position: fixed;
        bottom: 45px;
        right: 5px;
        background: $supla-yellow;
      border-color: color.adjust($supla-yellow, $lightness: -10%);
    }
</style>

<style>
    .hide-cookies-warning .cookie-warning {
        display: none;
    }
</style>
