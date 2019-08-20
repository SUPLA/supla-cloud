import Vue from "vue";
import autosize from "autosize";

Vue.directive('go-to-link-on-row-click', {
    inserted: function (el) {
        $(el).addClass('pointer');
        el.addEventListener('click', event => {
            if (!$(event.target).is('a, button')) {
                const link = $(event.currentTarget).find('a')[0];
                setTimeout(() => link.click());
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
});

Vue.directive('autosize', {
    bind(el) {
        autosize(el);
    },
    componentUpdated(el) {
        autosize(el);
    },
    unbind(el) {
        autosize.destroy(el);
    }
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
