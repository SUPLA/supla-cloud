<template>
    <div class="btn-filters">
        <div class="btn-group btn-group-filters btn-group-filters-inline">
            <button v-for="filter in filters"
                :class="'btn ' + (chosenFilter === filter.value ? 'active' : '')"
                @click="setFilter(filter.value)"
                type="button">
                {{ filter.label }}
            </button>
        </div>
        <div class="btn-group-filters btn-group-filters-dropdown">
            <div class="dropdown">
                <button class="btn dropdown-toggle"
                    type="button"
                    data-toggle="dropdown">
                    {{ chosenFilterLabel }}
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li v-for="filter in filters">
                        <a @click="setFilter(filter.value)">{{ filter.label }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['id', 'value', 'filters'],
        data() {
            return {
                chosenFilter: undefined,
            };
        },
        mounted() {
            if (this.id) {
                const filter = this.$localStorage.get(this.localStorageId);
                if (filter) {
                    const index = this.filters.findIndex(f => f.value === filter);
                    if (index >= 0) {
                        this.setFilter(filter);
                    }
                }
            }
            if (!this.chosenFilter) {
                this.setFilter(this.filters[0].value);
            }
        },
        methods: {
            setFilter(filter) {
                this.chosenFilter = filter;
                this.$emit('input', this.chosenFilter);
                if (this.id) {
                    this.$localStorage.set(this.localStorageId, filter);
                }
            }
        },
        computed: {
            localStorageId() {
                return `btnFilters${this.id}`;
            },
            chosenFilterLabel() {
                const filter = this.filters.find(f => f.value === this.chosenFilter);
                return filter ? filter.label : '';
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/mixins";
    @import "../styles/variables";

    .btn-filters {
        display: inline-block;
    }

    .btn-group-filters {
        .btn {
            border-color: $supla-grey-light;
            text-transform: uppercase;
            font-size: 11px;
            line-height: 26px;
            padding: 4px 10px;
            font-size: .8em;
            background: $supla-white;
            font-weight: 400;
            border-radius: 3px;
            outline-color: $supla-grey-light !important;
            &:hover {
                background: $supla-grey-light;
            }
            &.active {
                background: $supla-grey-light;
            }
        }
        &:hover {
            .btn {
                border-color: $supla-black;
                &.active {
                    color: $supla-white;
                    background: $supla-black;
                }
            }
        }
        &-dropdown {
            display: none;
        }
        @include on-xs-and-down {
            &-dropdown {
                display: block;
            }
            &-inline {
                display: none;
            }
        }
    }
</style>
