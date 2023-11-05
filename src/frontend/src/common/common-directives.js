import Vue from "vue";

Vue.directive('go-to-link-on-row-click', {
    inserted: function (el) {
        el.classList.add('pointer');
        el.addEventListener('click', event => {
            if (!['a', 'button'].includes(event.target?.nodeName.toLowerCase())) {
                const a = event.currentTarget.querySelectorAll('a')[0]
                setTimeout(() => a.click());
            }
        });
    }
});

const updatePageTitle = function (title) {
    document.title = title + ' - SUPLA Cloud';
};

Vue.directive('title', {
    inserted: (el, binding) => updatePageTitle(binding.value || el.innerText),
    update: (el, binding) => updatePageTitle(binding.value || el.innerText),
    componentUpdated: (el, binding) => updatePageTitle(binding.value || el.innerText),
});

Vue.directive('focus', {
    inserted: function (el, binding) {
        if (binding.value) {
            el.focus();
        } else {
            el.blur();
        }
    }
});

Vue.directive('input-digits-only', {
    inserted: function (el) {
        el.addEventListener('keypress', event => {
            if (!/\d/.test(event.key)) {
                return event.preventDefault();
            }
        });
    }
});
