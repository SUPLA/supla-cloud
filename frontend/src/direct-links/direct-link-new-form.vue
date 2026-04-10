<template>
  <div class="container">
    <BreadcrumbList current>
      <RouterLink :to="{name: 'directLinks'}">{{ $t('Direct links') }}</RouterLink>
    </BreadcrumbList>
    <h1 v-title>{{ $t('New direct link') }}</h1>
    <h3 class="text-center">{{ $t('What the link is for?') }}</h3>
    <div class="row">
      <div class="col-lg-6 col-lg-offset-3">
        <SubjectDropdown
          channels-dropdown-params="hasFunction=1"
          :filter="filterOutNotDirectLinkingSubjects"
          @input="chooseSubjectForNewLink($event)"
          disable-notifications
        />
        <span class="help-block">
          {{ $t('After you choose a subject, a direct link will be generated. You will be able to set all other options after its creation.') }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
  import SubjectDropdown from '@/devices/subject-dropdown.vue';
  import {useRouter} from 'vue-router';
  import {useDirectLinksStore} from '@/stores/direct-links-store.js';
  import BreadcrumbList from '@/common/gui/breadcrumb/BreadcrumbList.vue';

  const router = useRouter();
  const directLinksStore = useDirectLinksStore();

  function filterOutNotDirectLinkingSubjects(subject) {
    return !['ACTION_TRIGGER'].includes(subject.function.name);
  }

  async function chooseSubjectForNewLink(subject) {
    if (subject) {
      const newLink = await directLinksStore.create(subject);
      await router.push({name: 'directLink', params: {id: newLink.id}});
    }
  }
</script>
