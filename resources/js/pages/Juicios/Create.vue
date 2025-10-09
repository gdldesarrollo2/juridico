<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import FormJuicio from './components/FormJuicio.vue';
import TopNavLayout from '@/layouts/TopNavLayout.vue';
defineProps<{
  clientes: { id: number; nombre: string }[]
  autoridades: { id: number; nombre: string }[]
  abogados: { id: number; nombre: string }[]
  etiquetas: { id: number; nombre: string }[]
}>()

const form = useForm({
  nombre: '',
  tipo: 'nulidad',
  cliente_id: '',
  autoridad_id: '',
  fecha_inicio: '',
  monto: '',
  observaciones_monto: '',
  resolucion_impugnada: '',
  garantia: '',
  numero_juicio: '',
  numero_expediente: '',
  estatus: 'juicio',
  abogado_id: '',
  etiquetas: [] as number[],
  periodos: [] as { anio:number; meses:number[] }[],
})

function submit() {
  form.post(route('juicios.store'))
}
</script>

<template>
    <TopNavLayout>

  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold mb-4">Nuevo Juicio</h1>

    <FormJuicio
      :form="form"
      :clientes="clientes"
      :autoridades="autoridades"
      :abogados="abogados"
      :etiquetas="etiquetas"
      mode="create"
      submitRoute="juicios.store"
      method="post"
      @submit="submit"
    />
  </div>
  </TopNavLayout>
</template>
