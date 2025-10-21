<!-- resources/js/Pages/Clientes/Index.vue -->
<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/TopNavLayout.vue'
type Auth = {
  user?: { id:number; name:string; email?:string } | null
  roles?: string[]
  can?: string[]
}
const props = defineProps<{     auth?: Auth,                     // 👈 llega desde HandleInertiaRequests.share
clientes: any }>()
const canList = computed<string[]>(() => props.auth?.can ?? [])
const $can = (perm: string) => canList.value.includes(perm)
</script>

<template>
  <AppLayout></AppLayout>
  <div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold dark:text-gray-600">Clientes</h1>
      <Link  v-if="$can('nuevo cliente')" :href="route('clientes.create')" class="px-3 py-2 rounded bg-blue-600 text-white">Nuevo</Link>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 dark:text-gray-100">
          <tr>
            <th class="text-left px-4 py-2">Nombre</th>
            <th class="text-left px-4 py-2">Estatus</th>
            <th class="text-left px-4 py-2">Usuario</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in props.clientes.data" :key="c.id" class="border-t dark:border-gray-700">
            <td class="px-4 py-2 text-white">{{ c.nombre }}</td>
            <td class="px-4 py-2">
              <span class="px-2 py-0.5 rounded text-xs"
                    :class="c.estatus==='activo'
                      ? 'bg-green-100 text-green-800 dark:bg-green-200/20 dark:text-green-300'
                      : 'bg-gray-200 text-gray-800 dark:bg-gray-500/30 dark:text-gray-200'">
                {{ c.estatus }}
              </span>
            </td>
            <td class="px-4 py-2 text-white">{{ c.usuario?.name ?? '—' }}</td>
          </tr>
          <tr v-if="props.clientes.data.length===0">
            <td colspan="3" class="px-4 py-6 text-center text-gray-500">Sin clientes</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
