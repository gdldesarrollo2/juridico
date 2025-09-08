<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/TopNavLayout.vue'



const props = defineProps<{
  usuarios: Array<{ id:number; name:string }>
  defaults: { estatus: 'activo'|'inactivo'; usuario_id: number|null }
}>()

const form = useForm({
  nombre: '',
  estatus: props.defaults.estatus ?? 'activo',
})

const canSave = computed(() => form.nombre.trim().length > 0)

function submit() {
  form.post(route('clientes.store'))
}
</script>

<template>
      <AppLayout></AppLayout>

  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-900">Nuevo cliente</h1>
      <Link :href="route('clientes.index')"
            class="px-3 py-2 rounded border text-sm dark:border-gray-600 dark:text-gray-900">
        Volver
      </Link>
    </div>

    <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded shadow p-6 space-y-4">
      <!-- Nombre -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre / Razón social</label>
        <input v-model="form.nombre" type="text"
               class="mt-1 w-full border rounded px-3 py-2
                      bg-white text-gray-900
                      dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700"
               :class="{ 'border-red-500': form.errors.nombre }" />
        <p v-if="form.errors.nombre" class="text-red-400 text-xs">{{ form.errors.nombre }}</p>
      </div>

      <!-- Estatus -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estatus</label>
        <select v-model="form.estatus"
                class="mt-1 w-full border rounded px-3 py-2
                       bg-white text-gray-900
                       dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
          <option value="activo">Activo</option>
          <option value="inactivo">Inactivo</option>
        </select>
      </div>

    

      <!-- Botones -->
      <div class="flex justify-end gap-2">
        <button type="button"
                @click="form.reset(); form.estatus='activo'; form.usuario_id=props.defaults.usuario_id ?? ''"
                class="px-3 py-2 rounded border text-sm dark:border-gray-600 dark:text-gray-100">
          Limpiar
        </button>
        <button type="submit"
                :disabled="form.processing || !canSave"
                class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50">
          Guardar
        </button>
      </div>
    </form>
  </div>
</template>
