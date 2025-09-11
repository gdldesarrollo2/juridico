<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/TopNavLayout.vue'

type Empresa = { idempresa:number|string; razonsocial:string }
type Autoridad = { id:number|string; nombre:string }

const props = defineProps<{
  revision: {
    id: number
    idempresa: number|string|null
    autoridad_id: number|string|null
    revision?: string|null
    rev_gabinete: boolean
    rev_domiciliaria: boolean
    rev_electronica: boolean
    rev_secuencial: boolean
    periodo_desde?: string|null
    periodo_hasta?: string|null
    objeto?: string|null
    observaciones?: string|null
    aspectos?: string|null
    compulsas?: string|null
    estatus: string
    empresa?: { idempresa:number|string, razonsocial:string } | null
    autoridad?: { id:number|string, nombre:string } | null
  }
  empresas: Empresa[]
  autoridades: Autoridad[]
}>()

const toDateInput = (s?: string|null) => s ? s.slice(0,10) : ''

const form = useForm({
  idempresa: props.revision.idempresa != null ? Number(props.revision.idempresa) : null,
  autoridad_id: props.revision.autoridad_id != null ? Number(props.revision.autoridad_id) : null,
  revision: props.revision.revision ?? '',
  rev_gabinete: !!props.revision.rev_gabinete,
  rev_domiciliaria: !!props.revision.rev_domiciliaria,
  rev_electronica: !!props.revision.rev_electronica,
  rev_secuencial: !!props.revision.rev_secuencial,
  periodo_desde: toDateInput(props.revision.periodo_desde),
  periodo_hasta: toDateInput(props.revision.periodo_hasta),
  objeto: props.revision.objeto ?? '',
  observaciones: props.revision.observaciones ?? '',
  aspectos: props.revision.aspectos ?? '',
  compulsas: props.revision.compulsas ?? '',
  estatus: props.revision.estatus,
})

function submit() {
  form.put(route('revisiones.update', props.revision.id))
}
</script>

<template>
  <AppLayout>
    <form @submit.prevent="submit" class="max-w-4xl mx-auto p-6 bg-white rounded shadow space-y-4">
      <h1 class="text-2xl font-semibold">Editar revisión #{{ props.revision.id }}</h1>

      <!-- Sociedad -->
      <div>
        <label class="block text-sm font-medium">Sociedad</label>
        <select v-model="form.idempresa" class="mt-1 w-full border rounded px-3 py-2">
          <option disabled value="">Seleccione…</option>
          <option v-for="e in props.empresas" :key="e.idempresa" :value="Number(e.idempresa)">
            {{ e.razonsocial }}
          </option>
        </select>
      </div>

      <!-- Autoridad -->
      <div>
        <label class="block text-sm font-medium">Autoridad</label>
        <select v-model="form.autoridad_id" class="mt-1 w-full border rounded px-3 py-2">
          <option :value="null">—</option>
          <option v-for="a in props.autoridades" :key="a.id" :value="Number(a.id)">{{ a.nombre }}</option>
        </select>
      </div>

      <!-- Fechas -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Periodo desde</label>
          <input type="date" v-model="form.periodo_desde" class="mt-1 w-full border rounded px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm font-medium">Periodo hasta</label>
          <input type="date" v-model="form.periodo_hasta" class="mt-1 w-full border rounded px-3 py-2" />
        </div>
      </div>

      <!-- Flags -->
      <div class="grid grid-cols-2 gap-4">
        <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="form.rev_gabinete" /> Gabinete</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="form.rev_domiciliaria" /> Domiciliaria</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="form.rev_electronica" /> Electrónica</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="form.rev_secuencial" /> Secuencial</label>
      </div>

      <!-- Otros -->
      <div><label class="block text-sm font-medium">Nombre/Identificador</label><input v-model="form.revision" class="mt-1 w-full border rounded px-3 py-2" /></div>
      <div><label class="block text-sm font-medium">Objeto</label><input v-model="form.objeto" class="mt-1 w-full border rounded px-3 py-2" /></div>
      <div><label class="block text-sm font-medium">Observaciones</label><textarea v-model="form.observaciones" class="mt-1 w-full border rounded px-3 py-2" /></div>
      <div><label class="block text-sm font-medium">Aspectos</label><textarea v-model="form.aspectos" class="mt-1 w-full border rounded px-3 py-2" /></div>
      <div><label class="block text-sm font-medium">Compulsas</label><textarea v-model="form.compulsas" class="mt-1 w-full border rounded px-3 py-2" /></div>

      <!-- Estatus -->
      <div>
        <label class="block text-sm font-medium">Estatus</label>
        <select v-model="form.estatus" class="mt-1 w-full border rounded px-3 py-2">
          <option value="en_juicio">En juicio</option>
          <option value="pendiente">Pendiente</option>
          <option value="autorizado">Autorizado</option>
          <option value="en_proceso">En proceso</option>
          <option value="concluido">Concluido</option>
        </select>
      </div>

      <div class="flex justify-end gap-2">
        <Link :href="route('revisiones.index')" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Cancelar</Link>
        <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Guardar cambios</button>
      </div>
    </form>
  </AppLayout>
</template>
