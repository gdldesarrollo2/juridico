<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import TopNavLayout from '@/layouts/TopNavLayout.vue';
type AbogadoRow = {
  id: number
  nombre: string
  estatus: 'activo'|'inactivo'
  email: string,
  juicios_count: number
}
type Auth = {
  user?: { id:number; name:string; email?:string } | null
  roles?: string[]
  can?: string[]
}
const props = defineProps<{
    auth?: Auth,                     // 👈 llega desde HandleInertiaRequests.share
  abogados: { data: AbogadoRow[], links:any[] }
}>()
const canList = computed<string[]>(() => props.auth?.can ?? [])
const $can = (perm: string) => canList.value.includes(perm)
</script>

<template>
  <TopNavLayout></TopNavLayout>
  <div class="p-6 bg-white rounded shadow">
    <div class="flex items-center justify-between p-4 border-b">
      <h1 class="text-xl font-semibold">Abogados</h1>
      <Link :href="route('abogados.create')" class="px-3 py-1.5 rounded bg-indigo-600 text-white">
        Nuevo abogado
      </Link>
    </div>

    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left px-4 py-2">Nombre</th>
          <th class="text-left px-4 py-2">Estatus</th>
          <th class="text-left px-4 py-2">Email</th>
          <th class="text-left px-4 py-2">Juicios</th>
          <th class="text-left px-4 py-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="a in props.abogados.data" :key="a.id" class="border-t">
          <td class="px-4 py-2">{{ a.nombre }}</td>
          <td class="px-4 py-2">
            <span :class="a.estatus==='activo'
                          ? 'bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs'
                          : 'bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs'">
              {{ a.estatus }}
            </span>
          </td>
          <td class="px-4 py-2">  {{ a.usuario?.email ?? '—' }}</td>
          <td class="px-4 py-2">{{ a.juicios_count }}</td>

          <td class="px-4 py-2 space-x-2">
            <Link v-if="$can('ver abogados')"  :href="route('abogados.edit', a.id)" class="px-3 py-1 rounded border">Editar</Link>

           <Link
  v-if="a.juicios_count > 0 && $can('reasignar abogado')" 
  :href="route('abogados.reasignar.form', a.id)"
  class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700"
>
  Reasignar <span class="ml-1 text-xs opacity-80">({{ a.juicios_count }})</span>
</Link>
<span v-else class="px-3 py-1 rounded bg-gray-100 text-gray-400">Reasignar</span>

          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>