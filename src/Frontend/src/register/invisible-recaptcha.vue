<template>
    <div>
        <button class="invisible-recaptcha btn btn-default btn-block btn-lg"
            v-show="formValid"
            :type="type"
            :disabled="isDisabled"
            :id="id">
            <slot btn></slot>
        </button>
        <button class="btn btn-default btn-block btn-lg"
            v-show="!formValid"
            :type="type"
            :disabled="isDisabled">
            <slot btn></slot>
        </button>
    </div>
</template>

<script>
    export default {
        props: {
            sitekey: {
                type: String,
                required: true
            },
            badge: {
                type: String,
                required: false
            },
            theme: {
                type: String,
                required: false
            },
            formValid: {
                type: Boolean,
                required: false
            },
            callback: {
                type: Function,
                required: true
            },
            disabled: {
                type: Boolean,
                required: false
            },
            id: {
                type: String,
                required: false
            },
            type: {
                type: String,
                required: false
            },
        },
        data: function () {
            return {
                widgetId: false,
                loaded: false
            };
        },
        methods: {
            render: function () {
                this.widgetId = window.grecaptcha.render(this.id, {
                    sitekey: this.sitekey,
                    size: 'invisible',
                    badge: this.badge || 'bottomright',
                    theme: this.theme || 'dark',
                    callback: token => {
                        this.callback(token);
                        window.grecaptcha.reset(this.widgetId);
                    }
                });
                this.loaded = true;
            },
            renderWait: function () {
                const self = this;
                setTimeout(function () {
                    if (typeof window.grecaptcha !== 'undefined') self.render();
                    else self.renderWait();
                }, 200);
            }
        },
        computed: {
            isDisabled() {
                return !this.loaded || this.disabled;
            }
        },
        mounted: function () {
            if (typeof window.grecaptcha === 'undefined') {
                var script = document.createElement('script');
                script.src = 'https://www.google.com/recaptcha/api.js?render=explicit';
                script.onload = this.renderWait;

                document.head.appendChild(script);
            } else {
                this.render();
            }
        }
    };
</script>

<style>
    .grecaptcha-badge {
        margin-bottom: 50px;
    }
</style>
