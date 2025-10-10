<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import FormJuicio from './components/FormJuicio.vue';

type Opcion = { id:number; nombre:string }

const props = defineProps<{
  juicio: {
    id: number
    nombre: string
    tipo: 'nulidad' | 'revocacion'
    cliente_id: number | null
    autoridad_id: number | null
    fecha_inicio: string | null
    monto: number | string | null
    observaciones_monto: string | null
    resolucion_impugnada: string | null
    garantia: string | null
    numero_juicio: string | null
    numero_expediente: string | null
    estatus: 'juicio'|'autorizado'|'en_proceso'|'concluido'
    abogado_id: number | null
    // periodos puede venir como mapa JSON desde BD ({"2024":[1,2]})
    periodos?: Record<string, number[]> | null
  }
  clientes: Opcion[]
  autoridades: Opcion[]
  abogados: Opcion[]
  etiquetas: Opcion[]
  // ids ya ligados en la pivot
  etiquetasSeleccionadas: number[]
}>()

/** Normaliza el JSON guardado {"2024":[1,2], "2025":[3]} a
 *  arreglo [{anio:2024, meses:[1,2]}, {anio:2025, meses:[3]}]
 */
function toPeriodosArray(p?: Record<string, number[]> | null) {
  if (!p) return [] as { anio:number; meses:number[] }[]
  return Object.entries(p).map(([anio, meses]) => ({
    anio: Number(anio),
    meses: (meses ?? []).map(Number).sort((a,b)=>a-b),
  }))
}

const form = useForm({
  id: props.juicio.id,
  nombre: props.juicio.nombre ?? '',
  tipo: props.juicio.tipo ?? 'nulidad',
  cliente_id: props.juicio.cliente_id ?? '',
  autoridad_id: props.juicio.autoridad_id ?? '',
  fecha_inicio: props.juicio.fecha_inicio ?? '',
  monto: props.juicio.monto ?? '',
  observaciones_monto: props.juicio.observaciones_monto ?? '',
  resolucion_impugnada: props.juicio.resolucion_impugnada ?? '',
  garantia: props.juicio.garantia ?? '',
  numero_juicio: props.juicio.numero_juicio ?? '',
  numero_expediente: props.juicio.numero_expediente ?? '',
  estatus: props.juicio.estatus ?? 'juicio',
  abogado_id: props.juicio.abogado_id ?? '',
  etiquetas: props.etiquetasSeleccionadas ?? [],
  // 👇 importante para que FormJuicio tenga un array utilizable
  periodos: toPeriodosArray(props.juicio.periodos),
})

function submit() {
  // PUT a la ruta de actualización
  const url = typeof route === 'function'
    ? route('juicios.update', { juicio: props.juicio.id })
    : `/juicios/${props.juicio.id}`

  form.put(url)
}
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Editar Juicio #{{ props.juicio.id }}</h1>
      <Link :href="route('juicios.index')" class="text-indigo-600 hover:underline">Volver</Link>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
      <!-- 👇 Pasar el form como PROP, no v-model -->
      <FormJuicio
        :form="form"
        :clientes="props.clientes"
        :autoridades="props.autoridades"
        :abogados="props.abogados"
        :etiquetas="props.etiquetas"
        @submit="submit"
      />
    </div>
  </div>
</template>
