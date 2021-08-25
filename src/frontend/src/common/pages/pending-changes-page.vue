<template>
    <form @submit.prevent="$emit('save')">
        <div class="clearfix left-right-header">
            <h2 class="no-margin-top"
                v-title>{{ header }}</h2>
            <div class="button-container no-margin-top"
                v-show="!$frontendConfig.maintenanceMode">
                <transition name="fade">
                    <div class="btn-toolbar"
                        v-if="isPending">
                        <a class="btn btn-grey"
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
                    <div v-if="!isPending">
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
        props: ['header', 'deletable', 'isPending'],
    };
</script>
