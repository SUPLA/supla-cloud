<template>
    <span class="password-display"
        @click.stop="">
        <span class="password text-monospace">{{ thePassword }}</span>
        <a @mousedown="uncovered = true"
            @touchstart="uncovered = true"
            @mouseleave="uncovered = false"
            @touchend="uncovered = false"
            @mouseup="uncovered = false"
            class="uncover-link password-link">
            <i class="pe-7s-look"></i>
        </a>
        <a @click="editing = true"
            class="password-link"
            v-if="editable">
            <i class="pe-7s-note"></i>
        </a>
        <modal class="square-modal-chooser"
            cancellable="true"
            @cancel="editing = false"
            @confirm="dispatchNewPassword()"
            v-if="editing"
            :header="$t('Enter new password')">
            <span class="input-group">
                <input type="text"
                    class="form-control"
                    v-model="newPassword">
                <span class="input-group-btn">
                    <a class="btn btn-white"
                        @click="generatePassword(password.length || 5)">{{ $t('GENERATE') }}</a>
                </span>
            </span>
        </modal>
    </span>
</template>

<script>
    import {generatePassword} from "../utils";

    export default {
        props: ['password', 'editable'],
        data() {
            return {
                uncovered: false,
                editing: false,
                newPassword: '',
            };
        },
        methods: {
            generatePassword(length) {
                this.newPassword = generatePassword(length);
            },
            dispatchNewPassword() {
                if (this.newPassword) {
                    this.$emit('change', this.newPassword);
                    this.newPassword = '';
                }
                this.editing = false;
            }
        },
        computed: {
            thePassword() {
                return this.uncovered ? this.password : this.password.replace(/./g, '*');
            }
        }
    };
</script>


<style lang="scss">
    @import "../../styles/variables";

    .password-display {
        .uncover-link {
            display: inline-block;
            margin-left: 5px;
        }
        a.password-link {
            font-size: 1.3em;
            padding: 0 5px;
            vertical-align: middle;
        }
    }
</style>
