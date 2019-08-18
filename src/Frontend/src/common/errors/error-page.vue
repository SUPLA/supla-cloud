<template>
    <div>
        <div class="container text-center">
            <h1 class="nocapitalize">{{ $t(headerI18n) }}</h1>
            <i :class="icon"
                style="font-size: 160px"></i>
            <h5>{{ $t(messageI18n) }}</h5>
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['headerI18n', 'messageI18n', 'icon'],
    };
</script>
