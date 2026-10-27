<template>
  <div>
    <select ref="dropdownElement"></select>
  </div>
</template>

<script>
  import TomSelect from 'tom-select';
  import {escape} from 'lodash';
  import 'tom-select/dist/css/tom-select.bootstrap4.min.css';

  export default {
    compatConfig: {WATCH_ARRAY: false},
    props: {
      options: Array,
      optionGroups: {
        type: Array,
        default: () => [],
      },
      value: [Object, Array],
      choosePromptI18n: String,
      noneOption: Boolean,
      doNotHideSelected: Boolean,
      multiple: Boolean,
      disabled: Boolean,
      caption: {
        type: Function,
        default: (option) => option.caption,
      },
      searchText: {
        type: Function,
        default: (option) => option.fullCaption,
      },
      optionHtml: {
        type: Function,
        default: (option) => `<div>${option.fullCaption}</div>`,
      },
      maxOptions: {
        type: Number,
        default: 100,
      },
    },
    data() {
      return {
        chosenId: undefined,
        dropdown: undefined,
        dropdownContentKey: undefined,
      };
    },
    watch: {
      options() {
        this.syncDropdown();
      },
      value() {
        if (this.dropdown) {
          const ids = this.multiple ? (this.value || []).map((v) => v.id) : this.value?.id ? [this.value.id] : [];
          if (
            ids
              .filter((id) => this.dropdown.options[id])
              .map(String)
              .join(',') === this.dropdown.items.join(',')
          )
            return;
          if (this.multiple) {
            this.dropdown.setValue(
              (this.value || []).map((v) => v.id),
              true
            );
          } else {
            this.dropdown.setValue(this.value?.id || undefined, true);
          }
        }
      },
      '$i18n.locale'() {
        this.syncDropdown();
      },
      optionGroups() {
        this.syncDropdown();
      },
      disabled() {
        this.syncDropdown();
      },
    },
    mounted() {
      this.createDropdown();
    },
    methods: {
      createDropdown() {
        const plugins = {};
        if (this.hideNone) {
          plugins.remove_button = {
            title: this.$t('Remove this item'),
          };
        }
        this.$nextTick(() => {
          this.dropdown = new TomSelect(this.$refs.dropdownElement, {
            valueField: 'id',
            labelField: 'fullCaption',
            searchField: 'searchText',
            optgroupField: 'group',
            lockOptgroupOrder: true,
            hideSelected: !this.doNotHideSelected,
            maxItems: this.multiple ? null : 1,
            maxOptions: this.maxOptions,
            onInitialize: () => this.syncDropdown(),
            plugins,
            render: {
              option: (option, escape) => {
                if (option.id === 0) {
                  return `<div>${this.$t('None')}</div>`;
                }
                return this.optionHtml(option, escape);
              },
              item: (option, escape) => {
                if (option.id === 0) {
                  return `<em>${this.$t('None')}</em>`;
                }
                return this.optionHtml(option, escape);
              },
              no_results: () => {
                return `<div class="no-results">${this.$t('No results')}</div>`;
              },
            },
            onChange: (optId) => {
              if (this.multiple) {
                if (optId.length !== (this.value || []).length) {
                  const ids = optId.map((id) => +id);
                  const chosenOptions = this.options.filter((opt) => ids.includes(opt.id));
                  this.$emit('input', chosenOptions);
                }
              } else {
                if (optId === 0) {
                  this.dropdown.setValue([]);
                } else if (optId != this.value?.id) {
                  const chosenOption = this.options.find((opt) => opt.id == optId);
                  this.$emit('input', chosenOption);
                }
              }
              this.dropdown.blur();
            },
          });
          this.syncDropdown();
        });
      },
      syncDropdown() {
        if (this.dropdown) {
          const options = (this.options || []).map((option) => {
            const prepared = {...option, fullCaption: this.caption(option)};
            prepared.searchText = this.searchText(prepared);
            return prepared;
          });
          const groups = this.optionGroups.map(({id, labelI18n}) => [id, this.$t(labelI18n)]);
          const placeholder = this.$t(this.choosePromptI18n);
          // Polling changes channel state too. Rebuild only if the visible/searchable options change.
          const contentKey = JSON.stringify([
            options.map((option) => [option.id, option.group, option.fullCaption, option.searchText, this.optionHtml(option, escape)]),
            groups,
            placeholder,
            this.noneOption,
            this.disabled,
            this.$t('None'),
          ]);
          if (contentKey === this.dropdownContentKey) return;
          this.dropdownContentKey = contentKey;
          const wasOpen = this.dropdown.isOpen;
          const search = this.dropdown.control_input?.value || '';
          const scrollTop = this.dropdown.dropdown_content?.scrollTop || 0;
          const activeValue = this.dropdown.activeOption?.dataset.value;
          this.dropdown.clear(true);
          this.dropdown.clearOptions();
          this.dropdown.clearOptionGroups();
          groups.forEach(([id, label]) => this.dropdown.addOptionGroup(id, {label}));
          this.dropdown.settings.placeholder = placeholder;
          this.dropdown.inputState();
          if (options.length) {
            this.dropdown.enable();
            if (this.noneOption) {
              this.dropdown.addOption({id: 0, caption: this.$t('None')});
            }
            options.forEach((option) => this.dropdown.addOption(option));
            if (this.multiple) {
              this.dropdown.setValue(
                (this.value || []).map((v) => v.id),
                true
              );
            } else {
              this.dropdown.setValue(this.value?.id || undefined, true);
            }
          } else {
            this.dropdown.disable();
            this.dropdown.setValue(this.multiple ? [] : undefined, true);
          }
          if (this.disabled) {
            this.dropdown.disable();
          }
          if (wasOpen) this.dropdown.setTextboxValue(search);
          this.dropdown.refreshOptions(false);
          if (wasOpen && !this.dropdown.isDisabled) {
            this.dropdown.open();
            const active = this.dropdown.getOption(activeValue);
            if (active && this.dropdown.dropdown_content.contains(active)) this.dropdown.setActiveOption(active, false);
            const scrollBehavior = this.dropdown.dropdown_content.style.scrollBehavior;
            this.dropdown.dropdown_content.style.scrollBehavior = 'auto';
            this.dropdown.dropdown_content.scrollTop = scrollTop;
            this.dropdown.dropdown_content.style.scrollBehavior = scrollBehavior;
          }
        }
      },
    },
  };
</script>

<style lang="scss">
  @use '../styles/variables' as *;

  .subject-dropdown-option {
    padding: 5px 3px;
    .icon {
      margin-left: 8px;
      display: flex;
      align-items: center;
      padding-left: 0.25em;
      padding-right: 0.25em;
      img {
        height: 35px;
      }
    }
  }

  .ts-control {
    border-color: $supla-green !important;
    box-shadow: none !important;
    &:not(.rtl) {
      padding-right: 30px !important;
    }
    > .item {
      .option-extra {
        display: none;
      }
    }
  }
</style>
