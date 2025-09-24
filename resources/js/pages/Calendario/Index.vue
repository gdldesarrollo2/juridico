<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, useForm, Link } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue';

type Autoridad = { id:number; nombre:string }
type Dia = { id:number; fecha:string; dia_semana:string; descripcion:string|null }

const props = defineProps<{
  autoridades: Autoridad[]
  filtros: { autoridad_id:number; anio:number }
  dias: Dia[]
}>()

const autoridadId = ref<number>(props.filtros.autoridad_id)
const anio = ref<number>(props.filtros.anio)

function recargar() {
  router.get(route?.('calendario.index') ?? '/calendario', {
    autoridad_id: autoridadId.value,
    anio: anio.value
  }, { preserveScroll: true, replace: true })
}

const form = useForm<{ autoridad_id:number; anio:number; archivo: File|null }>({
  autoridad_id: autoridadId.value,
  anio: anio.value,
  archivo: null
})

function subir() {
  form.post(route?.('calendario.upload') ?? '/calendario/upload', {
    forceFormData: true,
    onSuccess: () => { form.reset('archivo') }
  })
}
</script>

<template>
  <TopNavLayout></TopNavLayout>

  <div class="space-y-4">
    <h1 class="text-3xl font-semibold">Calendario</h1>

    <div class="flex flex-wrap items-center gap-3">
      <div class="flex items-center gap-2">
        <span class="text-sm">AUTORIDAD:</span>
        <select v-model.number="autoridadId" class="rounded border border-slate-300" @change="recargar">
          <option v-for="a in autoridades" :key="a.id" :value="a.id">{{ a.nombre }}</option>
        </select>
      </div>

      <div class="flex items-center gap-2">
        <span class="text-sm">AÑO:</span>
        <select v-model.number="anio" class="rounded border border-slate-300" @change="recargar">
          <option v-for="y in Array.from({length: 10}, (_,i)=> props.filtros.anio - 5 + i)"
                  :key="y" :value="y">{{ y }}</option>
        </select>
      </div>

      <form @submit.prevent="subir" class="flex items-center gap-2">
        <input type="file" accept=".csv,text/csv" @change="e => form.archivo = (e.target as HTMLInputElement).files?.[0] ?? null"
               class="block w-full border border-gray-200 shadow-sm rounded-lg text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400
    file:bg-gray-50 file:border-0
    file:me-4
    file:py-3 file:px-4
    dark:file:bg-neutral-700 dark:file:text-neutral-400" />
        <input type="hidden" v-model.number="form.autoridad_id" />
        <input type="hidden" v-model.number="form.anio" />
        <button class="px-3 py-1.5 rounded bg-emerald-600 text-white text-sm hover:bg-emerald-700">
          Subir archivo de días festivos
        </button>
      </form>
    </div>

    <div class="bg-white rounded-xl p-4 shadow">
      <div class="overflow-auto max-h-[70vh]">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-100 sticky top-0">
            <tr>
              <th class="text-left px-3 py-2 w-40">Día</th>
              <th class="text-left px-3 py-2 w-32">Fecha</th>
              <th class="text-left px-3 py-2">Descripción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in props.dias" :key="d.id" class="border-b last:border-0">
              <td class="px-3 py-2 capitalize">{{ d.dia_semana }}</td>
              <td class="px-3 py-2">{{ d.fecha }}</td>
              <td class="px-3 py-2">{{ d.descripcion ?? '' }}</td>
            </tr>
            <tr v-if="!props.dias.length">
              <td colspan="3" class="px-3 py-6 text-center text-slate-500">Sin registros para este año/autoridad.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="form.errors.archivo" class="text-red-600 text-sm mt-2">{{ form.errors.archivo }}</p>
    </div>
  </div>
</template>
