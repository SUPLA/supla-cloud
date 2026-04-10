import {computed, isProxy, isReactive, reactive, ref, toRaw, watch} from 'vue';

function deepClone(val) {
  if (isProxy(val) || isReactive(val)) {
    val = toRaw(val);
  }
  return val == null ? val : structuredClone(val);
}

function deepEqual(a, b) {
  if (a === b) return true;

  if (typeof a !== typeof b) return false;
  if (a == null || b == null) return false;

  if (Array.isArray(a)) {
    if (!Array.isArray(b) || a.length !== b.length) return false;
    return a.every((v, i) => deepEqual(v, b[i]));
  }

  if (typeof a === 'object') {
    const ak = Object.keys(a);
    const bk = Object.keys(b);
    if (ak.length !== bk.length) return false;
    return ak.every((k) => deepEqual(a[k], b[k]));
  }

  return false;
}

/**
 * @param {Ref|ComputedRef} sourceRef
 * @param {string[]} keys
 * @param {{
 *   equals?: (a:any,b:any,key:string)=>boolean,
 *   clone?: (v:any)=>any,
 *   mapIn?: (src:any)=>any
 * }} opts
 */
export function useEditableFields(sourceRef, keys, opts = {}) {
  const equals = opts.equals ?? ((a, b) => deepEqual(a, b));
  const clone = opts.clone ?? deepClone;

  const draft = reactive({});
  const synced = reactive({});
  const conflict = ref(false);

  function pullFromSource(src) {
    const mapped = opts.mapIn ? opts.mapIn(src) : src;

    for (const k of keys) {
      draft[k] = clone(mapped?.[k]);
      synced[k] = clone(mapped?.[k]);
    }
    conflict.value = false;
  }

  const dirty = computed(() => {
    for (const k of keys) {
      if (!equals(draft[k], synced[k], k)) return true;
    }
    return false;
  });

  watch(
    () => sourceRef.value,
    (src) => {
      if (!src) return;

      if (!dirty.value) {
        pullFromSource(src);
        return;
      }

      const mapped = opts.mapIn ? opts.mapIn(src) : src;

      for (const k of keys) {
        if (!equals(mapped?.[k], synced[k], k)) {
          conflict.value = true;
          return;
        }
      }
    },
    {immediate: true}
  );

  function cancel() {
    for (const k of keys) {
      draft[k] = clone(synced[k]);
    }
    conflict.value = false;
  }

  function markSaved() {
    for (const k of keys) {
      synced[k] = clone(draft[k]);
    }
    conflict.value = false;
  }

  function syncNow() {
    if (sourceRef.value) pullFromSource(sourceRef.value);
  }

  function getPatch() {
    const patch = {};
    for (const k of keys) {
      if (!equals(draft[k], synced[k], k)) {
        patch[k] = clone(draft[k]);
      }
    }
    return patch;
  }

  return {
    draft,
    synced,
    dirty,
    conflict,
    cancel,
    markSaved,
    syncNow,
    getPatch,
  };
}
