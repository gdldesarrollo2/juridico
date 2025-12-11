<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  etapas: Object,
  filters: Object,
});

const f = ref({
  desde: props.filters.desde || '',
  hasta: props.filters.hasta || '',
  estatus: props.filters.estatus || '',
  juicio: props.filters.juicio || '',
  cliente: props.filters.cliente || '',
});

function buscar() {
  router.get('/etapas', f.value, { preserveScroll: true });
}
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Etapas de Juicios</h1>

    <!-- Filtros -->
    <div class="grid grid-cols-6 gap-4 mb-6">
      <input type="date" v-model="f.desde" class="border p-2" placeholder="Desde" />
      <input type="date" v-model="f.hasta" class="border p-2" placeholder="Hasta" />

      <input type="text" v-model="f.juicio" class="border p-2" placeholder="Nombre del juicio" />
      <input type="text" v-model="f.estatus" class="border p-2" placeholder="Estatus" />

      <button @click="buscar" class="bg-blue-600 text-white px-4 py-2 rounded">Buscar</button>
    </div>

    <!-- Tabla -->
    <table class="w-full table-auto border-collapse">
      <thead class="bg-gray-100">
        <tr>
          <th class="border p-2">Etapa</th>
          <th class="border p-2">Juicio</th>
          <th class="border p-2">Cliente</th>
          <th class="border p-2">Fecha vencimiento</th>
          <th class="border p-2">Estatus</th>
          <th class="border p-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="e in props.etapas.data" :key="e.id">
          <td class="border p-2">{{ e.nombre }}</td>
          <td class="border p-2">{{ e.nombre_cliente ?? '-' }}</td>
          <td class="border p-2">{{ e.nombre_juicio ?? '-'  }}</td>
          <td class="border p-2">{{ e.fecha_vencimiento }}</td>
          <td class="border p-2">
            <span :class="[
              'px-2 py-1 rounded text-white text-xs',
              e.estatus === 'pendiente' ? 'bg-yellow-500' :
              e.estatus === 'completado' ? 'bg-green-600' :
              'bg-blue-600'
            ]">
              {{ e.estatus }}
            </span>
          </td>
          <td class="border p-2 flex gap-2">
            <a :href="route('juicios.show', e.juicio_id)" class="text-blue-600 underline">
              Ver juicio
            </a>
            <a :href="route('etapas.edit', e.id)" class="text-purple-600 underline">
              Editar etapa
            </a>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Paginación -->
    <div class="mt-4">
      <Pagination :links="etapas.links" />
    </div>
  </div>
</template>
