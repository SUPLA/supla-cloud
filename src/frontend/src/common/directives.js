import {ref} from "vue";

export function registerDirectives(app) {
  app.directive('go-to-link-on-row-click', {
    mounted: function (el) {
      el.classList.add('pointer');
      el.addEventListener('click', event => {
        if (!['a', 'button'].includes(event.target?.nodeName.toLowerCase())) {
          const a = event.currentTarget.querySelectorAll('a')[0]
          setTimeout(() => a.click());
        }
      });
    }
  });

  const pageTitle = ref('');
  app.provide('pageTitle', pageTitle);

  const updatePageTitle = function (title) {
    document.title = title + ' - SUPLA Cloud';
    pageTitle.value = title;
  };


  app.directive('title', {
    mounted: (el, binding) => updatePageTitle(binding.value || el.innerText),
    updated: (el, binding) => updatePageTitle(binding.value || el.innerText),
  });

  app.directive('focus', {
    mounted: function (el, binding) {
      if (binding.value) {
        el.focus();
      } else {
        el.blur();
      }
    }
  });

  app.directive('input-digits-only', {
    mounted: function (el) {
      el.addEventListener('keypress', event => {
        if (!/\d/.test(event.key)) {
          return event.preventDefault();
        }
      });
    }
  });
}
