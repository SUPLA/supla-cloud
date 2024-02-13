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
            value: [Object, Array],
            choosePromptI18n: String,
            noneOption: Boolean,
            doNotHideSelected: Boolean,
            multiple: Boolean,
            caption: {
                type: Function,
                default: (option) => option.caption,
            },
            optionHtml: {
                type: Function,
                default: (option) => `<div>${option.fullCaption}</div>`,
            },
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
                        searchField: 'fullCaption',
                        hideSelected: !this.doNotHideSelected,
                        maxItems: this.multiple ? null : 1,
                        onInitialize: () => this.syncDropdown(),
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
                        }
                    });
                    this.syncDropdown();
                });
            },
            syncDropdown() {
                if (this.dropdown) {
                    this.dropdown.clearOptions();
                    this.dropdown.settings.placeholder = this.$t(this.choosePromptI18n);
                    this.dropdown.inputState();
                    if (this.options && this.options.length) {
                        this.dropdown.enable();
                        if (this.noneOption) {
                            this.dropdown.addOption({id: 0, caption: this.$t('None')});
                        }
                        this.options.forEach(o => this.dropdown.addOption({
                            ...o,
                            fullCaption: this.caption(o),
                        }));
                        if (this.multiple) {
                            this.dropdown.setValue((this.value || []).map(v => v.id));
                        } else {
                            this.dropdown.setValue(this.value?.id || undefined);
                        }
                    } else {
                        this.dropdown.disable();
                        this.dropdown.setValue(this.multiple ? [] : undefined);
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
