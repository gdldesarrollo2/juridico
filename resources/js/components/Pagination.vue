<script setup>
const props = defineProps({
  links: Array
})
</script>

<template>
  <div class="flex justify-center mt-4 space-x-1">
    <template v-for="(link, idx) in links" :key="idx">
      <div v-if="link.url === null"
           class="px-3 py-1 text-gray-400 border rounded bg-gray-100"
           v-html="link.label" />

      <Link
        v-else
        :href="link.url"
        v-html="link.label"
        class="px-3 py-1 border rounded hover:bg-gray-200"
        :class="{
          'bg-blue-600 text-white': link.active,
          'bg-white text-gray-700': !link.active
        }"
      />
    </template>
  </div>
</template>
