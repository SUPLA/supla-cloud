<template>
    <div>
        <div class="container text-center">
            <h1 class="nocapitalize">{{ $t(headerI18n) }}</h1>
            <i :class="[icon, 'error-page-icon']"
                style="font-size: 160px"></i>
            <div class="my-3">
                <h5>{{ $t(messageI18n) }}</h5>
            </div>
            <div v-if="guruMeditation">
                <button type="button"
                    class="btn btn-link"
                    @click="guruMeditationVisible = true"
                    v-if="!guruMeditationVisible">
                    Guru Meditation <i class="pe-7s-help1"></i>
                </button>
                <div class="well"
                    style="max-width: 600px; margin: 0 auto"
                    v-if="guruMeditationVisible">
                    <h3 class="no-margin-top">{{ $t('Guru Meditation') }}</h3>
                    <pre><code>{{ guruMeditation }}</code></pre>
                </div>
            </div>
            <div class="my-3">
                <router-link to="/">{{ $t('Start from the homepage.') }}</router-link>
            </div>
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['headerI18n', 'messageI18n', 'icon', 'guruMeditation'],
        data() {
            return {
                guruMeditationVisible: false
            };
        }
    };
</script>
