<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3'
import { ref} from 'vue'

type Cat = { id:number; nombre:string }
type Opcion = { value:string; label:string }

const props = defineProps<{
  juicios: {
    data: Array<{
      id:number
      nombre:string
      cliente?: { id:number, nombre:string } | null
      autoridad?: { id:number, nombre:string } | null
      abogado?: { id:number, nombre:string } | null
      etiquetas: Array<{ id:number, nombre:string }>
      fecha_inicio?: string|null
      monto?: string|null
      estatus: 'juicio'|'autorizado'|'en_proceso'|'concluido'
      created_at: string
    }>
    links: Array<{ url:string|null, label:string, active:boolean }>
    meta?: any
  }
  filters: {
    q?: string
    cliente_id?: number|null
    etiqueta_id?: number|null
    estatus?: string|null
    fecha_desde?: string|null
    fecha_hasta?: string|null
    sort?: string
  }
  catalogos: {
    clientes: Cat[]
    etiquetas: Cat[]
    estatuses: Opcion[]
    sorts: Opcion[]
  }
}>()

// Estado local (llenamos con filtros actuales)
const q           = ref(props.filters.q ?? '')
const cliente_id  = ref<number|null>(props.filters.cliente_id ?? null)
const etiqueta_id = ref<number|null>(props.filters.etiqueta_id ?? null)
const estatus     = ref<string|undefined>(props.filters.estatus ?? undefined)
const fecha_desde = ref(props.filters.fecha_desde ?? '')
const fecha_hasta = ref(props.filters.fecha_hasta ?? '')
const sort        = ref(props.filters.sort ?? '-fecha_inicio')

// Disparar búsqueda
function applyFilters(page?: number) {
  const params:any = {
    q: q.value || undefined,
    cliente_id: cliente_id.value || undefined,
    etiqueta_id: etiqueta_id.value || undefined,
    estatus: estatus.value || undefined,
    fecha_desde: fecha_desde.value || undefined,
    fecha_hasta: fecha_hasta.value || undefined,
    sort: sort.value || undefined,
    page: page || undefined,
  }
  router.get(route('juicios.index'), params, {
    preserveState: true,
    replace: true,
  })
}

// Paginación usando los links que manda Laravel
function goTo(link:string|null) {
  if (!link) return
  // Mantener filtros al cambiar de página
  // Inertia ya enviará querystring del link
  router.get(link, {}, { preserveState: true, replace: true })
}

// Formateo simple
const fmtMoney = (v: any) => {
  if (v === null || v === undefined || v === '') return '—'
  const n = Number(v)
  if (Number.isNaN(n)) return String(v)
  return new Intl.NumberFormat('es-MX', { style:'currency', currency:'MXN' }).format(n)
}
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Juicios</h1>
      <Link href="/juicios/create" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
        Nuevo
      </Link>
    </div>

    <!-- Filtros -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-3 bg-white rounded p-4 shadow">
      <div class="md:col-span-2">
        <label class="block text-sm font-medium">Buscar</label>
        <input v-model="q" @keyup.enter="applyFilters()" class="mt-1 w-full border rounded px-3 py-2 bg-white text-black dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700" placeholder="Nombre / Nº juicio / Nº expediente">
      </div>

      <div>
        <label class="block text-sm font-medium">Cliente</label>
        <select v-model="cliente_id" class="mt-1 w-full border rounded px-3 py-2 bg-white text-black dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
          <option :value="null">—</option>
          <option v-for="c in props.catalogos.clientes" :key="c.id" :value="c.id">{{ c.nombre }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium">Etiqueta</label>
        <select v-model="etiqueta_id" class="mt-1 w-full border rounded px-3 py-2 bg-white text-black dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
          <option :value="null">—</option>
          <option v-for="e in props.catalogos.etiquetas" :key="e.id" :value="e.id">{{ e.nombre }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium">Estatus</label>
        <select v-model="estatus" class="mt-1 w-full border rounded px-3 py-2 bg-white text-black dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
          <option :value="undefined">—</option>
          <option v-for="s in props.catalogos.estatuses" :key="s.value" :value="s.value">{{ s.label }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium">Orden</label>
        <select v-model="sort" class="mt-1 w-full border rounded px-3 py-2 bg-white text-black dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
          <option v-for="s in props.catalogos.sorts" :key="s.value" :value="s.value">{{ s.label }}</option>
        </select>
      </div>

      <div class="md:col-span-3">
        <label class="block text-sm font-medium">Desde</label>
        <input type="date" v-model="fecha_desde" class="mt-1 w-full border rounded px-3 py-2 bg-white text-black dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700"/>
      </div>
      <div class="md:col-span-3">
        <label class="block text-sm font-medium">Hasta</label>
        <input type="date" v-model="fecha_hasta" class="mt-1 w-full border rounded px-3 py-2 bg-white text-black dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700" />
      </div>

      <div class="md:col-span-6 flex gap-2 justify-end">
        <button @click="applyFilters()" class="px-4 py-2 rounded bg-gray-800 text-white hover:bg-black">Aplicar</button>
        <button class="px-4 py-2 rounded bg-gray-800 text-white hover:bg-black" @click="() => { q=''; cliente_id=null; etiqueta_id=null; estatus=undefined; fecha_desde=''; fecha_hasta=''; sort='-fecha_inicio'; applyFilters(); }"
              >
          Limpiar
        </button>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left px-4 py-2">Nombre</th>
            <th class="text-left px-4 py-2">Cliente</th>
            <th class="text-left px-4 py-2">Etiqueta(s)</th>
            <th class="text-left px-4 py-2">Estatus</th>
            <th class="text-left px-4 py-2">Fecha inicio</th>
            <th class="text-right px-4 py-2">Monto</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="j in props.juicios.data" :key="j.id" class="border-t">
            <td class="px-4 py-2">
              <div class="font-medium">{{ j.nombre }}</div>
              <div class="text-gray-500 text-xs">#{{ j.id }}</div>
            </td>
            <td class="px-4 py-2">{{ j.cliente?.nombre ?? '—' }}</td>
            <td class="px-4 py-2">
              <div class="flex flex-wrap gap-1">
                <span v-for="et in j.etiquetas" :key="et.id" class="px-2 py-0.5 rounded-full text-xs bg-gray-100 border">{{ et.nombre }}</span>
              </div>
            </td>
            <td class="px-4 py-2">
              <span class="px-2 py-0.5 rounded text-xs"
                    :class="{
                      'bg-yellow-100 border border-yellow-300': j.estatus==='juicio',
                      'bg-blue-100 border border-blue-300': j.estatus==='en_proceso',
                      'bg-green-100 border border-green-300': j.estatus==='autorizado',
                      'bg-gray-200 border border-gray-300': j.estatus==='concluido',
                    }">
                {{ j.estatus }}
              </span>
            </td>
            <td class="px-4 py-2">{{ j.fecha_inicio ?? '—' }}</td>
            <td class="px-4 py-2 text-right">{{ fmtMoney(j.monto) }}</td>
          </tr>

          <tr v-if="props.juicios.data.length === 0">
            <td colspan="6" class="px-4 py-6 text-center text-gray-500">Sin resultados</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div class="flex flex-wrap gap-2">
      <button
        v-for="l in props.juicios.links"
        :key="l.label"
        :disabled="!l.url"
        @click="goTo(l.url)"
        class="px-3 py-1 rounded border"
        :class="{
          'bg-gray-800 text-white border-gray-800': l.active,
          'opacity-50 cursor-not-allowed': !l.url
        }"
        v-html="l.label"
      />
    </div>
  </div>
</template>
