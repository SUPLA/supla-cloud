<template>
    <div>
        <input type="text"
            class="form-control text-center crontab-input"
            v-model="crontab"
            @input="updateValue()">
        <div class="help-block text-right">
            {{ humanizedCrontab }}
        </div>
    </div>
</template>

<script>
    import cronstrue from 'cronstrue/i18n';

    export default {
        props: ['value'],
        data() {
            return {
                crontab: ''
            };
        },
        mounted() {
            if (this.value) {
                this.crontab = this.value;
            }
        },
        methods: {
            updateValue() {
                if (this.humanizedCrontab) {
                    this.$emit('input', this.crontab);
                } else {
                    this.$emit('input', undefined);
                }
            }
        },
        computed: {
            humanizedCrontab() {
                try {
                    return cronstrue.toString(this.crontab, {locale: 'pl'});
                } catch (error) {
                    return '';
                }
            },
        }
    };
</script>

<style lang="scss">
    .crontab-input {
        font-size: 1.3em;
        font-weight: bold;
    }
</style>
