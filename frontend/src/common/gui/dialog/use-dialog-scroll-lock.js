import {onUnmounted, watch} from 'vue';

const locks = new WeakMap();
const lockClass = 'dialog-scroll-locked';

export function useDialogScrollLock(opened) {
  let locked = false;
  const unlock = () => {
    if (!locked) return;
    locked = false;
    const state = locks.get(document);
    if (--state.count) return;
    if (!state.original) document.documentElement.classList.remove(lockClass);
    locks.delete(document);
  };
  watch(
    opened,
    (active) => {
      if (!active) return unlock();
      if (locked || typeof document === 'undefined') return;
      let state = locks.get(document);
      if (!state) {
        state = {count: 0, original: document.documentElement.classList.contains(lockClass)};
        locks.set(document, state);
        document.documentElement.classList.add(lockClass);
      }
      state.count++;
      locked = true;
    },
    {immediate: true, flush: 'post'}
  );
  onUnmounted(unlock);
}
