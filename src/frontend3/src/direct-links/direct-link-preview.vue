<template>
    <div class="alert alert-warning">
        <h3 class="no-margin-top">{{ $t('Direct link has been created successfully!') }}</h3>
        <div class="form-group">
            <p>{{ $t('For security reasons, the full URL of this direct link will be displayed only once, during creation.') }}</p>
            <p>{{ $t('Make sure to save it in a secure place if you wish to access it in the future. The link won’t be again displayed after you leave this page.') }}</p>
        </div>
        <div class="form-group text-center">
            <a :href="url"
                target="_blank"
                class="btn btn-green">
                <i class="pe-7s-next-2"></i>
                {{ $t('See direct link possibilities') }}
            </a>
        </div>
        <ul class="nav nav-tabs">
            <li :class="currentAction == undefined ? 'active' : ''">
                <a @click="currentAction = undefined">{{ $t('Let me choose later') }}</a>
            </li>
            <li v-for="action in possibleAllowedActions"
                :key="action.id"
                :class="currentAction == action.nameSlug ? 'active' : ''">
                <a @click="currentAction = action.nameSlug">{{ actionCaption(action, directLink.subject) }}</a>
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
    import {actionCaption} from "../channels/channel-helpers";

    export default {
        methods: {actionCaption},
        components: {CopyButton},
        props: ['url', 'allowedActions', 'possibleActions', 'directLink'],
        data() {
            return {
                currentAction: undefined,
            };
        },
        computed: {
            currentActionUrl() {
                return this.url + (this.currentAction ? '/' + this.currentAction : '');
            },
            possibleAllowedActions() {
                return this.possibleActions.filter(action => !!this.allowedActions[action.name]);
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
