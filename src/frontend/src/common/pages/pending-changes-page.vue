<template>
    <form @submit.prevent="$emit('save')">
        <div class="clearfix left-right-header">
            <h2 class="no-margin-top"
                v-if="header"
                v-title>{{ header }}</h2>
            <div v-else></div>
            <div class="button-container no-margin-top"
                v-show="!$frontendConfig.maintenanceMode">
                <transition name="fade">
                    <div class="btn-toolbar"
                        v-if="showPendingButtons">
                        <a class="btn btn-grey"
                            v-if="cancellable"
                            @click="$emit('cancel')">
                            <i class="pe-7s-back"></i>
                            {{ $t('Cancel changes') }}
                        </a>
                        <button class="btn btn-white"
                            type="submit">
                            <i class="pe-7s-diskette"></i>
                            {{ $t('Save changes') }}
                        </button>
                    </div>
                </transition>
                <transition name="fade">
                    <div v-if="showNotPendingButtons">
                        <slot name="buttons"></slot>
                        <div class="btn-toolbar"
                            v-if="deletable">
                            <a class="btn btn-danger"
                                @click="$emit('delete')">
                                {{ $t('Delete') }}
                            </a>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
        <div class="form-group">
            <slot></slot>
        </div>
        <div class="form-group visible-xs">
            <transition name="fade">
                <div class="btn-toolbar"
                    v-if="isPending">
                    <a class="btn btn-grey"
                        v-if="cancellable"
                        @click="$emit('cancel')">
                        <i class="pe-7s-back"></i>
                        {{ $t('Cancel changes') }}
                    </a>
                    <button class="btn btn-white"
                        type="submit">
                        <i class="pe-7s-diskette"></i>
                        {{ $t('Save changes') }}
                    </button>
                </div>
            </transition>
        </div>
    </form>
</template>

<script>
    export default {
        props: ['header', 'deletable', 'cancellable', 'isPending'],
        data() {
            return {
                lastPendingState: false,
                currentTimeout: undefined,
            };
        },
        computed: {
            showPendingButtons() {
                return this.isPending && !this.currentTimeout;
            },
            showNotPendingButtons() {
                return !this.isPending && !this.currentTimeout;
            },
        },
        watch: {
            isPending() {
                clearTimeout(this.currentTimeout);
                this.currentTimeout = setTimeout(() => this.currentTimeout = undefined, 515);
            }
        }
    };
</script>
