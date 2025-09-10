<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue'

const props = defineProps<{
  revisiones: {
    data: Array<{
      id:number
      tipo:string
      sociedad:{ id:number, nombre:string }
      autoridad:{ id:number, nombre:string }
      periodo_inicio:string
      periodo_fin:string
      estatus:string
    }>
    links: Array<{ url:string|null, label:string, active:boolean }>
  }
}>()

function destroyRow(id:number) {
  if (!confirm('¿Eliminar revisión?')) return
  router.delete(route('revisiones.destroy', id))
}
</script>

<template>
  <TopNavLayout>
      </TopNavLayout>

    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
      <div class="flex items-center justify-between mb-3">
        <h1 class="text-xl font-semibold">Revisiones</h1>
        <Link :href="route('revisiones.create')"
              class="px-3 py-2 rounded-md text-sm bg-blue-600 text-white hover:bg-blue-700">
          Nueva revisión
        </Link>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="text-left px-4 py-2">Tipo</th>
              <th class="text-left px-4 py-2">Sociedad</th>
              <th class="text-left px-4 py-2">Autoridad</th>
              <th class="text-left px-4 py-2">Periodo</th>
              <th class="text-left px-4 py-2">Estatus</th>
              <th class="text-right px-4 py-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in props.revisiones.data" :key="r.id" class="border-t">
              <td class="px-4 py-2">{{ r.tipo }}</td>
              <td class="px-4 py-2">{{ r.sociedad?.nombre ?? '—' }}</td>
              <td class="px-4 py-2">{{ r.autoridad?.nombre ?? '—' }}</td>
              <td class="px-4 py-2">
                {{ r.periodo_inicio }} al {{ r.periodo_fin }}
              </td>
              <td class="px-4 py-2">
                <span class="px-2 py-0.5 rounded text-xs"
                      :class="r.estatus === 'EN JUICIO'
                        ? 'bg-blue-100 text-blue-800'
                        : 'bg-gray-200 text-gray-800'">
                  {{ r.estatus }}
                </span>
              </td>
              <td class="px-4 py-2 text-right">
                <div class="inline-flex gap-2">
                  <Link :href="route('revisiones.edit', r.id)"
                        class="px-2 py-1 rounded border text-xs hover:bg-gray-50">
                    Editar
                  </Link>
                 
                  <button @click="destroyRow(r.id)"
                          class="px-2 py-1 rounded border text-xs text-red-600 hover:bg-red-50">
                    Eliminar
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="props.revisiones.data.length === 0">
              <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                Sin registros
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="flex flex-wrap items-center gap-1 mt-3">
        <Link v-for="l in props.revisiones.links" :key="l.label" :href="l.url || ''"
              v-html="l.label"
              class="px-3 py-1.5 rounded border text-sm"
              :class="l.active ? 'bg-gray-900 text-white border-gray-900' : 'hover:bg-gray-50'"/>
      </div>
    </div>
</template>
