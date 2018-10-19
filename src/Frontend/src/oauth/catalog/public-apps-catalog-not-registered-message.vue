<template>
    <whole-screen-message icon="pe-7s-mouse"
        header="Your private SUPLA Cloud is not registered.">
        <p>
            <component :is="registerCloudMessage"></component>
        </p>
        <p>
            <component :is="onlyCatalogMessage"></component>
        </p>
        <p>
            <component :is="otherwiseMessage"></component>
        </p>
    </whole-screen-message>
</template>

<script>
    import WholeScreenMessage from "../../register/whole-screen-message";

    export default {
        components: {WholeScreenMessage},
        computed: {
            registerCloudMessage() {
                const message = this.$t('If you want to integrate your Cloud with public OAuth applications, you must [register your instance].')
                    .replace(/\[(.+?)\]/, `<a href="https://cloud.supla.org/register-cloud">$1</a>`);
                return {template: `<span>${message}</span>`};
            },
            onlyCatalogMessage() {
                const message = this.$t('If you wish to see the available integrations only, head over to [cloud.supla.org/apps].')
                    .replace(/\[(.+?)\]/, `<a href="https://cloud.supla.org/apps">$1</a>`);
                return {template: `<span>${message}</span>`};
            },
            otherwiseMessage() {
                const message = this.$t('Go to [the homepage] otherwise.')
                    .replace(/\[(.+?)\]/, `<router-link to="/">$1</router-link>`);
                return {template: `<span>${message}</span>`};
            }
        }
    };
</script>
