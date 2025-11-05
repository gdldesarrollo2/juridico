<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue';
const props = defineProps<{
  users: Array<{ id:number; name:string; email:string; roles:string[] }>
}>()
</script>

<template>
    <TopNavLayout></TopNavLayout>

  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-semibold">Usuarios y Roles</h1>
     <Link
              :href="route('roles.index')"
              class="px-3 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-700 "
            >
              Roles de usuario
            </Link>
             <Link
        :href="route('users.roles.create')"
        class="px-4 py-2 rounded bg-indigo-600 text-white text-sm font-medium"
      >
        Nuevo usuario
      </Link><br>
    <table class="min-w-full text-sm bg-white rounded shadow">
      <thead class="bg-gray-100">
        <tr>
          <th class="text-left px-4 py-2">Nombre</th>
          <th class="text-left px-4 py-2">Email</th>
          <th class="text-left px-4 py-2">Roles</th>
          <th class="text-left px-4 py-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="u in users" :key="u.id" class="border-t">
          <td class="px-4 py-2">{{ u.name }}</td>
          <td class="px-4 py-2">{{ u.email }}</td>
          <td class="px-4 py-2">
            <span v-if="u.roles.length === 0" class="text-gray-500">—</span>
            <span v-else class="flex flex-wrap gap-1">
              <span
                v-for="r in u.roles"
                :key="r"
                class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 text-xs"
              >
                {{ r }}
              </span>
            </span>
          </td>
          <td class="px-4 py-2">
            <Link
              :href="route('users.roles.edit', u.id)"
              class="px-3 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-700 text-xs"
            >
              Asignar rol
            </Link>
          </td>
        </tr>
        <tr v-if="users.length === 0">
          <td colspan="4" class="px-4 py-6 text-center text-gray-500">No hay usuarios</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
