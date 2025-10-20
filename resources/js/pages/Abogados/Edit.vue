<script setup lang="ts">
import FormAbogado from './components/FormAbogado.vue'

const props = defineProps<{
  abogado: { id:number; nombre:string; estatus:'activo'|'inactivo'; usuario_id:number|null }
  usuarios: Array<{id:number; name:string; email:string}>
}>()
</script>

<template>
  <div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Editar abogado</h1>

    <FormAbogado
      mode="edit"
      :usuarios="props.usuarios"
      :initial="props.abogado"
      :submitRoute="{ name: 'abogados.update', params: { abogado: props.abogado.id } }"
    />
  </div>
</template>
