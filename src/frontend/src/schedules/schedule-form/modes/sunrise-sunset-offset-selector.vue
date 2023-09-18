<template>
    <div>
        <div class="form-group">
            <span class="input-group">
                <input type="number"
                    class="form-control"
                    step="1"
                    min="0"
                    max="200"
                    maxlength="3"
                    v-model="sunMinute"
                    @change="updateModel()">
                <span class="input-group-addon">
                    {{ $t('minutes') }}
                </span>
            </span>
        </div>
        <div class="form-group">
            <div class="text-right">
                <div class="btn-group">
                    <a class="btn btn-default"
                        @click="sunBefore = !sunBefore; updateModel()">
                        <span v-show="sunBefore">{{ $t('before') }}</span>
                        <span v-show="!sunBefore">{{ $t('after') }}</span>
                    </a>
                    <a class="btn btn-default"
                        @click="sunrise = !sunrise; updateModel()">
                        <span v-show="sunrise && sunBefore">{{ $t('sunrise-before') }}</span>
                        <span v-show="!sunrise && sunBefore">{{ $t('sunset-before') }}</span>
                        <span v-show="sunrise && !sunBefore">{{ $t('sunrise-after') }}</span>
                        <span v-show="!sunrise && !sunBefore">{{ $t('sunset-after') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {isEqual} from "lodash";

    export default {
        props: {
            value: Object,
        },
        data() {
            return {
                sunrise: true,
                sunBefore: false,
                sunMinute: 0
            };
        },
        methods: {
            updateModel() {
                this.$emit('input', {
                    mode: this.sunrise ? 'sunrise' : 'sunset',
                    offset: (this.sunMinute || 0) * (this.sunBefore ? -1 : 1),
                });
            },
            initFromModel() {
                this.sunrise = this.value?.mode === 'sunrise';
                this.sunMinute = Math.abs(this.value?.offset || 0);
                this.sunBefore = this.value?.offset <= 0;
            }
        },
        mounted() {
            this.initFromModel();
        },
        computed: {
            model() {
                return {
                    mode: this.sunrise ? 'sunrise' : 'sunset',
                    offset: (this.sunMinute || 0) * (this.sunBefore ? -1 : 1),
                };
            }
        },
        watch: {
            value(newValue) {
                if (!isEqual(newValue, this.model)) {
                    this.initFromModel();
                }
            }
        },
    };
</script>
