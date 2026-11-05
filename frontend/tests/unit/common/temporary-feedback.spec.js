import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {mount} from '@vue/test-utils';
import TemporaryFeedback from '@/common/gui/temporary-feedback.vue';

describe('temporary feedback', () => {
  beforeEach(() => vi.useFakeTimers());
  afterEach(() => vi.useRealTimers());

  it('shows a message for three seconds and hides it', async () => {
    const wrapper = mount(TemporaryFeedback, {props: {message: 'Sent'}});
    expect(wrapper.text()).toBe('Sent');
    expect(wrapper.get('.alert').classes()).toEqual(expect.arrayContaining(['alert-info', 'mt-2', 'mb-0']));
    await vi.advanceTimersByTimeAsync(2999);
    expect(wrapper.text()).toBe('Sent');
    await vi.advanceTimersByTimeAsync(1);
    expect(wrapper.text()).toBe('');
    wrapper.unmount();
  });

  it('merges custom classes with the default feedback classes', () => {
    const wrapper = mount(TemporaryFeedback, {props: {message: 'Sent'}, attrs: {class: 'text-center'}});
    expect(wrapper.get('.alert').classes()).toEqual(expect.arrayContaining(['alert-info', 'mt-2', 'mb-0', 'text-center']));
    wrapper.unmount();
  });

  it('uses a custom timeout in milliseconds', async () => {
    const wrapper = mount(TemporaryFeedback, {props: {message: 'Sent', timeout: 5000}});
    await vi.advanceTimersByTimeAsync(3000);
    expect(wrapper.text()).toBe('Sent');
    await vi.advanceTimersByTimeAsync(2000);
    expect(wrapper.text()).toBe('');
    wrapper.unmount();
  });

  it('closes immediately and cancels its timer when dismissed', async () => {
    const wrapper = mount(TemporaryFeedback, {props: {message: 'Sent'}});
    await wrapper.get('button[aria-label="Close"]').trigger('click');
    expect(wrapper.text()).toBe('');
    expect(vi.getTimerCount()).toBe(0);
    await wrapper.setProps({message: 'Again'});
    expect(wrapper.text()).toBe('Again');
    wrapper.unmount();
  });

  it('restarts the timer for a new message and cleans up when unmounted', async () => {
    const wrapper = mount(TemporaryFeedback, {props: {message: 'First'}});
    await vi.advanceTimersByTimeAsync(2000);
    await wrapper.setProps({message: 'Second'});
    await vi.advanceTimersByTimeAsync(1000);
    expect(wrapper.text()).toBe('Second');
    await vi.advanceTimersByTimeAsync(2000);
    expect(wrapper.text()).toBe('');
    await wrapper.setProps({message: 'Third'});
    wrapper.unmount();
    expect(vi.getTimerCount()).toBe(0);
  });

  it('can show the same message again after it has been cleared', async () => {
    const wrapper = mount(TemporaryFeedback, {props: {message: 'Sent'}});
    await vi.advanceTimersByTimeAsync(3000);
    await wrapper.setProps({message: ''});
    await wrapper.setProps({message: 'Sent'});
    expect(wrapper.text()).toBe('Sent');
    await vi.advanceTimersByTimeAsync(3000);
    expect(wrapper.text()).toBe('');
    wrapper.unmount();
  });

  it('uses show to start and cancel feedback when provided', async () => {
    const wrapper = mount(TemporaryFeedback, {props: {message: 'Sent', show: false}});
    expect(wrapper.text()).toBe('');
    await wrapper.setProps({show: true});
    expect(wrapper.text()).toBe('Sent');
    await vi.advanceTimersByTimeAsync(3000);
    expect(wrapper.text()).toBe('');
    await wrapper.setProps({show: false});
    await wrapper.setProps({show: true});
    expect(wrapper.text()).toBe('Sent');
    await wrapper.setProps({show: false});
    expect(wrapper.text()).toBe('');
    expect(vi.getTimerCount()).toBe(0);
    wrapper.unmount();
  });

  it('translates message-i18n and restarts when its key changes', async () => {
    const wrapper = mount(TemporaryFeedback, {
      props: {messageI18n: 'first'},
      global: {mocks: {$t: (key) => `translated ${key}`}},
    });
    expect(wrapper.text()).toBe('translated first');
    await vi.advanceTimersByTimeAsync(2000);
    await wrapper.setProps({messageI18n: 'second'});
    await vi.advanceTimersByTimeAsync(1000);
    expect(wrapper.text()).toBe('translated second');
    await vi.advanceTimersByTimeAsync(2000);
    expect(wrapper.text()).toBe('');
    wrapper.unmount();
  });
});
