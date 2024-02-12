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
            value: Object,
            choosePromptI18n: String,
            noneOption: Boolean,
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
                        hideSelected: true,
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
                            if (optId === 0) {
                                this.dropdown.setValue(undefined);
                            } else if (+optId !== this.value?.id) {
                                const chosenChannel = this.options.find((opt) => opt.id === +optId);
                                this.$emit('input', chosenChannel);
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
                        this.dropdown.setValue(this.value?.id || undefined);
                    } else {
                        this.dropdown.disable();
                        this.dropdown.setValue(undefined);
                    }
                }
            }
        },
        watch: {
            options() {
                this.syncDropdown();
            },
            value() {
                this.dropdown.setValue(this.value?.id || undefined);
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
