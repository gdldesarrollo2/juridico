<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import FormJuicio from './components/FormJuicio.vue'

const props = defineProps<{
  juicio: any
  clientes: {id:number; nombre:string}[]
  autoridades: {id:number; nombre:string}[]
  abogados: {id:number; nombre:string}[]
  etiquetas: {id:number; nombre:string}[]
  etiquetasSeleccionadas: number[]
}>()
</script>

<template>
  <TopNavLayout />
  <div class="bg-white rounded-xl p-5 shadow space-y-4">
    <div class="flex items-center gap-2">
      <h1 class="text-2xl font-semibold">Editar Juicio #{{ props.juicio.id }}</h1>
      <Link :href="route('juicios.index')" class="ml-auto text-indigo-600 hover:underline">Volver</Link>
    </div>

    <FormJuicio
      mode="edit"
      :initial="props.juicio"
      :clientes="props.clientes"
      :autoridades="props.autoridades"
      :abogados="props.abogados"
      :etiquetas="props.etiquetas"
      :etiquetasSeleccionadas="props.etiquetasSeleccionadas"
    />
  </div>
</template>
