<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps<{
  user: { id:number; name:string; email:string }
  roles: Array<{ id:number; name:string }>
  selected: string[] // nombres de roles actuales
}>()

const form = useForm({
  roles: [...props.selected],
})

function toggle(name: string) {
  const i = form.roles.indexOf(name)
  i === -1 ? form.roles.push(name) : form.roles.splice(i, 1)
}

function submit() {
  form.put(route('users.roles.update', props.user.id))
}
</script>

<template>
  <div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold">Asignar roles a:</h1>
        <p class="text-gray-600">{{ user.name }} — {{ user.email }}</p>
      </div>
      <Link :href="route('users.roles.index')" class="px-3 py-1 rounded border">Volver</Link>
    </div>

    <div class="bg-white rounded shadow p-4">
      <div class="space-y-2">
        <label v-for="r in roles" :key="r.id" class="flex items-center gap-2">
          <input
            type="checkbox"
            :checked="form.roles.includes(r.name)"
            @change="toggle(r.name)"
          />
          <span>{{ r.name }}</span>
        </label>
      </div>
    </div>

    <div class="flex justify-end">
      <button
        class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700"
        :disabled="form.processing"
        @click="submit"
      >
        Guardar
      </button>
    </div>
  </div>
</template>
