<script setup lang="ts">
import { computed, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

type TipoRevision = 'gabinete' | 'domiciliaria' | 'electronica' | 'secuencial'

const props = defineProps<{
  // Catálogo que viene del controlador: { gabinete: string[], domiciliaria: string[], ... }
  catalogoRevision: Record<TipoRevision, string[]>
  // Catálogos de selects (pon los nombres reales que ya usas)
  empresas: Array<{ idempresa: number; razonsocial: string }>
  autoridades: Array<{ id: number; nombre: string }>
  usuarios?: Array<{ id: number; name: string }>
  estatuses?: Array<{ value: string; label: string }>
  // Valores por defecto opcionales
  defaults?: {
    estatus?: string
    empresa_id?: number
    autoridad_id?: number
  }
}>()

const form = useForm({
  empresa_id: props.defaults?.empresa_id ?? '',
  autoridad_id: props.defaults?.autoridad_id ?? '',
  usuario_id: '',
  // 👇 aquí están los radios y el select dependiente
  tipo_revision: '' as '' | TipoRevision,
  revision: '',
  periodo_desde: '',
  periodo_hasta: '',
  objeto: '',
  observaciones: '',
  aspectos: '',
  compulsas: '',
  no_juicio: '',
  estatus: props.defaults?.estatus ?? 'en_juicio',
})

// lista dependiente de “revision”
const opcionesRevision = computed(() => {
  return form.tipo_revision ? (props.catalogoRevision[form.tipo_revision] ?? []) : []
})

// si cambia el tipo, valida/reset la revisión elegida
watch(() => form.tipo_revision, () => {
  if (!opcionesRevision.value.includes(form.revision)) {
    form.revision = opcionesRevision.value[0] ?? ''
  }
})

function submit() {
  form.post(route('revisiones.store'))
}
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">REVISIÓN DE AUTORIDAD FISCAL</h1>
      <Link :href="route('revisiones.index')" class="text-indigo-600 hover:underline">Volver</Link>
    </div>

    <form @submit.prevent="submit" class="space-y-6 bg-white rounded-lg shadow p-5">
      <!-- Tipo de revisión (RADIOS) -->
      <div>
        <label class="block text-sm font-medium mb-2">Tipo</label>
        <div class="flex flex-wrap gap-x-8 gap-y-2">
          <label class="inline-flex items-center gap-2">
            <input type="radio" value="gabinete" v-model="form.tipo_revision" />
            <span>Revisión de gabinete</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="radio" value="domiciliaria" v-model="form.tipo_revision" />
            <span>Visita domiciliaria</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="radio" value="electronica" v-model="form.tipo_revision" />
            <span>Revisión electrónica</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="radio" value="secuencial" v-model="form.tipo_revision" />
            <span>Revisión secuencial</span>
          </label>
        </div>
        <p v-if="form.errors.tipo_revision" class="text-red-600 text-xs mt-1">{{ form.errors.tipo_revision }}</p>
      </div>

      <!-- Sociedad y Autoridad -->
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Sociedad</label>
          <select v-model="form.empresa_id" class="w-full border rounded px-3 py-2">
            <option value="">Seleccione…</option>
            <option v-for="s in empresas" :key="s.idempresa" :value="s.idempresa">
              {{ s.razonsocial }}
            </option>
          </select>
          <p v-if="form.errors.empresa_id" class="text-red-600 text-xs mt-1">{{ form.errors.empresa_id }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Autoridad</label>
          <select v-model="form.autoridad_id" class="w-full border rounded px-3 py-2">
            <option value="">—</option>
            <option v-for="a in autoridades" :key="a.id" :value="a.id">
              {{ a.nombre }}
            </option>
          </select>
          <p v-if="form.errors.autoridad_id" class="text-red-600 text-xs mt-1">{{ form.errors.autoridad_id }}</p>
        </div>
      </div>

      <!-- Revisión dependiente del tipo -->
      <div>
        <label class="block text-sm font-medium mb-1">Revisión</label>
        <select v-model="form.revision" :disabled="!form.tipo_revision" class="w-full border rounded px-3 py-2">
          <option v-if="!form.tipo_revision" value="">Primero elige el tipo…</option>
          <option v-for="(op, i) in opcionesRevision" :key="i" :value="op">
            {{ i + 1 }}. {{ op }}
          </option>
        </select>
        <p v-if="form.errors.revision" class="text-red-600 text-xs mt-1">{{ form.errors.revision }}</p>
      </div>

      <!-- Periodo -->
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Periodo/Ejercicio desde</label>
          <input type="date" v-model="form.periodo_desde" class="w-full border rounded px-3 py-2" />
          <p v-if="form.errors.periodo_desde" class="text-red-600 text-xs mt-1">{{ form.errors.periodo_desde }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">al</label>
          <input type="date" v-model="form.periodo_hasta" class="w-full border rounded px-3 py-2" />
          <p v-if="form.errors.periodo_hasta" class="text-red-600 text-xs mt-1">{{ form.errors.periodo_hasta }}</p>
        </div>
      </div>

      <!-- Objeto -->
      <div>
        <label class="block text-sm font-medium mb-1">Objeto</label>
        <input type="text" v-model="form.objeto" class="w-full border rounded px-3 py-2" placeholder="Escriba…" />
        <p v-if="form.errors.objeto" class="text-red-600 text-xs mt-1">{{ form.errors.objeto }}</p>
      </div>

      <!-- Observaciones -->
      <div>
        <label class="block text-sm font-medium mb-1">Observaciones</label>
        <input type="text" v-model="form.observaciones" class="w-full border rounded px-3 py-2" placeholder="Escriba…" />
        <p v-if="form.errors.observaciones" class="text-red-600 text-xs mt-1">{{ form.errors.observaciones }}</p>
      </div>

      <!-- Aspectos relevantes -->
      <div>
        <label class="block text-sm font-medium mb-1">Aspectos relevantes</label>
        <textarea v-model="form.aspectos" rows="3" class="w-full border rounded px-3 py-2" placeholder="Escriba…"></textarea>
        <p v-if="form.errors.aspectos" class="text-red-600 text-xs mt-1">{{ form.errors.aspectos }}</p>
      </div>
      <div>
  <label class="block text-sm font-medium text-gray-600">Número de Juicio Compulsa</label>
  <input v-model="form.no_juicio" type="text"
         class="mt-1 w-full border rounded px-3 py-2"
         placeholder="Ej: J1234ABC" />
  <div v-if="form.errors.no_juicio" class="text-red-400 text-xs">{{ form.errors.no_juicio }}</div>
</div>
      <!-- Compulsas -->
      <div>
        <label class="block text-sm font-medium mb-1">Compulsas</label>
        <textarea v-model="form.compulsas" rows="2" class="w-full border rounded px-3 py-2" placeholder="Escriba…"></textarea>
        <p v-if="form.errors.compulsas" class="text-red-600 text-xs mt-1">{{ form.errors.compulsas }}</p>
      </div>

      <!-- Estatus -->
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Estatus</label>
          <select v-model="form.estatus" class="w-full border rounded px-3 py-2">
            <option value="en_juicio">En juicio</option>
            <option value="concluido">Concluido</option>
            <option value="cancelado">Cancelado</option>
          </select>
          <p v-if="form.errors.estatus" class="text-red-600 text-xs mt-1">{{ form.errors.estatus }}</p>
        </div>
        <div class="flex items-end justify-end">
          <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700" :disabled="form.processing">
            Guardar
          </button>
        </div>
      </div>
    </form>
  </div>
</template>
