<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { onMounted, onBeforeUnmount } from 'vue'

const emit = defineEmits(['cerrar', 'cliente-creado'])

// Formulario REAL según tu código
const form = useForm({
  nombre: '',
  estatus: 'activo',
})

// Cerrar con tecla ESC
const handleKey = (e: KeyboardEvent) => {
  if (e.key === 'Escape') emit('cerrar')
}

onMounted(() => window.addEventListener('keydown', handleKey))
onBeforeUnmount(() => window.removeEventListener('keydown', handleKey))

function guardar() {
  form.post(route('clientes.store'), {
    preserveScroll: true,
    onSuccess: (response) => {
      // Recuperar cliente desde flash o props
      const cliente = response?.props?.flash?.clienteNuevo
      if (cliente) emit('cliente-creado', cliente)

      emit('cerrar')
    }
  })
}
</script>

<template>
  <!-- Fondo oscuro -->
  <div
    class="fixed inset-0 bg-black/50 flex justify-center items-center z-50"
    @click="emit('cerrar')"
  >
    <!-- Tarjeta del modal -->
    <div
      class="bg-white dark:bg-gray-800 rounded shadow p-6 w-full max-w-xl relative"
      @click.stop
    >
      <!-- Botón cerrar -->
      <button
        class="absolute top-2 right-2 text-gray-500 hover:text-gray-300"
        @click="emit('cerrar')"
      >
        ✕
      </button>

      <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-200 mb-4">
        Nuevo cliente
      </h1>

      <form @submit.prevent="guardar" class="space-y-4">

        <!-- Nombre -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Nombre / Razón social
          </label>

          <input
            v-model="form.nombre"
            type="text"
            class="mt-1 w-full border rounded px-3 py-2
                   bg-white text-gray-900
                   dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700"
            :class="{ 'border-red-500': form.errors.nombre }"
          />

          <p v-if="form.errors.nombre" class="text-red-400 text-xs">
            {{ form.errors.nombre }}
          </p>
        </div>

        <!-- Estatus -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Estatus
          </label>

          <select
            v-model="form.estatus"
            class="mt-1 w-full border rounded px-3 py-2
                   bg-white text-gray-900
                   dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700"
          >
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
          </select>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-2 pt-4">
          <button
            type="button"
            @click="form.reset(); form.estatus='activo'"
            class="px-3 py-2 rounded border text-sm dark:border-gray-600 dark:text-gray-100"
          >
            Limpiar
          </button>

          <button
            type="submit"
            :disabled="form.processing || !form.nombre.trim().length"
            class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
          >
            Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
