<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import FormJuicio from './components/FormJuicio.vue';
import TopNavLayout from '@/layouts/TopNavLayout.vue';
import { ref, watch } from 'vue'

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
  // Inertia suele mandar strings; forzamos a number[]
  form.etiquetas = (form.etiquetas || []).map(Number)
  form.post(route('juicios.store'))
}
const displayMonto = ref('')

watch(() => form.monto, (val) => {
  if (val === '' || val === null || typeof val === 'undefined') {
    displayMonto.value = ''
  } else {
    displayMonto.value = formatNumber(val)
  }
}, { immediate: true })

function formatMonto() {
  const raw = (displayMonto.value ?? '').toString().replace(/[^\d.]/g, '')
  const num = parseFloat(raw || '0')
  form.monto = isNaN(num) ? '' : num
  displayMonto.value = form.monto === '' ? '' : formatNumber(num)
}

function formatNumber(val: number | string) {
  const num = Number(val)
  if (isNaN(num)) return ''
  // Cambia 'en-US' por 'es-MX' si quieres formato local
  return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
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
