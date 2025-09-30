<script setup lang="ts">
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue'

// === Props que envía el controlador ===
type CatalogoEtapa = { id:number; nombre:string }
type Abogado = { id:number; nombre:string }
type HistItem = {
  id:number
  nombre: string|null
  etapa:number
  fecha_inicio:string|null
  fecha_vencimiento:string|null
  estatus:string
  comentarios:string|null
  usuario:string|null
}
const props = defineProps<{
  revision: { id:number; numero:number|string; empresa:string|null; juicio_id:number|string }
  catalogoEtapas: CatalogoEtapa[]
  estatus: string[]
  abogados: Abogado[]
  historial: HistItem[]
}>()

// === Formulario ===
const today = new Date().toISOString().slice(0,10)
const form = useForm({
  tipo_revision: '' as 'gabinete' | 'domiciliaria' | 'electronica' | 'secuencial' | '',
  catalogo_etapa_id: props.catalogoEtapas?.[0]?.id ?? null,
  fecha_inicio: today,
  dias_vencimiento: 30,
  comentarios: '',
  estatus: 'PENDIENTE',
  abogado_id: props.abogados?.[0]?.id ?? null,
  nombre: '', // nuevo campo a capturar
})

const fechaVencimiento = computed(() => {
  if (!form.fecha_inicio) return ''
  const base = new Date(form.fecha_inicio)
  const out = new Date(base)
  out.setDate(out.getDate() + Number(form.dias_vencimiento || 0))
  return out.toISOString().slice(0,10)
})

const postUrl = computed(() => {
  try { return route('revisiones.etapas.store', props.revision.id) }
  catch { return `/revisiones/${props.revision.id}/etapas` } // fallback si Ziggy no está
})

function submit() {
  form.post(postUrl.value, { preserveScroll: true, onSuccess: () => form.reset('comentarios') })
}

function etapaNombre(id:number) {
  const e = props.catalogoEtapas.find(x => x.id === id)
  return e ? e.nombre : String(id)
}

function fmtDMY(d?: string|null) {
  if (!d) return '—'
  try { return new Date(d).toLocaleDateString('es-MX') } catch { return d }
}
</script>

<template>
  <TopNavLayout />

  <!-- Header -->
  <div class="bg-white rounded-xl p-5 shadow space-y-4">
    <h1 class="text-2xl font-semibold">CAPTURA DE ETAPA DE REVISIONES</h1>

    <div class="flex flex-wrap items-center gap-2 text-sm">
      <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">
        Empresa:
        <strong>{{ props.revision.empresa.razonsocial ?? '—' }}</strong>
      </span>
      <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">Revisión# {{ props.revision.id }}</span>
      <Link :href="route('revisiones.index')" class="ml-auto text-indigo-600 hover:underline">Volver</Link>
    </div>
  

    <!-- Form -->
    <div class="grid md:grid-cols-2 gap-4">
        <div>
        <label class="block text-sm font-medium mb-1">Nombre</label>
         <input
     type="text"
      v-model="form.nombre"
      class="w-full rounded border border-slate-300"
      placeholder="Escribe el nombre..."
    />
        <p v-if="form.errors.catalogo_etapa_id" class="text-red-600 text-sm mt-1">{{ form.errors.catalogo_etapa_id }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Etapa</label>
        <select v-model="form.catalogo_etapa_id" class="w-full rounded border border-slate-300">
          <option v-for="e in props.catalogoEtapas" :key="e.id" :value="e.id">{{ e.nombre }}</option>
        </select>
        <p v-if="form.errors.catalogo_etapa_id" class="text-red-600 text-sm mt-1">{{ form.errors.catalogo_etapa_id }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Fecha</label>
        <input type="date" v-model="form.fecha_inicio" class="w-full rounded border border-slate-300" />
        <p v-if="form.errors.fecha_inicio" class="text-red-600 text-sm mt-1">{{ form.errors.fecha_inicio }}</p>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Vencimiento</label>
        <div class="flex items-center gap-2">
          <input type="number" min="0" v-model.number="form.dias_vencimiento" class="w-24 rounded border border-slate-300" />
          <span class="text-sm">Días.</span>
          <span class="text-sm px-2 py-1 rounded bg-slate-100">{{ fmtDMY(fechaVencimiento) }}</span>
        </div>
        <p v-if="form.errors.dias_vencimiento" class="text-red-600 text-sm mt-1">{{ form.errors.dias_vencimiento }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Abogado</label>
        <select v-model="form.abogado_id" class="w-full rounded border border-slate-300">
          <option v-for="u in props.abogados" :key="u.id" :value="u.id">{{ u.nombre }}</option>
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Comentarios</label>
        <textarea v-model="form.comentarios" rows="3" class="w-full rounded border border-slate-300"></textarea>
        <p v-if="form.errors.comentarios" class="text-red-600 text-sm mt-1">{{ form.errors.comentarios }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Estatus</label>
        <select v-model="form.estatus" class="w-full rounded border border-slate-300">
          <option v-for="e in props.estatus" :key="e" :value="e">{{ e }}</option>
        </select>
      </div>
    </div>

    <div class="pt-2">
      <button :disabled="form.processing" @click="submit"
        class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50">
        Guardar
      </button>
    </div>
  </div>

  <!-- Historial -->
  <div class="bg-white rounded-xl p-5 shadow mt-6">
    <h2 class="text-lg font-semibold mb-3">Historial de etapas</h2>
    <div class="overflow-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left px-3 py-2">Fecha inicio</th>
            <th class="text-left px-3 py-2">Etapa</th>
            <th class="text-left px-3 py-2">Vencimiento</th>
            <th class="text-left px-3 py-2">Usuario captura</th>
            <th class="text-left px-3 py-2">Estatus</th>
            <th class="text-left px-3 py-2">Comentarios</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in props.historial" :key="row.id" class="border-t">
            <td class="px-3 py-2">{{ row.fecha_inicio ?? '—' }}</td>
            <td class="px-3 py-2">{{ etapaNombre(row.etapa) }}</td>
            <td class="px-3 py-2">{{ row.fecha_vencimiento ?? '—' }}</td>
            <td class="px-3 py-2">{{ row.usuario ?? '—' }}</td>
            <td class="px-3 py-2">
              <span class="px-2 py-0.5 rounded text-xs"
                :class="{
                  'bg-yellow-100 border border-yellow-300': row.estatus==='PENDIENTE',
                  'bg-blue-100 border border-blue-300': row.estatus==='EN PROCESO' || row.estatus==='EN_PROCESO',
                  'bg-green-100 border border-green-300': row.estatus==='ATENDIDO',
                  'bg-gray-200 border border-gray-300': row.estatus==='CANCELADO',
                }">
                {{ row.estatus }}
              </span>
            </td>
            <td class="px-3 py-2">{{ row.comentarios ?? '' }}</td>
          </tr>
          <tr v-if="props.historial.length === 0">
            <td colspan="6" class="px-3 py-6 text-center text-gray-500">Sin registros aún.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
