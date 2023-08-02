<template>
    <div>
        <button :class="'invisible-recaptcha ' + btnClass"
            v-show="formValid"
            type="submit"
            :disabled="isDisabled"
            :id="id">
            <slot></slot>
        </button>
        <button :class="btnClass"
            v-show="!formValid"
            type="submit"
            :disabled="isDisabled">
            <slot></slot>
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
                required: false,
                default: true,
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
            btnClass: {
                type: String,
                default: 'btn btn-block btn-lg btn-default'
            }
        },
        data: function () {
            return {
                widgetId: undefined,
                loaded: false
            };
        },
        methods: {
            render: function () {
                this.widgetId = window.grecaptcha.render(this.id, {
                    sitekey: this.sitekey,
                    size: 'invisible',
                    badge: this.badge || 'bottomright',
                    theme: this.theme || 'light',
                    callback: token => {
                        this.callback(token);
                        window.grecaptcha.reset(this.widgetId);
                    }
                });
                this.loaded = true;
            },
            waitForRecaptcha: function () {
                setTimeout(() => {
                    if (typeof window.grecaptcha !== 'undefined') {
                        window.grecaptcha.ready(() => this.render());
                    } else {
                        this.waitForRecaptcha();
                    }
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
                const script = document.createElement('script');
                script.src = 'https://www.google.com/recaptcha/api.js?render=explicit';
                document.head.appendChild(script);
            }
            this.waitForRecaptcha();
        }
    };
</script>

<style>
    .grecaptcha-badge {
        margin-bottom: 50px;
    }
</style>
