<script setup>
import { router, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import TopNavLayout from '@/layouts/TopNavLayout.vue'
// OJO: aquí pon el path real según tu proyecto
// si tu carpeta es "components" en minúsculas, cambia esto:
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  etapas: Object,
  filters: Object,
  clientes: Array
})

const f = ref({
  desde: props.filters.desde || '',
  hasta: props.filters.hasta || '',
  estatus: props.filters.estatus || '',
  juicio: props.filters.juicio || '',
  cliente: props.filters.cliente || '',
})

function buscar() {
  router.get(route('etapas.lista'), f.value, { preserveScroll: true })
}

const fmtDate = (v) =>
  v ? new Intl.DateTimeFormat('es-MX').format(new Date(v)) : '—'
</script>

<template>
  <TopNavLayout />

  <div class="p-6 space-y-6">
    <div>
      <h1 class="text-3xl font-semibold text-gray-800">
        Etapas de Juicios
      </h1>
      <p class="text-gray-600">Consulta todas las etapas registradas, filtradas por vencimiento, juicio o cliente.</p>
    </div>

    <!-- FILTROS -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 bg-white p-4 rounded shadow">
      <div>
        <label>Desde</label>
        <input type="date" v-model="f.desde" class="w-full border rounded p-2" />
      </div>

      <div>
        <label>Hasta</label>
        <input type="date" v-model="f.hasta" class="w-full border rounded p-2" />
      </div>

      <div>
        <label>Nombre juicio</label>
        <input v-model="f.juicio" type="text" class="w-full border rounded p-2" placeholder="Ej. HOSPITAL CMQ" />
      </div>

      <div>
        <label>Cliente</label>
        <select v-model="f.cliente" class="w-full border rounded p-2">
          <option value="">Todos</option>
          <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre }}</option>
        </select>
      </div>

      <div>
        <label>Estatus</label>
        <select v-model="f.estatus" class="w-full border rounded p-2">
          <option value="">Todos</option>
          <option value="en_tramite">En trámite</option>
          <option value="en_juicio">En juicio</option>
          <option value="concluido">Concluido</option>
          <option value="cancelado">Cancelado</option>
        </select>
      </div>

      <div class="flex items-end">
        <button
          @click="buscar"
          class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700"
        >
          Buscar
        </button>
      </div>
    </div>

    <!-- TABLA -->
    <div class="bg-white rounded shadow overflow-x-auto">
      <table class="min-w-full text-sm text-gray-900">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-2">Fecha vencimiento</th>
            <th class="px-4 py-2">Juicio</th>
            <th class="px-4 py-2">Cliente</th>
            <th class="px-4 py-2">Etapa</th>
            <th class="px-4 py-2">Estatus</th>
            <th class="px-4 py-2">Comentarios</th>
            <th class="px-4 py-2">Archivo</th>
          </tr>
        </thead>

     <tbody>
  <tr v-for="e in etapas.data" :key="e.id" class="border-t">
    <!-- FECHA -->
    <td class="px-4 py-2">
      {{ fmtDate(e.fecha_vencimiento) }}
    </td>

    <!-- JUICIO -->
    <td class="px-4 py-2">
      <Link
        :href="route('juicios.show', e.juicio_id)"
        class="text-blue-600 hover:underline"
      >
        {{ e.nombre_juicio || '—' }}
      </Link>
    </td>

    <!-- CLIENTE -->
    <td class="px-4 py-2">
      {{ e.nombre_cliente || '—' }}
    </td>

    <!-- ETAPA -->
    <td class="px-4 py-2">
      {{ e.etapa }}
    </td>

    <!-- ESTATUS -->
    <td class="px-4 py-2">
      <span
        class="px-2 py-1 rounded text-xs"
        :class="{
          'bg-yellow-200': e.estatus === 'en_tramite',
          'bg-blue-200': e.estatus === 'en_juicio',
          'bg-green-200': e.estatus === 'concluido',
          'bg-gray-300': e.estatus === 'cancelado',
        }"
      >
        {{ e.estatus }}
      </span>
    </td>

    <!-- COMENTARIOS -->
    <td class="px-4 py-2">
      {{ e.comentarios || '—' }}
    </td>

    <!-- ARCHIVO -->
    <td class="px-4 py-2">
      <a
        v-if="e.archivo_path"
        :href="`/storage/${e.archivo_path}`"
        target="_blank"
        class="text-blue-600 hover:underline"
      >
        Ver
      </a>
      <span v-else>—</span>
    </td>
  </tr>

  <tr v-if="etapas.data.length === 0">
    <td colspan="7" class="text-center py-4 text-gray-500">
      No se encontraron etapas.
    </td>
  </tr>
</tbody>

      </table>
    </div>

    <!-- PAGINACIÓN -->
    <Pagination :links="etapas.links" />
  </div>
</template>
