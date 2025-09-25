<script setup lang="ts">
import { computed, watch } from 'vue'
import TopNavLayout from '@/layouts/TopNavLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'

type TipoRevision = 'gabinete' | 'visita' | 'electronica' | 'secuencial'
const props = defineProps<{
 catalogoRevision: Record<TipoRevision, string[]>
  empresas: Array<{ id:number, nombre:string }>
  autoridades: Array<{id:number,nombre:string}>
  etiquetas: Array<{id:number,nombre:string}>
  defaults: { estatus: string }
}>()

const form = useForm({
  idempresa: '',
  tipo_revision: '' as TipoRevision | '',
  autoridad_id: '',
  revision: '',
  rev_gabinete: false,
  rev_domiciliaria: false,
  rev_electronica: false,
  rev_secuencial: false,
  periodo_desde: '',
  periodo_hasta: '',
  objeto: '',
  observaciones: '',
  aspectos: '',
  compulsas: '',
  estatus: props.defaults.estatus,
  etiquetas: [] as number[],
})
const opcionesRevision = computed(() => {
  return form.tipo_revision ? (props.catalogoRevision[form.tipo_revision] ?? []) : []
})
// Cuando cambia el tipo, resetea la revisión si ya no es válida
watch(() => form.tipo_revision, () => {
  if (!opcionesRevision.value.includes(form.revision)) {
    form.revision = opcionesRevision.value[0] ?? ''
  }
})

function submit(){ form.post(route('revisiones.store')) }
</script>

<template>
  <TopNavLayout>
    <div class="bg-white border rounded p-6 max-w-4xl mx-auto">
      <h1 class="text-2xl font-semibold mb-4 text-center">REVISIÓN DE AUTORIDAD FISCAL</h1>

      <form @submit.prevent="submit" class="space-y-5">
        <!-- Etiquetas -->
        <div>
          <label class="block text-sm font-medium">Etiqueta(s)</label>
          <div class="flex flex-wrap gap-2 mt-1">
            <label v-for="e in props.etiquetas" :key="e.id" class="inline-flex items-center gap-2">
              <input type="checkbox" :value="e.id" v-model="form.etiquetas" class="rounded border-gray-300">
              <span class="text-sm">{{ e.nombre }}</span>
            </label>
          </div>
        </div>

        <!-- Tipo -->
      <div>
  <label class="block text-sm font-medium">Tipo</label>
  <div class="mt-1 flex flex-wrap gap-6">
    <label class="inline-flex items-center gap-2">
      <input type="radio" value="gabinete" v-model="form.tipo_revision" />
      <span>Revisión de gabinete</span>
    </label>
    <label class="inline-flex items-center gap-2">
      <input type="radio" value="visita" v-model="form.tipo_revision" />
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
  <p v-if="form.errors.tipo_revision" class="text-red-600 text-xs mt-1">
    {{ form.errors.tipo_revision }}
  </p>
</div>

<!-- Revisión (select dependiente) -->
<div>
  <label class="block text-sm font-medium">Revisión</label>
  <select v-model="form.revision" :disabled="!form.tipo_revision"
          class="mt-1 w-full border rounded px-3 py-2">
    <option v-if="!form.tipo_revision" value="">Selecciona el tipo de revisión…</option>
    <option v-for="op in opcionesRevision" :key="op" :value="op">{{ op }}</option>
  </select>
  <p v-if="form.errors.revision" class="text-red-600 text-xs mt-1">
    {{ form.errors.revision }}
  </p>
</div>

        <!-- Sociedad / Autoridad -->
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium">Sociedad</label>
            <select v-model="form.idempresa" class="mt-1 w-full border rounded px-3 py-2 ">
              <option value="">Seleccione…</option>
              <option v-for="c in props.empresas" :value="c.idempresa" :key="c.id">{{ c.razonsocial }}</option>
            </select>
            <p v-if="form.errors.idempresa" class="text-xs text-red-600">{{ form.errors.idempresa }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium">Autoridad</label>
            <select v-model="form.autoridad_id" class="mt-1 w-full border rounded px-3 py-2">
              <option value="">—</option>
              <option v-for="a in props.autoridades" :value="a.id" :key="a.id">{{ a.nombre }}</option>
            </select>
          </div>
        </div>

       

        <!-- Periodo -->
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium">Periodo/Ejercicio desde</label>
            <input v-model="form.periodo_desde" type="date" class="mt-1 w-full border rounded px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium">al</label>
            <input v-model="form.periodo_hasta" type="date" class="mt-1 w-full border rounded px-3 py-2">
          </div>
        </div>

        <!-- Texto -->
        <div>
          <label class="block text-sm font-medium">Objeto</label>
          <input v-model="form.objeto" type="text" class="mt-1 w-full border rounded px-3 py-2" placeholder="Escriba…">
        </div>
        <div>
          <label class="block text-sm font-medium">Observaciones</label>
          <input v-model="form.observaciones" type="text" class="mt-1 w-full border rounded px-3 py-2" placeholder="Escriba…">
        </div>
        <div>
          <label class="block text-sm font-medium">Aspectos relevantes</label>
          <textarea v-model="form.aspectos" rows="3" class="mt-1 w-full border rounded px-3 py-2" placeholder="Escriba…"/>
        </div>
        <div>
          <label class="block text-sm font-medium">Compulsas</label>
          <input v-model="form.compulsas" type="text" class="mt-1 w-full border rounded px-3 py-2" placeholder="Escriba…">
        </div>

        <!-- Estatus -->
        <div>
          <label class="block text-sm font-medium">Estatus general</label>
          <select v-model="form.estatus" class="mt-1 w-full border rounded px-3 py-2">
            <option value="en_juicio">En juicio</option>
            <option value="pendiente">Pendiente</option>
            <option value="en_proceso">En proceso</option>
            <option value="autorizado">Autorizado</option>
            <option value="concluido">Concluido</option>
          </select>
        </div>

        <div class="flex justify-center">
          <button type="submit" :disabled="form.processing"
                  class="px-5 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50">
            Guardar
          </button>
        </div>
      </form>
    </div>
  </TopNavLayout>
</template>
