<template>
    <div>
        <div class="clearfix left-right-header">
            <div>
                <h5>{{ $t('OAuth applications allow easy integration of your solutions and SUPLA.') }}</h5>
            </div>
            <div>
                <a class="btn btn-white"
                    v-if="!addingNew"
                    @click="addingNew = true">
                    <i class="pe-7s-plus"></i>
                    {{ $t('Register a new OAuth application') }}
                </a>
            </div>
        </div>
        <div class="well"
            v-if="addingNew">
            <h2 class="no-margin-top">{{ $t('Register a new OAuth application') }}</h2>
            <form>
                <div class="form-group">
                    <label>{{ $t('Application name') }}</label>
                    <input type="text"
                        class="form-control">
                    <span class="help-block">
                        {{ $t('It will be shown to the users before app authorization') }}
                    </span>
                    <div class="form-group">
                        <label>{{ $t('Application description') }}</label>
                        <textarea type="text"
                            class="form-control"></textarea>
                        <span class="help-block">
                            {{ $t('Tell users more about its purpose') }}
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ $t('Authorization callback URL') }}</label>
                    <input type="text"
                        class="form-control">
                    <span class="help-block">
                        {{ $t('We will send an authorization code there after successful app authorization') }}
                    </span>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox">
                        {{ $t('Enable offline access') }}
                    </label>
                    <span class="help-block">
                        {{ $t('Decide if application needs to interact with SUPLA account without the user presence') }}
                    </span>
                </div>
                <div class="text-right">
                    <button class="btn btn-green">
                        {{ $t('Register application') }}
                    </button>
                    <button class="btn btn-white"
                        type="button">
                        {{ $t('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                addingNew: false,
            };
        }
    };
</script>
