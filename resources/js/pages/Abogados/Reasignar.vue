<script setup lang="ts">
import TopNavLayout from '@/layouts/TopNavLayout.vue';
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps<{
  abogado: { id:number; nombre:string }
  candidatos: { id:number; nombre:string }[]
  juicios: { id:number; nombre:string }[]
}>()

const form = useForm({
  nuevo_abogado_id: '',
  juicios: [] as number[],
  motivo: '',
})

// debug opcional
console.log('candidatos:', props.candidatos)

function submit () {
  form.post(route('abogados.reasignar.store', props.abogado.id))
}
</script>

<template>
  <TopNavLayout></TopNavLayout>
  <div class="space-y-6">
    <h1 class="text-xl font-semibold">
      Reasignar juicios de: {{ abogado.nombre }}
    </h1>

    <!-- Nuevo abogado -->
    <div>
      <label class="block text-sm font-medium">Nuevo abogado</label>
      <select v-model="form.nuevo_abogado_id" class="border rounded px-3 py-2 w-full">
        <option value="">Seleccione…</option>
        <option v-for="a in candidatos" :key="a.id" :value="a.id">
          {{ a.nombre }}
        </option>
      </select>
      <p v-if="form.errors.nuevo_abogado_id" class="text-red-600 text-sm mt-1">
        {{ form.errors.nuevo_abogado_id }}
      </p>
    </div>

    <!-- Juicios -->
    <div>
      <label class="block text-sm font-medium">Juicios a reasignar</label>
      <div class="mt-2 grid gap-2">
        <label v-for="j in juicios" :key="j.id" class="flex items-center gap-2">
          <input type="checkbox" :value="j.id" v-model="form.juicios" />
          <span>#{{ j.id }} – {{ j.nombre }}</span>
        </label>
      </div>
      <p v-if="form.errors.juicios" class="text-red-600 text-sm mt-1">
        {{ form.errors.juicios }}
      </p>
    </div>

    <!-- Motivo -->
    <div>
      <label class="block text-sm font-medium">Motivo</label>
      <textarea v-model="form.motivo" class="border rounded px-3 py-2 w-full" rows="2"></textarea>
      <p v-if="form.errors.motivo" class="text-red-600 text-sm mt-1">
        {{ form.errors.motivo }}
      </p>
    </div>

    <div class="flex items-center gap-3">
      <button
        type="button"
        @click="submit"
        class="px-4 py-2 rounded bg-indigo-600 text-white"
        :disabled="form.processing"
      >
        Reasignar
      </button>

      <Link :href="route('abogados.index')" class="px-4 py-2 rounded bg-gray-200">
        Cancelar
      </Link>
    </div>
  </div>
</template>
