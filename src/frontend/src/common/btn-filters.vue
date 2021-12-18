<template>
    <div class="btn-filters">
        <div class="btn-group btn-group-filters btn-group-filters-inline">
            <button v-for="filter in visibleFilters"
                :key="filter.label"
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
                    <li v-for="filter in filters"
                        :key="filter.label">
                        <a @click="setFilter(filter.value)">{{ filter.label }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['id', 'value', 'filters', 'defaultSort'],
        data() {
            return {
                chosenFilter: undefined,
            };
        },
        mounted() {
            if (this.id) {
                const filter = this.defaultSort || this.$localStorage.get(this.localStorageId);
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
                if (this.value != filter) {
                    this.$emit('input', this.chosenFilter);
                }
                if (this.id && !this.defaultSort) {
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
            },
            visibleFilters() {
                return this.filters.filter(f => f.visible !== false);
            },
        },
        watch: {
            value(value) {
                if (value) {
                    this.setFilter(value);
                }
            }
        }
    };
</script>

<style lang="scss">
    .btn-filters {
        .btn-group-filters {
            margin-left: .5em;
        }
    }
</style>
