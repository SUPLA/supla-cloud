<template>
  <carousel-page
    header-i18n="Tariff profiles"
    :tile="TariffProfileTile"
    endpoint="energy-tariff-profiles"
    create-new-label-i18n="Create new tariff profile"
    details-route="tariffProfile"
  ></carousel-page>
</template>

<script setup>
  import CarouselPage from '@/common/pages/carousel-page.vue';
  import TariffProfileTile from './tariff-profile-tile.vue';
</script>
