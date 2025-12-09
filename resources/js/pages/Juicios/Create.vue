<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import FormJuicio from './components/FormJuicio.vue';
import ModalCliente from '../Clientes/ModalCliente.vue';
import ModalAutoridad from '../Autoridad/ModalAutoridad.vue';
import TopNavLayout from '@/layouts/TopNavLayout.vue';
import { ref, watch } from 'vue'

const mostrarModalAutoridad = ref(false)
function autoridadCreada(a) {
  props.autoridades.push(a)
  form.autoridad_id = a.id
  mostrarModalAutoridad.value = false
}
const props = defineProps<{
  clientes: { id: number; nombre: string }[]
  autoridades: { id: number; nombre: string }[]
  abogados: { id: number; nombre: string }[]
  etiquetas: { id: number; nombre: string }[]
}>()

// FORMULARIO PRINCIPAL
const form = useForm({
  nombre: '',
  tipo: '',
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

// MODAL
const mostrarModalCliente = ref(false)

// Cuando el modal crea un cliente nuevo
function clienteCreado(cliente: { id:number; nombre:string }) {

  // Agregamos directamente el cliente a la lista recibida por props
  props.clientes.push(cliente)

  // Seleccionarlo automáticamente
  form.cliente_id = cliente.id

  // Cerramos modal
  mostrarModalCliente.value = false
}

function submit() {
  form.etiquetas = (form.etiquetas || []).map(Number)
  form.post(route('juicios.store'))
}

// Monto
const displayMonto = ref('')

watch(() => form.monto, (val) => {
  displayMonto.value = val ? formatNumber(val) : ''
}, { immediate: true })

function formatMonto() {
  const raw = (displayMonto.value ?? '').replace(/[^\d.]/g, '')
  const num = parseFloat(raw || '0')
  form.monto = isNaN(num) ? '' : num
  displayMonto.value = form.monto === '' ? '' : formatNumber(num)
}

function formatNumber(val: number | string) {
  const num = Number(val)
  if (isNaN(num)) return ''
  return num.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}
</script>

<template>
  <TopNavLayout>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold mb-4">Asunto o Expediente</h1>

      <!-- 🌟 BOTÓN PARA ABRIR EL MODAL -->
      <button
        @click="mostrarModalCliente = true"
        class="px-4 py-2 bg-indigo-600 text-white rounded shadow hover:bg-indigo-700"
      >
        + Agregar cliente
      </button>
      <button @click="mostrarModalAutoridad = true"
        class="m-2 px-4 py-2 bg-indigo-600 text-white rounded">
  + Agregar autoridad
</button>

<ModalAutoridad
  v-if="mostrarModalAutoridad"
  @cerrar="mostrarModalAutoridad = false"
  @autoridad-creada="autoridadCreada"
/>

      <!-- 🌟 MODAL PARA CREAR CLIENTE -->
      <ModalCliente
        v-if="mostrarModalCliente"
        @cerrar="mostrarModalCliente = false"
        @cliente-creado="clienteCreado"
      />

      <!-- 🌟 FORMULARIO PRINCIPAL -->
      <FormJuicio
        :form="form"
        :clientes="props.clientes"
        :autoridades="props.autoridades"
        :abogados="props.abogados"
        :etiquetas="props.etiquetas"
        mode="create"
        submitRoute="juicios.store"
        method="post"
        @submit="submit"
      />
    </div>
  </TopNavLayout>
</template>
