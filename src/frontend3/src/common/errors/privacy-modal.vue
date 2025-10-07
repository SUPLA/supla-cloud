<template>
    <modal class="text-left modal-800 display-newlines">
        <div>
            <loading-cover :loading="!rules">
                <div v-if="rules" v-html="rules"></div>
            </loading-cover>
        </div>
        <template #footer></template>
    </modal>
</template>

<script>
  import {api} from "@/api/api.js";
  import LoadingCover from "@/common/gui/loaders/loading-cover.vue";
  import Modal from "@/common/modal.vue";

  export default {
    components: {Modal, LoadingCover},
        data() {
            return {
                rules: '',
            };
        },
        mounted() {
            this.fetch();
        },
        methods: {
            fetch() {
                this.rules = '';
                let rulesLang = 'en';
                if (['pl', 'it', 'es', 'fr', 'ru', 'pt', 'de'].includes(this.$i18n.locale)) {
                    rulesLang = this.$i18n.locale;
                }
              api.get(`/regulations/privacy/privacy_${rulesLang}.html`).then(response => this.rules = response.body);
            }
        },
        watch: {
            '$i18n.locale'() {
                this.fetch();
            }
        }
    };
</script>
l
