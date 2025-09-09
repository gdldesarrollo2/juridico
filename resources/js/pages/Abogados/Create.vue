<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue'

const form = useForm({
  nombre: '',
  estatus: 'activo' as 'activo'|'inactivo',
})
function submit() {
  form.post(route('abogados.store'))
}
</script>

<template>
  <TopNavLayout>
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm max-w-xl">
      <div class="flex items-center justify-between mb-3">
        <h1 class="text-xl font-semibold">Nuevo abogado</h1>
        <Link :href="route('abogados.index')" class="px-3 py-2 rounded-md border text-sm">Volver</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Nombre</label>
          <input v-model="form.nombre" type="text"
                 class="mt-1 w-full border rounded px-3 py-2"
                 :class="{'border-red-500': form.errors.nombre}">
          <p v-if="form.errors.nombre" class="text-red-600 text-xs mt-1">{{ form.errors.nombre }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Estatus</label>
          <select v-model="form.estatus" class="mt-1 w-full border rounded px-3 py-2">
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
          </select>
          <p v-if="form.errors.estatus" class="text-red-600 text-xs mt-1">{{ form.errors.estatus }}</p>
        </div>

        <div class="flex justify-end gap-2">
          <button type="button" @click="form.reset()" class="px-3 py-2 rounded-md border text-sm">
            Limpiar
          </button>
          <button type="submit" :disabled="form.processing"
                  class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50">
            Guardar
          </button>
        </div>
      </form>
    </div>
  </TopNavLayout>
</template>
