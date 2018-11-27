<template>
    <div class="alert alert-warning">
        <h3 class="no-margin-top">{{ $t('Direct link has been created successfully!') }}</h3>
        <div class="form-group">
            <p>{{ $t('For security reasons, the full URL of this direct link will be displayed only once, during creation.')}}</p>
            <p>{{ $t('Make sure to save it in a secure place if you wish to access it in the future. The link won’t be again displayed after you leave this page.')}}</p>
        </div>

        <ul class="nav nav-tabs">
            <li :class="currentAction == undefined ? 'active' : ''">
                <a @click="currentAction = undefined">{{ $t('Let me choose later') }}</a>
            </li>
            <li v-for="action in possibleActions"
                v-if="allowedActions[action.name]"
                :class="currentAction == action.nameSlug ? 'active' : ''">
                <a @click="currentAction = action.nameSlug">{{ $t(action.caption) }}</a>
            </li>
        </ul>

        <div class="flex-left-full-width">
            <pre><code>{{ currentActionUrl }}</code></pre>
            <copy-button :text="currentActionUrl"></copy-button>
        </div>
    </div>
</template>

<script>
    import CopyButton from "../common/copy-button";

    export default {
        components: {CopyButton},
        props: ['url', 'allowedActions', 'possibleActions'],
        data() {
            return {
                currentAction: undefined,
            };
        },
        computed: {
            currentActionUrl() {
                return this.url + (this.currentAction ? '/' + this.currentAction : '');
            }
        }
    };
</script>

<style lang="scss"
    scoped>
    pre {
        border-top-left-radius: 0;
        border-top: 0;
        border-color: #ddd;
        background: #fafbfc;
    }

    .copy-button {
        border-top: 0;
        border-top-right-radius: 0;
        border-color: #ddd;
    }
</style>
