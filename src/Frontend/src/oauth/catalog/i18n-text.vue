<template>
    <span>{{ translatedText }}</span>
</template>

<script>
    export default {
        props: ['text'],
        computed: {
            translatedText() {
                if (this.text) {
                    if (typeof this.text === 'string') {
                        return this.text;
                    }
                    const locale = this.$i18n.locale;
                    return this.text[locale] || this.text['en'] || this.text['pl'] || this.text[Object.keys(this.text)[0]];
                } else {
                    return '';
                }
            }
        }
    };
</script>
