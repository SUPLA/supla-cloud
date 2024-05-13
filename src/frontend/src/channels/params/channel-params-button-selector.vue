<template>
    <div>
        <div class="btn-group btn-group-flex"
            v-if="values.length < 5 && !useDropdown">
            <a :class="'btn ' + (value == valueDef.id ? 'btn-green' : 'btn-default')"
                :key="valueDef.id"
                v-for="valueDef in values"
                @click="$emit('input', valueDef.id)">
                {{ valueDef.label }}
            </a>
        </div>
        <div class="dropdown"
            v-else>
            <button class="btn btn-default dropdown-toggle btn-block btn-wrapped"
                type="button"
                data-toggle="dropdown">
                {{ currentValueDef.label }}
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li v-for="valueDef in values"
                    :key="valueDef.id">
                    <a @click="$emit('input', valueDef.id)"
                        v-show="valueDef.id != value">{{ valueDef.label }}</a>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            value: String,
            values: Array,
            useDropdown: Boolean,
        },
        mounted() {
            if (!this.values.find(v => v.id === this.value)) {
                this.$emit('input', this.values[0].id);
            }
        },
        computed: {
            currentValueDef() {
                return this.values.find(v => v.id === this.value);
            }
        }
    };
</script>
