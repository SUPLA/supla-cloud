<template>
    <a :class="'copy-button btn btn-' + (copied ? copiedCssClass : defaultCssClass)"
        v-clipboard:copy="text"
        v-clipboard:success="onCopy">
        <span v-if="copied">
            <i class="pe-7s-check"></i>
            {{ $t('copied') }}
        </span>
        <span v-else>
            <i class="pe-7s-copy-file"></i>
            {{ $t('copy') }}
        </span>
    </a>
</template>

<script>
    import Vue from 'vue';
    import VueClipboard from 'vue-clipboard2';
    import {debounce} from "lodash";

    Vue.use(VueClipboard);

    export default {
        props: ['text', 'copiedClass', 'defaultClass'],
        data() {
            return {
                copied: false
            };
        },
        computed: {
            copiedCssClass() {
                return this.copiedClass || 'green';
            },
            defaultCssClass() {
                return this.defaultClass || 'white';
            }
        },
        methods: {
            onCopy() {
                this.copied = true;
                this.resetCopiedState(this);
            },
            resetCopiedState: debounce((that) => that.copied = false, 1500),
        }
    };
</script>

<style lang="scss">

</style>
