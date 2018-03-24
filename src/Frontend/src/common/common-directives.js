import Vue from "vue";

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
