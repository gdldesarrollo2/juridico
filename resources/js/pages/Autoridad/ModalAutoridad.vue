<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { onMounted, onBeforeUnmount } from 'vue'

const emit = defineEmits(['cerrar', 'autoridad-creada'])

const form = useForm({
  nombre: '',
  estatus: 'activo',
})

const handleKey = (e) => {
  if (e.key === 'Escape') emit('cerrar')
}

onMounted(() => window.addEventListener('keydown', handleKey))
onBeforeUnmount(() => window.removeEventListener('keydown', handleKey))

function guardar() {
  form.post(route('autoridades.store'), {
    onSuccess: (response) => {
      const autoridad = response?.props?.flash?.autoridadNueva
      if (autoridad) emit('autoridad-creada', autoridad)
      emit('cerrar')
    }
  })
}
</script>

<template>
  <div class="fixed inset-0 bg-black/50 flex justify-center items-center z-50" @click="emit('cerrar')">
    <div class="bg-white dark:bg-gray-800 rounded shadow p-6 w-full max-w-xl" @click.stop>
      <button class="absolute top-2 right-2 text-gray-500" @click="emit('cerrar')">✕</button>

      <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-200 mb-4">Nueva autoridad</h1>

      <form @submit.prevent="guardar" class="space-y-4">

        <!-- Nombre -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
          <input v-model="form.nombre"
                 type="text"
                 class="mt-1 w-full border rounded px-3 py-2 
                        dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700"
                 :class="{ 'border-red-500': form.errors.nombre }" />

          <p v-if="form.errors.nombre" class="text-red-400 text-xs">{{ form.errors.nombre }}</p>
        </div>

        <!-- Estatus -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estatus</label>
          <select v-model="form.estatus"
                  class="mt-1 w-full border rounded px-3 py-2 
                         dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
          </select>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-2 pt-4">
          <button type="button"
                  @click="form.reset(); form.estatus='activo'"
                  class="px-3 py-2 rounded border dark:border-gray-600 dark:text-gray-100">
            Limpiar
          </button>

          <button type="submit"
                  :disabled="form.processing || !form.nombre.trim().length"
                  class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
            Guardar
          </button>
        </div>

      </form>
    </div>
  </div>
</template>
