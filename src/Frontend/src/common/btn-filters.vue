<template>
    <div class="btn-group btn-group-filters">
        <button v-for="filter in filters"
            :class="'btn ' + (chosenFilter === filter.value ? 'active' : '')"
            @click="setFilter(filter.value)"
            type="button">
            {{ filter.label }}
        </button>
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
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

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
    }
</style>
