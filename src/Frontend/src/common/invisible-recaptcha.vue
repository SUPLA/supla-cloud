<template>
<button :class="computedClass" :type="type" :disabled="!loaded || disabled" :id="id || _uid" @click="click">
    <slot></slot>
</button>
</template>

<script type="text/javascript">
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

        validate: {
            type: Function,
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
        }
    },

    data: function() {
        return {
            widgetId: false,
            loaded: false
        };
    },

    methods: {
        render: function() {
            this.widgetId = window.grecaptcha.render(this.id || this._uid, {
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

        renderWait: function() {
            const self = this;
            setTimeout(function() {
                if (typeof window.grecaptcha !== 'undefined') self.render();
                else self.renderWait();
            }, 200);
        },

        click: function() {
            if (this.validate) this.validate();
            window.grecaptcha.execute();
        }
    },

    computed: {
        computedClass: function() {
            var classArray = this.class ? this.class.split(' ') : [];

            if (this.value) {
                classArray.push('invisible-recaptcha');
            }

            return classArray;
        }
    },

    mounted: function() {
        if (typeof window.grecaptcha === 'undefined') {
            var script = document.createElement('script');
            script.src = 'https://www.google.com/recaptcha/api.js?render=explicit';
            script.onload = this.renderWait;

            document.head.appendChild(script);
        } else this.render();
    }
};
</script>
<style>
.grecaptcha-badge {
	margin-bottom: 50px;
}
</style>
