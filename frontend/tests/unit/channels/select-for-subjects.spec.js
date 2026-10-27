import {mount, flushPromises} from '@vue/test-utils';
import SelectForSubjects from '@/devices/select-for-subjects.vue';

const options = [
  {id: 10, caption: 'Chosen thermometer', fullCaption: 'Chosen thermometer', group: 'local'},
  {id: 11, caption: 'Other local thermometer', fullCaption: 'Other local thermometer', group: 'local'},
  {id: 20, caption: 'Remote thermometer', fullCaption: 'Remote thermometer', group: 'remote'},
];
const optionGroups = [
  {id: 'local', labelI18n: 'Channels on this device'},
  {id: 'remote', labelI18n: 'Channels on other devices'},
];
const wrappers = [];

async function dropdown(props = {}) {
  const wrapper = mount(SelectForSubjects, {attachTo: document.body, props: {options, optionGroups, ...props}});
  wrappers.push(wrapper);
  await flushPromises();
  const tom = wrapper.find('select').element.tomselect;
  return {wrapper, tom};
}

async function open(tom) {
  tom.refreshOptions(false);
  tom.open();
  // TomSelect focuses the input asynchronously; settle that before measuring refresh mutations.
  await new Promise((resolve) => setTimeout(resolve, 0));
  await flushPromises();
  tom.setTextboxValue('therm');
  tom.refreshOptions(false);
  tom.setActiveOption(tom.getOption('20'), false);
  tom.dropdown_content.scrollTop = 135;
}

afterEach(() => {
  for (const wrapper of wrappers.splice(0)) {
    wrapper.find('select').element.tomselect.destroy();
    wrapper.unmount();
  }
  vi.restoreAllMocks();
});

it.each(['valid', 'none', 'stale', 'multiple'])('preserves the open menu and its DOM through state-only refreshes (%s selection)', async (selection) => {
  const value = () =>
    selection === 'multiple'
      ? options.slice(0, 2).map((o) => ({...o}))
      : selection === 'valid'
        ? {...options[0]}
        : selection === 'stale'
          ? {id: 999}
          : undefined;
  const {wrapper, tom} = await dropdown({value: value(), multiple: selection === 'multiple'});
  await open(tom);
  const visibleOptions = [...tom.dropdown_content.querySelectorAll('[data-selectable]')];
  expect(visibleOptions.length).toBeGreaterThan(0);
  const clear = vi.spyOn(tom, 'clearOptions');
  const render = vi.spyOn(tom, 'refreshOptions');
  const close = vi.fn();
  tom.on('dropdown_close', close);
  for (const connected of [true, false, true]) {
    await wrapper.setProps({options: options.map((o) => ({...o, connected, state: {value: connected ? 21 : 22}})), value: value()});
    await flushPromises();
  }
  expect(clear).not.toHaveBeenCalled();
  expect(render).not.toHaveBeenCalled();
  expect(close).not.toHaveBeenCalled();
  expect([...tom.dropdown_content.querySelectorAll('[data-selectable]')]).toEqual(visibleOptions);
  expect(tom.control_input.value).toBe('therm');
  expect(tom.activeOption.dataset.value).toBe('20');
  expect(tom.dropdown_content.scrollTop).toBe(135);
  expect(tom.isOpen).toBe(true);
  expect(wrapper.emitted('input')).toBeUndefined();
});

it('renders actual option changes while preserving search, focus, active option and scroll', async () => {
  const {wrapper, tom} = await dropdown({value: options[0]});
  await open(tom);
  const previousNode = tom.getOption('20');
  await wrapper.setProps({
    options: [
      ...options.map((o) => ({...o, caption: o.caption + ' updated'})),
      {id: 21, caption: 'New thermometer', fullCaption: 'New thermometer', group: 'remote'},
    ],
  });
  await flushPromises();
  expect(tom.getOption('20')).not.toBe(previousNode);
  expect(tom.getOption('20').textContent).toContain('updated');
  expect(tom.dropdown_content.querySelector('[data-value="21"]')).not.toBeNull();
  expect(tom.control_input.value).toBe('therm');
  expect(document.activeElement).toBe(tom.control_input);
  expect(tom.activeOption.dataset.value).toBe('20');
  expect(tom.dropdown_content.scrollTop).toBe(135);
  expect(tom.isOpen).toBe(true);
  expect(tom.items).toEqual(['10']);
  expect(wrapper.emitted('input')).toBeUndefined();
});

it('removes unavailable options without emitting a configuration change or leaving empty group headers', async () => {
  const {wrapper, tom} = await dropdown({value: options[0]});
  await open(tom);
  await wrapper.setProps({options: options.slice(1)});
  await flushPromises();
  expect(tom.options['10']).toBeUndefined();
  expect([...tom.dropdown_content.querySelectorAll('[data-selectable]')].map((node) => node.dataset.value)).toEqual(['11', '20']);
  expect(tom.items).toEqual([]);
  expect(wrapper.emitted('input')).toBeUndefined();
  expect(wrapper.props('value').id).toBe(10);
});

it('does not open a closed menu when options are refreshed', async () => {
  const {wrapper, tom} = await dropdown();
  await wrapper.setProps({options: options.slice(1)});
  await flushPromises();
  expect(tom.isOpen).toBe(false);
  expect([...tom.dropdown_content.querySelectorAll('[data-selectable]')].map((node) => node.dataset.value)).toEqual(['11', '20']);
});

it('updates group labels and respects disabling an open menu', async () => {
  const {wrapper, tom} = await dropdown();
  await open(tom);
  await wrapper.setProps({optionGroups: [{id: 'local', labelI18n: 'Local channels'}, optionGroups[1]]});
  await flushPromises();
  expect(tom.dropdown_content.textContent).toContain('Local channels');
  await wrapper.setProps({disabled: true});
  await flushPromises();
  expect(tom.isDisabled).toBe(true);
  expect(tom.isOpen).toBe(false);
});
