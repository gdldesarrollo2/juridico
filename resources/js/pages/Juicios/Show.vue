<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue'
type Etapa = {
  id:number
  etiqueta?: { nombre:string } | null
  etapa:string
  usuario?: { name:string } | null
  rol?: string|null
  comentarios?: string|null
  dias_vencimiento:number
  fecha_inicio?: string|null
  fecha_vencimiento?: string|null
  estatus:string
  archivo_path?: string|null
  created_at:string
}

type Hist = {
  id:number
  abogado?: { nombre:string } | null
  usuario?: { name:string } | null
  motivo?: string|null
  asignado_desde?: string|null
  asignado_hasta?: string|null
  created_at:string
}

const props = defineProps<{
  juicio: {
    id:number
    numero_juicio?:string|null
    numero_expediente?:string|null
    nombre:string
    tipo:string
    cliente?:string|null
    autoridad?:string|null
    abogado?:string|null
    estatus:string
    monto?:number|null
    fecha_inicio?:string|null
    observaciones_monto?:string|null
    resolucion_impugnada?:string|null
    garantia?:string|null
    etiquetas:string[]
    periodo?:string|null
  }
  etapas: Etapa[]
  historial: Hist[]
}>()

const money = (n?: number|null) =>
  (n ?? null) === null ? '—'
  : new Intl.NumberFormat('es-MX', { style:'currency', currency:'MXN' }).format(n!)
const dmx = (d?: string|null) => d ? new Date(d).toLocaleDateString('es-MX') : '—'
</script>

<template>
<TopNavLayout></TopNavLayout>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Juicio #{{ juicio.id }}</h1>
      <div class="space-x-2">
        <Link :href="route('juicios.index')" class="px-3 py-1.5 rounded border">Volver</Link>
        <Link :href="route('juicios.edit', juicio.id)" class="px-3 py-1.5 rounded bg-indigo-600 text-white">Editar</Link>
      </div>
    </div>

    <!-- Resumen -->
    <div class="bg-white rounded shadow p-4">
      <h2 class="font-semibold mb-3">Información general</h2>
      <div class="grid md:grid-cols-2 gap-4 text-sm">
        <div><span class="font-medium">Nombre:</span> {{ juicio.nombre }}</div>
        <div><span class="font-medium">Tipo:</span> {{ juicio.tipo }}</div>
        <div><span class="font-medium">Cliente:</span> {{ juicio.cliente ?? '—' }}</div>
        <div><span class="font-medium">Autoridad:</span> {{ juicio.autoridad ?? '—' }}</div>
        <div><span class="font-medium">Abogado actual:</span> {{ juicio.abogado ?? '—' }}</div>
        <div><span class="font-medium">Estatus:</span> {{ juicio.estatus }}</div>
        <div><span class="font-medium">Fecha inicio:</span> {{ dmx(juicio.fecha_inicio) }}</div>
        <div><span class="font-medium">Monto:</span> {{ money(juicio.monto) }}</div>
        <div><span class="font-medium">Número de juicio:</span> {{ juicio.numero_juicio ?? '—' }}</div>
        <div><span class="font-medium">Número de expediente:</span> {{ juicio.numero_expediente ?? '—' }}</div>
        <div class="md:col-span-2">
          <span class="font-medium">Periodo:</span> {{ juicio.periodo ?? '—' }}
        </div>
        <div class="md:col-span-2" v-if="juicio.etiquetas?.length">
          <span class="font-medium">Etiquetas:</span>
          <span class="inline-flex gap-2 flex-wrap">
            <span v-for="e in juicio.etiquetas" :key="e" class="px-2 py-0.5 rounded bg-slate-100">{{ e }}</span>
          </span>
        </div>
        <div class="md:col-span-2" v-if="juicio.resolucion_impugnada">
          <span class="font-medium">Resolución impugnada:</span> {{ juicio.resolucion_impugnada }}
        </div>
        <div class="md:col-span-2" v-if="juicio.garantia">
          <span class="font-medium">Garantía:</span> {{ juicio.garantia }}
        </div>
        <div class="md:col-span-2" v-if="juicio.observaciones_monto">
          <span class="font-medium">Observaciones del monto:</span> {{ juicio.observaciones_monto }}
        </div>
      </div>
    </div>

    <!-- Etapas -->
    <div class="bg-white rounded shadow p-4">
      <h2 class="font-semibold mb-3">Etapas</h2>
      <div class="overflow-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left px-3 py-2">Fecha vencimiento</th>
              <th class="text-left px-3 py-2">Etiqueta</th>
              <th class="text-left px-3 py-2">Etapa</th>
              <th class="text-left px-3 py-2">Usuario</th>
              <th class="text-left px-3 py-2">Estatus</th>
              <th class="text-left px-3 py-2">Comentarios</th>
              <th class="text-left px-3 py-2">Archivo</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in etapas" :key="e.id" class="border-t">
              <td class="px-3 py-2">{{ dmx(e.fecha_vencimiento) }}</td>
              <td class="px-3 py-2">{{ e.etiqueta?.nombre ?? '—' }}</td>
              <td class="px-3 py-2">{{ e.etapa }}</td>
              <td class="px-3 py-2">{{ e.abogado?.usuario?.name ?? e.abogado?.nombre ?? '—' }}</td>
              <td class="px-3 py-2">{{ e.estatus }}</td>
              <td class="px-3 py-2">{{ e.comentarios ?? '—' }}</td>
              <td class="px-3 py-2">
                <a v-if="e.archivo_path" :href="`/storage/${e.archivo_path}`" target="_blank" class="text-blue-600 hover:underline">Ver</a>
                <span v-else>—</span>
              </td>
            </tr>
            <tr v-if="etapas.length===0">
              <td colspan="7" class="px-3 py-6 text-center text-gray-500">Sin etapas registradas</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Historial de abogados -->
    <div class="bg-white rounded shadow p-4">
      <h2 class="font-semibold mb-3">Historial de abogados</h2>
      <div class="overflow-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left px-3 py-2">Abogado</th>
              <th class="text-left px-3 py-2">Fecha asignación</th>
              <th class="text-left px-3 py-2">Fecha fin</th>
              <th class="text-left px-3 py-2">Motivo</th>
              <th class="text-left px-3 py-2">Capturó</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="h in historial" :key="h.id" class="border-t">
              <td class="px-3 py-2">{{ h.abogado?.nombre ?? '—' }}</td>
              <td class="px-3 py-2">{{ dmx(h.asignado_desde) }}</td>
              <td class="px-3 py-2">{{ dmx(h.asignado_hasta) }}</td>
              <td class="px-3 py-2">{{ h.motivo ?? '—' }}</td>
              <td class="px-3 py-2">{{ h.usuario?.name ?? '—' }}</td>
            </tr>
            <tr v-if="historial.length===0">
              <td colspan="5" class="px-3 py-6 text-center text-gray-500">Sin historial registrado</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>
