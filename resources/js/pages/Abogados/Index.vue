<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import TopNavLayout from '@/Layouts/TopNavLayout.vue'

const props = defineProps<{
  abogados: {
    data: Array<{
      id:number
      nombre:string
      estatus:'activo'|'inactivo'
      usuario?: { id:number, name:string } | null
    }>
    links: Array<{ url:string|null, label:string, active:boolean }>
  }
}>()

function destroyRow(id:number) {
  if (!confirm('¿Eliminar abogado?')) return
  router.delete(route('abogados.destroy', id))
}
</script>

<template>
  <TopNavLayout>
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
      <div class="flex items-center justify-between mb-3">
        <h1 class="text-xl font-semibold">Abogados</h1>
        <Link :href="route('abogados.create')"
              class="px-3 py-2 rounded-md text-sm bg-indigo-600 text-white hover:bg-indigo-700">
          Nuevo abogado
        </Link>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="text-left px-4 py-2">Nombre</th>
              <th class="text-left px-4 py-2">Estatus</th>
              <th class="text-left px-4 py-2">Usuario</th>
              <th class="text-right px-4 py-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in props.abogados.data" :key="a.id" class="border-t">
              <td class="px-4 py-2">{{ a.nombre }}</td>
              <td class="px-4 py-2">
                <span
                  class="px-2 py-0.5 rounded text-xs"
                  :class="a.estatus==='activo'
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-200 text-gray-800'">
                  {{ a.estatus }}
                </span>
              </td>
              <td class="px-4 py-2">{{ a.usuario?.name ?? '—' }}</td>
              <td class="px-4 py-2 text-right">
                <div class="inline-flex gap-2">
                  <Link :href="route('abogados.edit', a.id)"
                        class="px-2 py-1 rounded border text-xs hover:bg-gray-50">
                    Editar
                  </Link>
                  <!-- <button @click="destroyRow(a.id)"
                          class="px-2 py-1 rounded border text-xs text-red-600 hover:bg-red-50">
                    Eliminar
                  </button> -->
                </div>
              </td>
            </tr>
            <tr v-if="props.abogados.data.length===0">
              <td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin registros</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="flex flex-wrap items-center gap-1 mt-3">
        <Link v-for="l in props.abogados.links" :key="l.label" :href="l.url || ''"
              v-html="l.label"
              class="px-3 py-1.5 rounded border text-sm"
              :class="l.active ? 'bg-gray-900 text-white border-gray-900' : 'hover:bg-gray-50'"/>
      </div>
    </div>
  </TopNavLayout>
</template>
