<script setup lang="ts">
import FormAbogado from './components/FormAbogado.vue'

const props = defineProps<{ usuarios: Array<{id:number; name:string; email:string}> }>()
</script>

<template>
  <div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Nuevo abogado</h1>

    <FormAbogado
      mode="create"
      :usuarios="props.usuarios"
      :submitRoute="{ name: 'abogados.store' }"
    />
  </div>
</template>
