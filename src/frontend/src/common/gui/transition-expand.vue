<!-- https://medium.com/@sultondev/building-a-smooth-building-a-smooth-collapse-expand-animation-in-vue-3-a-deep-dive-into-2ac68a2aeb50 -->
<script setup>
  import {computed, ref, watch} from "vue";
  import {noop} from "@vueuse/core";

  const {
    name = "collapse",
    dimension = "height",
    duration = 200,
    easing = "ease-in-out",
  } = defineProps({
    name: String,
    dimension: String,
    duration: Number,
    easing: String,
  });

  const emit = defineEmits([
    'before-appear',
    'appear',
    'after-appear',
    'appear-cancelled',
    'before-enter',
    'enter',
    'after-enter',
    'enter-cancelled',
    'before-leave',
    'leave',
    'after-leave',
    'leave-cancelled',
  ]);

  const cachedStyles = ref(null);

  const transition = computed(() => {
    if (!cachedStyles.value) return "";
    const transitions = [];
    for (const key in cachedStyles.value) {
      transitions.push(`${convertToCssProperty(key)} ${duration}ms ${easing}`);
    }
    return transitions.join(", ");
  });

  watch(
    () => dimension,
    () => {
      clearCachedDimensions();
    },
  );

  /* Transition hook methods – note that the parameter is now of type `Element`.
     Inside the functions, if you need HTMLElement‐only properties (like offsetHeight),
     you can cast the element to HTMLElement. */

  function beforeAppear(el) {
    emit("before-appear", el);
  }

  function appear(el) {
    emit("appear", el);
  }

  function afterAppear(el) {
    emit("after-appear", el);
  }

  function appearCancelled(el) {
    emit("appear-cancelled", el);
  }

  function beforeEnter(el) {
    emit("before-enter", el);
  }

  function enter(el, done) {
    const htmlEl = el;
    detectAndCacheDimensions(htmlEl);
    setClosedDimensions(htmlEl);
    hideOverflow(htmlEl);
    forceRepaint(htmlEl);
    setTransition(htmlEl);
    setOpenedDimensions(htmlEl);

    emit("enter", el, done);
    setTimeout(done, duration);
  }

  function afterEnter(el) {
    const htmlEl = el;
    unsetOverflow(htmlEl);
    unsetTransition(htmlEl);
    unsetDimensions(htmlEl);
    clearCachedDimensions();
    emit("after-enter", el);
  }

  function enterCancelled(el) {
    emit("enter-cancelled", el);
  }

  function beforeLeave(el) {
    emit("before-leave", el);
  }

  function leave(el, done) {
    const htmlEl = el;
    detectAndCacheDimensions(htmlEl);
    setOpenedDimensions(htmlEl);
    hideOverflow(htmlEl);
    forceRepaint(htmlEl);
    setTransition(htmlEl);
    setClosedDimensions(htmlEl);

    emit("leave", el, done);
    setTimeout(done, duration);
  }

  function afterLeave(el) {
    const htmlEl = el;
    unsetOverflow(htmlEl);
    unsetTransition(htmlEl);
    unsetDimensions(htmlEl);
    clearCachedDimensions();
    emit("after-leave", el);
  }

  function leaveCancelled(el) {
    emit("leave-cancelled", el);
  }

  /* Utility functions – these continue to work with HTMLElement since they access DOM-specific properties. */

  function detectAndCacheDimensions(el) {
    if (cachedStyles.value !== null) return;

    const originalVisibility = el.style.visibility;
    const originalDisplay = el.style.display;

    el.style.visibility = "hidden";
    el.style.display = "";
    cachedStyles.value = detectRelevantDimensions(el);
    el.style.visibility = originalVisibility;
    el.style.display = originalDisplay;
  }

  function clearCachedDimensions() {
    cachedStyles.value = null;
  }

  function detectRelevantDimensions(el) {
    if (dimension === "height") {
      return {
        height: el.offsetHeight + "px",
        paddingTop: el.style.paddingTop || getCssValue(el, "padding-top"),
        paddingBottom: el.style.paddingBottom || getCssValue(el, "padding-bottom"),
      };
    } else if (dimension === "width") {
      return {
        width: el.offsetWidth + "px",
        paddingLeft: el.style.paddingLeft || getCssValue(el, "padding-left"),
        paddingRight: el.style.paddingRight || getCssValue(el, "padding-right"),
      };
    }
    return {};
  }

  function setTransition(el) {
    el.style.transition = transition.value;
  }

  function unsetTransition(el) {
    el.style.transition = "";
  }

  function hideOverflow(el) {
    el.style.overflow = "hidden";
  }

  function unsetOverflow(el) {
    el.style.overflow = "";
  }

  function setClosedDimensions(el) {
    if (!cachedStyles.value) return;
    Object.keys(cachedStyles.value).forEach((key) => {
      el.style.setProperty(convertToCssProperty(key), "0");
    });
  }

  function setOpenedDimensions(el) {
    if (!cachedStyles.value) return;
    Object.keys(cachedStyles.value).forEach((key) => {
      el.style.setProperty(convertToCssProperty(key), cachedStyles.value[key]);
    });
  }

  function unsetDimensions(el) {
    if (!cachedStyles.value) return;
    Object.keys(cachedStyles.value).forEach((key) => {
      el.style.removeProperty(convertToCssProperty(key));
    });
  }

  function forceRepaint(el) {
    // Accessing a property forces the browser to repaint.
    const b = getComputedStyle(el)[dimension];
    noop(b);
  }

  function getCssValue(el, styleProp) {
    return getComputedStyle(el).getPropertyValue(styleProp);
  }

  function convertToCssProperty(style) {
    const upperChars = style.match(/([A-Z])/g);
    if (!upperChars) {
      return style;
    }
    for (const char of upperChars) {
      style = style.replace(new RegExp(char), "-" + char.toLowerCase());
    }
    if (style.startsWith("-")) {
      style = style.slice(1);
    }
    return style;
  }
</script>

<template>
  <transition
    :name="name"
    @appear="appear"
    @enter="enter"
    @leave="leave"
    @before-appear="beforeAppear"
    @after-appear="afterAppear"
    @appear-cancelled="appearCancelled"
    @before-enter="beforeEnter"
    @after-enter="afterEnter"
    @enter-cancelled="enterCancelled"
    @before-leave="beforeLeave"
    @after-leave="afterLeave"
    @leave-cancelled="leaveCancelled"
  >
    <slot></slot>
  </transition>
</template>
