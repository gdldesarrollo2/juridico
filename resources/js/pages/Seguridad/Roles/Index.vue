<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue';

const props = defineProps<{
  roles: Array<{ id:number; name:string; permissions_count:number }>
}>()
</script>

<template>
  <TopNavLayout></TopNavLayout>
  <div class="p-6 space-y-4">
    <h1 class="text-2xl font-semibold">Roles</h1>

    <table class="min-w-full text-sm bg-white rounded shadow">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left p-3">Rol</th>
          <th class="text-left p-3"># Permisos</th>
          <th class="text-left p-3">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="r in props.roles" :key="r.id" class="border-t">
          <td class="p-3">{{ r.name }}</td>
          <td class="p-3">{{ r.permissions_count }}</td>
          <td class="p-3">
            <Link :href="route('roles.edit', r.id)" class="px-3 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-700">
              Editar permisos
            </Link>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
