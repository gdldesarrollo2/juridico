<!-- resources/js/Pages/Juicios/Edit.vue -->
<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import FormJuicio from './components/FormJuicio.vue';

type Opcion = { id:number; nombre:string }

const props = defineProps<{
  juicio: {
    id: number
    // Los demás campos que ya envías desde el backend…
    // nombre?: string; tipo?: string; cliente_id?: number; autoridad_id?: number;
    // fecha_inicio?: string|null; monto?: number|null; observaciones_monto?: string|null;
    // resolucion_impugnada?: string|null; garantia?: string|null;
    // numero_juicio?: string|null; numero_expediente?: string|null;
    // estatus: string; abogado_id?: number|null;
    // etiquetas?: number[];  // si envías IDs seleccionados
    periodos?: Record<string, number[]>  // 👈 importante para el multi-año/mes
  }
  clientes: Opcion[]
  autoridades: Opcion[]
  abogados: Opcion[]
  etiquetas: Opcion[]     // catálogo
}>()
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Editar Juicio #{{ props.juicio.id }}</h1>
      <Link :href="route('juicios.index')" class="text-indigo-600 hover:underline">Volver</Link>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
      <FormJuicio
        :mode="'edit'"
        :initial="props.juicio"
        :clientes="props.clientes"
        :autoridades="props.autoridades"
        :abogados="props.abogados"
        :etiquetas="props.etiquetas"
        :method="'put'"
        :submit-route="{ name: 'juicios.update', params: { juicio: props.juicio.id } }"
      />
    </div>
  </div>
</template>
