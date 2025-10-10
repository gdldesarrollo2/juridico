<script setup lang="ts">
import { reactive } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue'
type Abogado = { id:number; nombre:string }
type Juicio   = { id:number; nombre:string; fecha_inicio:string|null; cliente?:{ id:number; nombre:string } }

const props = defineProps<{
  abogado: { id:number; nombre:string; estatus:string }
  juicios: Juicio[]
  abogadosActivos: Abogado[]
  nextStatus: 'activo'|'inactivo'
}>()

const form = useForm({
  reasignaciones: props.juicios.map(j => ({ id: j.id, nuevo_abogado_id: null as number|null })),
  nextStatus: props.nextStatus
})

function submit() {
  form.post(route('abogados.reasignar.store', props.abogado.id))
}
</script>

<template>
  <TopNavLayout></TopNavLayout>
  <div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold">Reasignar juicios de {{ abogado.nombre }}</h1>
      <Link :href="route('abogados.index')" class="px-3 py-1.5 rounded border">Volver</Link>
    </div>

    <p class="text-gray-600">
      Estás cambiando el estatus a <strong>{{ nextStatus }}</strong>. Reasigna cada juicio a un abogado activo o déjalo sin asignar.
    </p>

    <div class="overflow-auto border rounded">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left px-3 py-2">Juicio</th>
            <th class="text-left px-3 py-2">Cliente</th>
            <th class="text-left px-3 py-2">Fecha inicio</th>
            <th class="text-left px-3 py-2">Nuevo abogado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(j, idx) in juicios" :key="j.id" class="border-t">
            <td class="px-3 py-2">{{ j.nombre }}</td>
            <td class="px-3 py-2">{{ j.cliente?.nombre ?? '—' }}</td>
            <td class="px-3 py-2">{{ j.fecha_inicio ?? '—' }}</td>
            <td class="px-3 py-2">
              <select v-model="form.reasignaciones[idx].nuevo_abogado_id" class="border rounded px-2 py-1">
                <option :value="null">— Sin abogado —</option>
                <option v-for="ab in abogadosActivos" :key="ab.id" :value="ab.id">
                  {{ ab.nombre }}
                </option>
              </select>
            </td>
          </tr>
          <tr v-if="juicios.length === 0">
            <td colspan="4" class="px-3 py-6 text-center text-gray-500">No hay juicios por reasignar.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex justify-end">
      <button class="px-4 py-2 rounded bg-blue-600 text-white" :disabled="form.processing" @click="submit">
        {{ form.processing ? 'Guardando…' : 'Confirmar y actualizar' }}
      </button>
    </div>
  </div>
</template>
