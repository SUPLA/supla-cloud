<template>
    <div>
        <select ref="dropdownElement"></select>
    </div>
</template>

<script>
    import TomSelect from "tom-select";
    import "tom-select/dist/css/tom-select.bootstrap4.min.css";

    export default {
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
                default: 50,
            }
        },
        data() {
            return {
                chosenId: undefined,
                dropdown: undefined,
            };
        },
        mounted() {
            this.createDropdown();
        },
        methods: {
            createDropdown() {
                this.$nextTick(() => {
                    this.dropdown = new TomSelect(this.$refs.dropdownElement, {
                        valueField: 'id',
                        labelField: 'fullCaption',
                        searchField: 'searchText',
                        optgroupField: 'group',
                        hideSelected: !this.doNotHideSelected,
                        maxItems: this.multiple ? null : 1,
                        maxOptions: this.maxOptions,
                        onInitialize: () => this.syncDropdown(),
                        plugins: {
                            remove_button: {
                                title: this.$t('Remove this item'),
                            }
                        },
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
                                    const ids = optId.map(id => +id);
                                    const chosenOptions = this.options.filter(opt => ids.includes(opt.id));
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
                        }
                    });
                    this.syncDropdown();
                });
            },
            syncDropdown() {
                if (this.dropdown) {
                    this.dropdown.clearOptions();
                    this.dropdown.clearOptionGroups();
                    this.optionGroups.forEach(({id, labelI18n}) => this.dropdown.addOptionGroup(id, {label: this.$t(labelI18n)}));
                    this.dropdown.settings.placeholder = this.$t(this.choosePromptI18n);
                    this.dropdown.inputState();
                    if (this.options && this.options.length) {
                        this.dropdown.enable();
                        if (this.noneOption) {
                            this.dropdown.addOption({id: 0, caption: this.$t('None')});
                        }
                        this.options.forEach(o => {
                            const optionToAdd = {...o, fullCaption: this.caption(o)};
                            optionToAdd.searchText = this.searchText(optionToAdd);
                            this.dropdown.addOption(optionToAdd);
                        });
                        if (this.multiple) {
                            this.dropdown.setValue((this.value || []).map(v => v.id));
                        } else {
                            this.dropdown.setValue(this.value?.id || undefined);
                        }
                    } else {
                        this.dropdown.disable();
                        this.dropdown.setValue(this.multiple ? [] : undefined);
                    }
                    if (this.disabled) {
                        this.dropdown.disable();
                    }
                }
            }
        },
        watch: {
            options() {
                this.syncDropdown();
            },
            value() {
                if (this.dropdown) {
                    if (this.multiple) {
                        this.dropdown.setValue((this.value || []).map(v => v.id));
                    } else {
                        this.dropdown.setValue(this.value?.id || undefined);
                    }
                }
            },
            '$i18n.locale'() {
                this.syncDropdown();
            },
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .subject-dropdown-option {
        padding: 5px 3px;
        .icon {
            margin-left: 8px;
            display: flex;
            align-items: center;
            padding-left: .25em;
            padding-right: .25em;
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
