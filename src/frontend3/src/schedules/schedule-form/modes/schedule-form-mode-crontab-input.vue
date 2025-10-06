<template>
    <div>
        <input type="text"
            class="form-control text-center crontab-input"
            placeholder="23 0-20/2 * * *"
            v-model="crontab"
            @input="updateValue()">
        <div class="help-block text-right">
            {{ humanizedCrontab }}
        </div>
    </div>
</template>

<script>
    import cronstrue from 'cronstrue/i18n';
    import {i18n} from "@/locale";

    if (!cronstrue.prototype.getTimeOfDayDescriptionOriginal) {
        cronstrue.prototype.getTimeOfDayDescriptionOriginal = cronstrue.prototype.getTimeOfDayDescription;
        cronstrue.prototype.getTimeOfDayDescription = function () {
            const minutePart = this.expressionParts[1];
            if (minutePart.match(/^S[SR]-?[0-9]+/)) {
                const sunset = minutePart.charAt(1) === 'S';
                const delay = parseInt(minutePart.substr(2));
                if (delay === 0) {
                    if (sunset) {
                        return i18n.global.t('At sunset');
                    } else {
                        return i18n.global.t('At sunrise');
                    }
                } else if (delay > 0) {
                    if (sunset) {
                        return i18n.global.t('{minutes} minutes after sunset', {minutes: delay});
                    } else {
                        return i18n.global.t('{minutes} minutes after sunrise', {minutes: delay});
                    }
                } else {
                    if (sunset) {
                        return i18n.global.t('{minutes} minutes before sunset', {minutes: -delay});
                    } else {
                        return i18n.global.t('{minutes} minutes before sunrise', {minutes: -delay});
                    }
                }
            } else {
                return this.getTimeOfDayDescriptionOriginal();
            }
        };
    }

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
                    const description = cronstrue.toString(this.crontab, {locale: this.$i18n.locale});
                    if (description.indexOf('undefined') !== -1 || description.indexOf('null') !== -1) {
                        return '';
                    }
                    return description;
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
