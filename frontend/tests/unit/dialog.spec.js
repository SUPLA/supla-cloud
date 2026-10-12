import {afterEach, describe, expect, it, vi} from 'vitest';
import {mount} from '@vue/test-utils';
import {h, nextTick} from 'vue';
import DialogWindow from '@/common/gui/dialog/DialogWindow.vue';
import DialogContent from '@/common/gui/dialog/DialogContent.vue';

const wrappers = [];
afterEach(() => {
  wrappers.splice(0).forEach((wrapper) => wrapper.unmount());
  document.documentElement.classList.remove('dialog-scroll-locked');
  vi.restoreAllMocks();
});

async function dialog(props = {}) {
  const wrapper = mount(DialogWindow, {
    props: {modelValue: true, cancellable: true, ...props},
    slots: {default: () => h(DialogContent)},
  });
  wrappers.push(wrapper);
  await nextTick();
  return wrapper;
}
async function click(element) {
  element.dispatchEvent(new Event('pointerdown', {bubbles: true}));
  element.dispatchEvent(new MouseEvent('click', {bubbles: true, detail: 1}));
  await nextTick();
  await new Promise((resolve) => setTimeout(resolve, 0));
}
const locked = () => document.documentElement.classList.contains('dialog-scroll-locked');

describe('shared dialog behavior', () => {
  it('locks background scrolling and preserves default backdrop closing', async () => {
    const wrapper = await dialog();
    expect(locked()).toBe(true);
    await click(document.querySelector('.dialog-container'));
    expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    await click(document.querySelector('.dialog-mask'));
    expect(wrapper.emitted('update:modelValue').at(-1)).toEqual([false]);
    expect(locked()).toBe(false);
  });

  it('can ignore backdrop clicks while keeping the explicit cancel action available', async () => {
    const wrapper = await dialog({closeOnBackdrop: false});
    await click(document.querySelector('.dialog-mask'));
    expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    expect(locked()).toBe(true);
    await click(document.querySelector('.dialog-footer .cancel'));
    expect(wrapper.emitted('update:modelValue').at(-1)).toEqual([false]);
    expect(locked()).toBe(false);
  });

  it('keeps scrolling locked until the last dialog closes or unmounts', async () => {
    const first = await dialog();
    const second = await dialog();
    await first.setProps({modelValue: false});
    expect(locked()).toBe(true);
    second.unmount();
    wrappers.splice(wrappers.indexOf(second), 1);
    expect(locked()).toBe(false);
  });

  it('does not lock scrolling until a closed dialog opens and releases it on unmount', async () => {
    const wrapper = await dialog({modelValue: false});
    expect(locked()).toBe(false);
    await wrapper.setProps({modelValue: true});
    expect(locked()).toBe(true);
    wrapper.unmount();
    wrappers.splice(wrappers.indexOf(wrapper), 1);
    expect(locked()).toBe(false);
  });

  it('leaves page layout and inline styles untouched without manually changing scroll position', async () => {
    const scroll = vi.spyOn(window, 'scrollTo').mockImplementation(() => {});
    const originalBody = document.body.getAttribute('style');
    const originalRoot = document.documentElement.getAttribute('style');
    const wrapper = await dialog();
    expect(document.body.getAttribute('style')).toBe(originalBody);
    expect(document.documentElement.getAttribute('style')).toBe(originalRoot);
    await wrapper.setProps({modelValue: false});
    expect(document.body.getAttribute('style')).toBe(originalBody);
    expect(document.documentElement.getAttribute('style')).toBe(originalRoot);
    expect(scroll).not.toHaveBeenCalled();
  });
});
