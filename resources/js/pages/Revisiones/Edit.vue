<script setup lang="ts">
import { computed, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue'

const props = defineProps<{
  revision: {
    id: number
    idempresa: number|null
    usuario_id: number|null
    autoridad_id: number|null
    revision: string
    periodos: Record<string, number[]> // { "2022": [1,6,7], "2025":[1,2] }
    objeto: string|null
    observaciones: string|null
    aspectos: string|null
    compulsas: string|null
    estatus: string
    tipo_revision: 'gabinete'|'domiciliaria'|'electronica'|'secuencial'
    no_juicio?: string|null
  }
  catalogoRevision: Record<'gabinete'|'domiciliaria'|'electronica'|'secuencial', string[]>
  // Catálogos de selects (pon los nombres reales que ya usas)
  empresas: Array<{ idempresa: number; razonsocial: string }>
  autoridades: Array<{ id: number; nombre: string }>
  // ...otros catálogos
}>()

// inicializa el formulario usando props.revision (ya no truena)
const form = useForm({
  empresa_id: props.revision.idempresa ?? '',
  usuario_id: props.revision.usuario_id ?? '',
  autoridad_id: props.revision.autoridad_id ?? '',
  tipo_revision: props.revision.tipo_revision,
  revision: props.revision.revision,
periodos: Object.entries(props.revision.periodos || {}).map(
    ([anio, meses]) => ({
      anio: parseInt(anio),
      meses: meses as number[],
    })
  ),
  objeto: props.revision.objeto ?? '',
  observaciones: props.revision.observaciones ?? '',
  aspectos: props.revision.aspectos ?? '',
  compulsas: props.revision.compulsas ?? '',
  estatus: props.revision.estatus ?? 'en_juicio',
  no_juicio: props.revision.no_juicio ?? '',
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
function toggleMes(periodoIndex: number, mes: number) {
  const meses = form.periodos[periodoIndex].meses
  if (meses.includes(mes)) {
    form.periodos[periodoIndex].meses = meses.filter(m => m !== mes)
  } else {
    form.periodos[periodoIndex].meses.push(mes)
  }
}

function addPeriodo() {
  form.periodos.push({ anio: new Date().getFullYear(), meses: [] })
}

function removePeriodo(i:number) {
  form.periodos.splice(i,1)
}

function submit() {
  form.put(route('revisiones.update', props.revision.id))
}
</script>
<template>
  <TopNavLayout></TopNavLayout>

  <AppLayout>
    <form @submit.prevent="submit" class="max-w-4xl mx-auto p-6 bg-white rounded shadow space-y-4">
      <h1 class="text-2xl font-semibold">Editar revisión #{{ props.revision.id }}</h1>

      <!-- Sociedad -->
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

      <!-- Autoridad -->
      <div>
        <label class="block text-sm font-medium">Autoridad</label>
        <select v-model="form.autoridad_id" class="mt-1 w-full border rounded px-3 py-2">
          <option :value="null">—</option>
          <option v-for="a in props.autoridades" :key="a.id" :value="Number(a.id)">{{ a.nombre }}</option>
        </select>
      </div>

      <div>
      <label class="block text-sm font-medium">Periodos</label>
      <div v-for="(p, i) in form.periodos" :key="i" class="mb-4 border p-2">
        <select v-model="p.anio" class="border rounded px-2 py-1">
          <option v-for="anio in [2020,2021,2022,2023,2024,2025]" :key="anio" :value="anio">{{anio}}</option>
        </select>
        <div class="grid grid-cols-4 gap-2 mt-2">
          <label v-for="mes in 12" :key="mes" class="flex items-center gap-1">
            <input type="checkbox" :checked="p.meses.includes(mes)" @change="toggleMes(i, mes)" />
            {{ new Date(2000, mes-1, 1).toLocaleString('es-MX',{month:'long'}) }}
          </label>
        </div>
        <button type="button" @click="removePeriodo(i)" class="text-red-500">Eliminar</button>
      </div>
      <button type="button" @click="addPeriodo" class="text-blue-500">+ Añadir año</button>
    </div>
      <!-- Flags -->
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
