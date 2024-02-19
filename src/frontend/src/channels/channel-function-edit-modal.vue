<template>
    <modal-confirm :header="$t('Change channel function')"
        :loading="loading"
        @confirm="$emit('confirm', chosenFunction)"
        @cancel="$emit('cancel')">
        <div class="functions-list"
            v-if="chosenFunction">
            <a v-for="fnc in supportedFunctions"
                :key="fnc.id"
                :class="{active: chosenFunction.id === fnc.id}"
                @click="chosenFunction = fnc">
                <span>
                    <function-icon :model="fnc" width="80"></function-icon>
                    {{ $t(fnc.caption) }}
                </span>
            </a>
        </div>
    </modal-confirm>
</template>

<script>
    import FunctionIcon from "@/channels/function-icon";

    export default {
        components: {FunctionIcon},
        props: ['channel', 'loading'],
        data() {
            return {
                chosenFunction: undefined,
            };
        },
        mounted() {
            this.chosenFunction = this.channel.function;
        },
        computed: {
            supportedFunctions() {
                return [].concat.apply([{
                    id: 0,
                    caption: 'None (channel disabled)', // i18n
                    name: 'NONE',
                }], this.channel.supportedFunctions);
            },
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";
    @import "../styles/mixins";

    .functions-list {
        list-style: none;
        text-align: center;
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        a {
            min-width: 25%;
            flex: 1;
            border: 1px solid $supla-grey-dark;
            padding: 5px;
            position: relative;
            img {
                display: block;
                clear: both;
                margin: 0 auto;
            }
            &:hover {
                border-color: $supla-green;
            }
            &.active {
                border-color: $supla-black;
                &:after {
                    content: '';
                    position: absolute;
                    width: 50px;
                    height: 51px;
                    background: url('../assets/checked-corner.svg') no-repeat;
                    top: -1px;
                    right: -1px;
                    border-top-right-radius: 3px;
                    transition: all .5s ease-in-out;
                    animation-duration: 0.5s;
                    animation-fill-mode: both;
                    animation-name: fadeIn;
                }
            }
            @include on-xs-and-down {
                min-width: 45%;
            }
        }
    }
</style>
