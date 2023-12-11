<template>
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-lg-offset-3">
                <h2 class="no-margin-top">{{ $t('The device is locked') }}</h2>

                <p>{{ $t('Locked devices are meant to be distributed and installed only by the authorized installers.') }}</p>
                <p>{{ $t('If you are not an authorized installer of this device, you should contact the seller and request a refund.') }}</p>
                <form>
                    <div class="form-group form-group-lg">
                        <label for>{{ $t('Unlock code') }}</label>
                        <input type="text" class="form-control form-control-lg text-center text-monospace">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-green">
                            <fa icon="unlock"/>
                            {{ $t('Unlock the device') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import 'vue-slider-component/theme/antd.css';

    export default {
        props: {
            device: Object,
        },

    }
</script>

<style lang="scss">

</style>
