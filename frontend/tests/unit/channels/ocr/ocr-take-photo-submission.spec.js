import {beforeEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import OcrSettingsForm from '@/channels/ocr/ocr-settings-form.vue';
import {api} from '@/api/api.js';
import {successNotification} from '@/common/notifier';

vi.mock('@/api/api.js', () => ({api: {get: vi.fn(), patch: vi.fn()}}));
vi.mock('@/common/notifier', () => ({successNotification: vi.fn()}));

describe('OCR take photo submission feedback', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    api.get.mockResolvedValue({body: {}});
  });

  function mountForm(subject) {
    return mount(OcrSettingsForm, {
      props: {subject},
      global: {stubs: {PendingChangesPage: true, LoadingCover: true, TransitionExpand: true, TimeIntervalSlider: true, OcrPhotoCrop: true}},
    });
  }

  it('shows queued feedback for an offline channel eligible to queue a photo', async () => {
    const subject = {id: 4, connected: false, calcfgQueueableActions: {'ocr:takePhoto': true}, config: {ocr: {enabled: true, availableLightingModes: []}}};
    api.patch.mockResolvedValue({body: {actionResult: {status: 'QUEUED'}}});
    const wrapper = mountForm(subject);

    const button = wrapper.find('button');
    expect(button.element.disabled).toBe(false);
    await button.trigger('click');
    await flushPromises();

    expect(api.patch).toHaveBeenCalledWith('channels/4/settings', {action: 'ocr:takePhoto'});
    expect(wrapper.text()).toContain('calcfg_feedback_QUEUED');
    expect(successNotification).not.toHaveBeenCalled();
    wrapper.unmount();
  });

  it('shows waiting-for-result feedback instead of claiming the photo was sent', async () => {
    const subject = {id: 4, connected: true, config: {ocr: {enabled: true, availableLightingModes: []}}};
    api.patch.mockResolvedValue({body: {actionResult: {status: 'WAITING_FOR_RESULT'}}});
    const wrapper = mountForm(subject);

    await wrapper.find('button').trigger('click');
    await flushPromises();

    expect(wrapper.text()).toContain('calcfg_feedback_WAITING_FOR_RESULT');
    expect(successNotification).not.toHaveBeenCalled();
    wrapper.unmount();
  });

  it('disables photo requests for an offline channel without queue eligibility', () => {
    const subject = {id: 4, connected: false, calcfgQueueableActions: {'ocr:takePhoto': false}, config: {ocr: {enabled: true, availableLightingModes: []}}};
    const wrapper = mountForm(subject);

    expect(wrapper.find('button').element.disabled).toBe(true);
    expect(api.patch).not.toHaveBeenCalled();
    wrapper.unmount();
  });

  it('does not show success feedback when the HTTP request is rejected', async () => {
    const subject = {id: 4, connected: true, config: {ocr: {enabled: true, availableLightingModes: []}}};
    api.patch.mockRejectedValue({status: 503});
    const wrapper = mountForm(subject);

    await expect(wrapper.vm.takePhotoNow()).rejects.toEqual({status: 503});
    await flushPromises();

    expect(wrapper.text()).not.toContain('calcfg_feedback_QUEUED');
    expect(wrapper.text()).not.toContain('calcfg_feedback_WAITING_FOR_RESULT');
    expect(successNotification).not.toHaveBeenCalled();
    expect(wrapper.find('button').element.disabled).toBe(false);
    wrapper.unmount();
  });
});
