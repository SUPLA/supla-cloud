<template>

    <div class="row">
        <div class="col-sm-6">
            <div>
                <label>{{ $t('Active from') }}</label>
            </div>
            <div class="text-center mb-3">
                <div class="btn-group btn-group-xs">
                    <a :class="['btn', `btn-${afterCondition ? 'white' : 'green'}`]"
                        @click="afterCondition = undefined; updateModel()">
                        {{ $t('midnight') }}
                    </a>
                    <a :class="['btn', `btn-${!afterCondition ? 'white' : 'green'}`]"
                        @click="afterCondition = {mode: 'sunrise', offset: 0}; updateModel()">
                        {{ $t('sunrise / sunset') }}
                    </a>
                </div>
            </div>
            <transition-expand>
                <sunrise-sunset-offset-selector v-model="afterCondition" v-if="afterCondition" @input="updateModel()"/>
            </transition-expand>
        </div>
        <div class="col-sm-6">
            <div>
                <label>{{ $t('Active to') }}</label>
            </div>
            <div class="text-center mb-3">
                <div class="btn-group btn-group-xs">
                    <a :class="['btn', `btn-${beforeCondition ? 'white' : 'green'}`]"
                        @click="beforeCondition = undefined; updateModel()">
                        {{ $t('midnight') }}
                    </a>
                    <a :class="['btn', `btn-${!beforeCondition ? 'white' : 'green'}`]"
                        @click="beforeCondition = {mode: 'sunset', offset: 0}; updateModel()">
                        {{ $t('sunrise / sunset') }}
                    </a>
                </div>
            </div>
            <transition-expand>
                <sunrise-sunset-offset-selector v-model="beforeCondition" v-if="beforeCondition"/>
            </transition-expand>
        </div>
    </div>

</template>

<script>
    import SunriseSunsetOffsetSelector from "@/schedules/schedule-form/modes/sunrise-sunset-offset-selector.vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {startCase} from "lodash";

    export default {
        components: {TransitionExpand, SunriseSunsetOffsetSelector},
        props: {
            value: Array,
            displayValidationErrors: Boolean,
        },
        data() {
            return {
                afterCondition: undefined,
                beforeCondition: undefined,
            };
        },
        mounted() {
            // this.condition = deepCopy(this.value) || [];
        },
        methods: {
            updateModel() {
                this.$emit('input', this.condition);
            }
        },
        computed: {
            condition() {
                const condition = [];
                if (this.afterCondition) {
                    const field = `after${startCase(this.afterCondition.mode)}`;
                    condition.push({[field]: this.afterCondition.offset});
                }
                if (this.beforeCondition) {
                    const field = `before${startCase(this.beforeCondition.mode)}`;
                    condition.push({[field]: this.beforeCondition.offset});
                }
                return condition;
            }
        },
    }
</script>
