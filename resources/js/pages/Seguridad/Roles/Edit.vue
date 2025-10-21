<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue';

const props = defineProps<{
  role: { id:number; name:string }
  permissions: Record<string, Array<{ id:number; name:string; label:string }>>
  selected: string[] // nombres de permisos
}>()

const form = useForm({
  permissions: [...props.selected],
})

function toggle(name: string) {
  const i = form.permissions.indexOf(name)
  i === -1 ? form.permissions.push(name) : form.permissions.splice(i, 1)
}

function submit() {
  form.put(route('roles.update', props.role.id))
}
</script>

<template>
  <TopNavLayout></TopNavLayout>
  <div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Permisos de: {{ role.name }}</h1>
      <Link :href="route('roles.index')" class="px-3 py-1 rounded border">Volver</Link>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
      <div v-for="(group, modulo) in permissions" :key="modulo" class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3">{{ modulo.toUpperCase() }}</h2>

        <div class="space-y-2">
          <label v-for="p in group" :key="p.id" class="flex items-center gap-2">
            <input
              type="checkbox"
              :checked="form.permissions.includes(p.name)"
              @change="toggle(p.name)"
            />
            <span>{{ p.label }}</span>
          </label>
        </div>
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
