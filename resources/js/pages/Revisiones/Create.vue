<script setup lang="ts">
import { reactive,computed, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue'

type TipoRevision = 'gabinete' | 'domiciliaria' | 'electronica' | 'secuencial'

// Meses
const MESES = [
  { v:1,  label:'Enero' }, { v:2,  label:'Febrero' }, { v:3,  label:'Marzo' },
  { v:4,  label:'Abril' }, { v:5,  label:'Mayo' },    { v:6,  label:'Junio' },
  { v:7,  label:'Julio' }, { v:8,  label:'Agosto' },  { v:9,  label:'Septiembre' },
  { v:10, label:'Octubre' }, { v:11,label:'Noviembre' }, { v:12,label:'Diciembre' },
]

// Años disponibles (ajusta el rango)
const aniosDisponibles = computed(() => {
  const y = new Date().getFullYear()
  const arr: number[] = []
  for (let i = y + 1; i >= y - 20; i--) arr.push(i)
  return arr
})
type Opcion = { id: number; nombre: string }
const props = defineProps<{
  // Catálogo que viene del controlador: { gabinete: string[], domiciliaria: string[], ... }
  catalogoRevision: Record<TipoRevision, string[]>
  // Catálogos de selects (pon los nombres reales que ya usas)
   options?: { sociedades?: Opcion[]; autoridades?: Opcion[] }
  sociedades?: Opcion[]
  autoridades?: Opcion[]
  usuarios?: Array<{ id: number; name: string }>
  estatuses?: Array<{ value: string; label: string }>
  // Valores por defecto opcionales
  defaults?: {
    estatus?: string
    empresa_id?: number
    autoridad_id?: number
  }
}>()
// Normalizamos: si vienen en options las tomamos; si no, planas
const sociedades = computed<Opcion[]>(() => props.options?.sociedades ?? props.sociedades ?? [])
const autoridades = computed<Opcion[]>(() => props.options?.autoridades ?? props.autoridades ?? [])


const form = useForm({
  empresa_id: props.defaults?.empresa_id ?? '',
  autoridad_id: props.defaults?.autoridad_id ?? '',
  usuario_id: '',
  // 👇 aquí están los radios y el select dependiente
  tipo_revision: '' as '' | TipoRevision,
  //revision: '',
  periodos: [] as Array<{ anio:number, meses:number[] }>,
  objeto: '',
  observaciones: '',
  aspectos: '',
  compulsas: '',
  no_juicio: '',
  estatus: props.defaults?.estatus ?? 'en_juicio',
})

// lista dependiente de “revision”
// const opcionesRevision = computed(() => {
//   return form.tipo_revision ? (props.catalogoRevision[form.tipo_revision] ?? []) : []
// })

// Estado para el picker de año nuevo
const nuevoAnio = reactive<{ value: number | '' }>({ value: '' })

// Años ya seleccionados (para evitar duplicados)
const aniosSeleccionados = computed(() => form.periodos.map(p => p.anio))

function agregarAnio() {
  const val = Number(nuevoAnio.value)
  if (!val) return
  if (aniosSeleccionados.value.includes(val)) return
  form.periodos.push({ anio: val, meses: [] })
  nuevoAnio.value = ''
}

function eliminarAnio(anio: number) {
  const i = form.periodos.findIndex(p => p.anio === anio)
  if (i >= 0) form.periodos.splice(i, 1)
}

function toggleMes(anio: number, mes: number) {
  const periodo = form.periodos.find(p => p.anio === anio)
  if (!periodo) return
  const idx = periodo.meses.indexOf(mes)
  if (idx === -1) periodo.meses.push(mes)
  else periodo.meses.splice(idx, 1)
  periodo.meses.sort((a,b)=>a-b)
}

function mesesDe(anio:number) {
  const p = form.periodos.find(x => x.anio === anio)
  return p ? p.meses : []
}
// si cambia el tipo, valida/reset la revisión elegida
// watch(() => form.tipo_revision, () => {
//   if (!opcionesRevision.value.includes(form.revision)) {
//     form.revision = opcionesRevision.value[0] ?? ''
//   }
// })

// en tu Create.vue
function submit () {
  form.transform((data) => ({
    ...data,
    idempresa: form.idempresa === '' ? null : Number(form.idempresa),
    autoridad_id: form.autoridad_id === '' ? null : Number(form.autoridad_id),
    periodos: form.periodos.map(p => ({
      anio: Number(p.anio),
      meses: Array.isArray(p.meses) ? p.meses.map(Number) : []
    })),
  })).post(route('revisiones.store'))
}

</script>

<template>
    <TopNavLayout></TopNavLayout>
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
       <!-- Revisión dependiente del tipo -->
      <div>
        <!-- <label class="block text-sm font-medium mb-1">Revisión</label>
        <select v-model="form.revision" :disabled="!form.tipo_revision" class="w-full border rounded px-3 py-2">
          <option v-if="!form.tipo_revision" value="">Primero elige el tipo…</option>
          <option v-for="(op, i) in opcionesRevision" :key="i" :value="op">
            {{ i + 1 }}. {{ op }}
          </option>
        </select>
        <p v-if="form.errors.revision" class="text-red-600 text-xs mt-1">{{ form.errors.revision }}</p> -->
      </div>
      <!-- Sociedad y Autoridad -->
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Sociedad</label>
         <!-- Sociedad -->
<select
  v-model="form.idempresa"
  @change="form.idempresa = toNumberOrEmpty(form.idempresa)"
  class="w-full border rounded px-3 py-2"
>
  <option value="">Seleccione…</option>
  <option
    v-for="s in sociedades"
    :key="s.id"
    :value="s.id"
  >
    {{ s.nombre }}
  </option>
</select>
<p v-if="form.errors.idempresa" class="text-sm text-red-600 mt-1">{{ form.errors.idempresa }}</p>

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

     

     <div class="space-y-4">
    <!-- Selector para agregar años -->
    <div class="flex items-end gap-3">
      <div class="grow">
        <label class="block text-sm font-medium mb-1">Año</label>
        <select v-model="nuevoAnio.value" class="w-full border rounded px-3 py-2">
          <option value="">Seleccione…</option>
          <option
            v-for="y in aniosDisponibles"
            :key="y"
            :value="y"
            :disabled="aniosSeleccionados.includes(y)"
          >
            {{ y }}
          </option>
        </select>
      </div>
      <button type="button" @click="agregarAnio"
              class="h-10 px-4 rounded bg-emerald-600 text-white hover:bg-emerald-700">
        Añadir año
      </button>
    </div>
    <p v-if="form.errors['periodos']" class="text-red-600 text-xs">
      {{ form.errors['periodos'] }}
    </p>

    <!-- Bloques por año con sus meses -->
    <div v-for="p in form.periodos" :key="p.anio" class="border rounded-lg p-4 space-y-3">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Meses de {{ p.anio }}</h3>
        <button type="button" @click="eliminarAnio(p.anio)"
                class="text-sm text-red-600 hover:underline">Quitar año</button>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
        <label v-for="m in MESES" :key="m.v"
               class="inline-flex items-center gap-2 border rounded px-3 py-2">
          <input
            type="checkbox"
            :checked="mesesDe(p.anio).includes(m.v)"
            @change="toggleMes(p.anio, m.v)"
          />
          <span>{{ m.label }}</span>
        </label>
      </div>

      <p v-if="form.errors[`periodos.${form.periodos.indexOf(p)}.meses`]"
         class="text-red-600 text-xs">
        {{ form.errors[`periodos.${form.periodos.indexOf(p)}.meses`] }}
      </p>
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
        <label class="block text-sm font-medium mb-1">Observaciones impuesto</label>
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
        <label class="block text-sm font-medium mb-1">Empresa compulsada</label>
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
