<template>
    <form @submit.prevent="$emit('save')">
        <div class="clearfix left-right-header">
            <h2 class="no-margin-top">{{ header }}</h2>
            <div class="button-container no-margin-top">
                <transition name="fade">
                    <div class="btn-toolbar"
                        v-if="isPending">
                        <button class="btn btn-white"
                            type="submit">
                            <i class="pe-7s-diskette"></i>
                            {{ $t('Save changes') }}
                        </button>
                        <a class="btn btn-grey"
                            @click="$emit('cancel')">
                            <i class="pe-7s-back"></i>
                            {{ $t('Cancel changes') }}
                        </a>
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
                    <button class="btn btn-white"
                        type="submit">
                        <i class="pe-7s-diskette"></i>
                        {{ $t('Save changes') }}
                    </button>
                    <a class="btn btn-grey"
                        @click="$emit('cancel')">
                        <i class="pe-7s-back"></i>
                        {{ $t('Cancel changes') }}
                    </a>
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


<style lang="scss">
    @import "../../styles/mixins";

    .button-container {
        position: relative;
        .btn-toolbar {
            position: absolute;
            right: 0;
            min-width: 350px;
            .btn {
                float: right;
            }

        }
        @include on-xs-and-down {
            height: 35px;
            .btn-toolbar {
                width: 100%;
            }
            .btn {
                float: none !important;
            }
        }
    }
</style>
