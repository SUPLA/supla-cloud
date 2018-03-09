import Vue from "vue";

Vue.directive('fade-out-link', {
    inserted: function (el) {
        $(el).click(() => $('.main-content').fadeOut(300));
    }
});

Vue.directive('go-to-link-on-row-click', {
    inserted: function (el) {
        $(el).addClass('pointer');
        el.addEventListener('click', event => {
            if (!$(event.target).is('a')) {
                const link = $(event.currentTarget).find('a')[0];
                setTimeout(() => link.click());
            }
        });
    }
});
