<template>
    <div class="alert alert-danger">
        <p>
            <strong>{{ $t('Settings have not been saved!') }}</strong>
        </p>
        <p>
            {{ $t('The configuration has been changed from another source (e.g. another browser tab, mobile app, device). In order to prevent data loss, you have to refresh the configuration first.') }}
        </p>
        <p>
            {{ $t('Refreshing the configuration will overwrite the changes you made here.') }}
        </p>
        <p class="text-center">
            <a @click="$emit('refresh')">
                <fa icon="refresh"/>
                {{ $t('Refresh the config') }}
            </a>
        </p>
    </div>
</template>
