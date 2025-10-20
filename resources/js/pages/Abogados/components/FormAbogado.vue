<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

type Usuario = { id:number; name:string; email:string }

const props = defineProps<{
  mode: 'create'|'edit'
  submitRoute: { name:string, params?:Record<string, any> } | string
  initial?: { nombre?:string; estatus?:'activo'|'inactivo'; usuario_id?:number|null }
  usuarios: Usuario[]
}>()

const form = useForm({
  nombre: props.initial?.nombre ?? '',
  estatus: props.initial?.estatus ?? 'activo',
  usuario_id: props.initial?.usuario_id ?? null as number|null,
})

function submit() {
  const url = typeof props.submitRoute === 'string'
    ? props.submitRoute
    : route(props.submitRoute.name, props.submitRoute.params ?? {})

  if (props.mode === 'create') form.post(url)
  else form.put(url)
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div>
      <label class="block text-sm font-medium">Nombre</label>
      <input v-model="form.nombre" type="text" class="border rounded px-3 py-2 w-full" />
      <p v-if="form.errors.nombre" class="text-red-600 text-xs">{{ form.errors.nombre }}</p>
    </div>

    <div>
      <label class="block text-sm font-medium">Estatus</label>
      <select v-model="form.estatus" class="border rounded px-3 py-2 w-full">
        <option value="activo">Activo</option>
        <option value="inactivo">Inactivo</option>
      </select>
      <p v-if="form.errors.estatus" class="text-red-600 text-xs">{{ form.errors.estatus }}</p>
    </div>

    <div>
      <label class="block text-sm font-medium">Usuario (opcional)</label>
      <select v-model="form.usuario_id" class="border rounded px-3 py-2 w-full">
        <option :value="null">— Sin usuario —</option>
        <option v-for="u in usuarios" :key="u.id" :value="u.id">
          {{ u.name }} ({{ u.email }})
        </option>
      </select>
      <p v-if="form.errors.usuario_id" class="text-red-600 text-xs">{{ form.errors.usuario_id }}</p>
    </div>

    <div class="pt-2">
      <button :disabled="form.processing"
              class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50">
        {{ mode === 'create' ? 'Crear' : 'Actualizar' }}
      </button>
    </div>
  </form>
</template>
