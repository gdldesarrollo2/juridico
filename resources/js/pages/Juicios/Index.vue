<script setup lang="ts">
import { reactive, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue'

type Opcion = { id:number; nombre:string }
type EstatusOpt = { value:string; label:string }
type SortOpt = { value:string; label:string }

const props = defineProps<{
  juicios: {
    data: Array<{
      id: number
      numero_juicio: string|null
      nombre: string
      cliente?: { id:number; nombre:string } | null
      tipo: 'nulidad'|'revocacion'
      estatus: 'juicio'|'autorizado'|'en_proceso'|'concluido'
      fecha_inicio: string|null
      monto: number|string|null
      etiquetas?: { id:number; nombre:string }[]
      // 👇 llega desde el accessor del modelo; si no, mostramos fallback
      periodo_resumen?: string | null
      // por si el accessor aún no existe, mostramos fallback con esto (si llega):
      periodos?: unknown
    }>
    links: Array<{ url:string|null; label:string; active:boolean }>
    meta?: any
  }
  filters: {
    q?: string|null
    cliente_id?: number|null
    estatus?: string|null
    fecha_desde?: string|null
    fecha_hasta?: string|null
    etiqueta_id?: number|null
    sort?: string|null
    page?: number|null
  }
  catalogos: {
    clientes: Opcion[]
    etiquetas: Opcion[]
    estatuses: EstatusOpt[]
    sorts: SortOpt[]
  }
}>()

// ====== Filtros (estado local) ======
const f = reactive({
  q: props.filters.q ?? '',
  cliente_id: props.filters.cliente_id ?? '',
  estatus: props.filters.estatus ?? '',
  fecha_desde: props.filters.fecha_desde ?? '',
  fecha_hasta: props.filters.fecha_hasta ?? '',
  etiqueta_id: props.filters.etiqueta_id ?? '',
  sort: props.filters.sort ?? '-fecha_inicio',
  page: props.filters.page ?? 1,
})

function applyFilters() {
  router.get(route('juicios.index'), { ...f }, {
    preserveState: true,
    replace: true,
  })
}

watch(() => f.sort, applyFilters)
watch(() => f.cliente_id, applyFilters)
watch(() => f.estatus, applyFilters)
watch(() => f.etiqueta_id, applyFilters)
watch(() => f.fecha_desde, applyFilters)
watch(() => f.fecha_hasta, applyFilters)

// Buscar
function onSearch() {
  f.page = 1
  applyFilters()
}
function decodeLabel(lbl: string | null | undefined): string {
  if (!lbl) return ''
  // decodificar entidades (&laquo; &raquo; etc.) sin usar v-html
  const el = document.createElement('textarea')
  el.innerHTML = lbl
  return el.value.trim()
}
// Limpiar
function clearFilters() {
  f.q = ''
  f.cliente_id = ''
  f.estatus = ''
  f.fecha_desde = ''
  f.fecha_hasta = ''
  f.etiqueta_id = ''
  f.sort = '-fecha_inicio'
  f.page = 1
  applyFilters()
}

// ====== Helpers de formato ======
const fmtMoney = (v: any) => {
  if (v === null || v === undefined || v === '') return '—'
  const n = Number(v)
  if (Number.isNaN(n)) return String(v)
  return n.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })
}
const fmtDate = (d?: string|null) => d ? new Date(d).toLocaleDateString('es-MX') : '—'

// Fallback para periodos si no hay `periodo_resumen` desde el backend
const MES_ABBR = ['', 'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic']
type PeriodoArr = { anio:number; meses:number[] }[]
function normalizePeriodos(p: unknown): PeriodoArr {
  try {
    if (typeof p === 'string' && p.trim()) {
      const parsed = JSON.parse(p)
      return normalizePeriodos(parsed)
    }
    if (Array.isArray(p) && p.length && typeof (p as any)[0] === 'object' && 'anio' in (p as any)[0]) {
      return (p as any[]).map((x:any)=>({
        anio: Number(x.anio),
        meses: (x.meses ?? []).map((m:any)=>Number(m)).sort((a:number,b:number)=>a-b)
      }))
    }
    if (p && typeof p === 'object') {
      return Object.entries(p as Record<string, any>).map(([anio, meses])=>({
        anio: Number(anio),
        meses: (meses ?? []).map((m:any)=>Number(m)).sort((a:number,b:number)=>a-b)
      }))
    }
  } catch {}
  return []
}
function formatPeriodos(row:any): string {
  if (row.periodo_resumen) return row.periodo_resumen
  const arr = normalizePeriodos(row.periodos)
  if (!arr.length) return '—'
  arr.sort((a,b)=>b.anio - a.anio)
  return arr.map(r => r.meses.length
    ? `${r.anio}: ${r.meses.map(m => MES_ABBR[m] ?? m).join(', ')}`
    : `${r.anio}: —`
  ).join(' · ')
}

const estatusClass = (s:string) => {
  switch (s) {
    case 'juicio': return 'bg-yellow-100 border border-yellow-300 text-yellow-800'
    case 'autorizado': return 'bg-emerald-100 border border-emerald-300 text-emerald-800'
    case 'en_proceso': return 'bg-sky-100 border border-sky-300 text-sky-800'
    case 'concluido': return 'bg-gray-200 border border-gray-300 text-gray-800'
    default: return 'bg-slate-100 border border-slate-300 text-slate-800'
  }
}
</script>

<template>
  <TopNavLayout></TopNavLayout>
  <div class="p-4 space-y-4">
    <h1 class="text-2xl font-semibold">Juicios</h1>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow p-4 space-y-3">
      <div class="grid md:grid-cols-6 gap-3">
        <div class="md:col-span-2">
          <label class="block text-xs font-medium text-gray-500">Buscar</label>
          <div class="flex gap-2">
            <input v-model="f.q" @keyup.enter="onSearch" type="text" class="w-full border rounded px-2 py-1" placeholder="Nombre, No. juicio o expediente" />
            <button @click="onSearch" class="px-3 py-1 rounded bg-indigo-600 text-white">Buscar</button>
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-500">Cliente</label>
          <select v-model="f.cliente_id" class="w-full border rounded px-2 py-1">
            <option value="">—</option>
            <option v-for="c in catalogos.clientes" :key="c.id" :value="c.id">{{ c.nombre }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-500">Estatus</label>
          <select v-model="f.estatus" class="w-full border rounded px-2 py-1">
            <option value="">—</option>
            <option v-for="s in catalogos.estatuses" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-500">Desde</label>
          <input v-model="f.fecha_desde" type="date" class="w-full border rounded px-2 py-1" />
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-500">Hasta</label>
          <input v-model="f.fecha_hasta" type="date" class="w-full border rounded px-2 py-1" />
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-500">Etiqueta</label>
          <select v-model="f.etiqueta_id" class="w-full border rounded px-2 py-1">
            <option value="">—</option>
            <option v-for="e in catalogos.etiquetas" :key="e.id" :value="e.id">{{ e.nombre }}</option>
          </select>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-500">Ordenar</label>
          <select v-model="f.sort" class="border rounded px-2 py-1">
            <option v-for="s in catalogos.sorts" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>
        <button @click="clearFilters" class="px-3 py-1 rounded border">Limpiar</button>
      </div>
    </div>
    <Link :href="route('juicios.create')" class="px-3 py-1 rounded bg-indigo-600 text-white">Nuevo Juicio</Link><br>

    <!-- Tabla -->
    <div class="bg-white rounded-xl shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left px-3 py-2">Número de juicio</th>
            <th class="text-left px-3 py-2">Nombre</th>
            <th class="text-left px-3 py-2">Cliente</th>
            <th class="text-left px-3 py-2">Tipo</th>
            <th class="text-left px-3 py-2">Etiqueta(s)</th>
            <th class="text-left px-3 py-2">Estatus</th>
            <th class="text-left px-3 py-2">Fecha inicio</th>
            <th class="text-left px-3 py-2">Monto</th>
            <th class="text-left px-3 py-2">Periodo</th>
            <th class="text-left px-3 py-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="j in juicios.data" :key="j.id" class="border-t">
            <td class="px-3 py-2">{{ j.numero_juicio ?? '—' }}</td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-2">
                <span>{{ j.nombre }}</span>
                <span class="text-xs text-gray-500">#{{ j.id }}</span>
              </div>
            </td>
            <td class="px-3 py-2">{{ j.cliente?.nombre ?? '—' }}</td>
            <td class="px-3 py-2 capitalize">{{ j.tipo }}</td>
            <td class="px-3 py-2">
              <div class="flex flex-wrap gap-1">
                <span v-for="et in (j.etiquetas ?? [])" :key="et.id" class="px-2 py-0.5 rounded text-xs bg-slate-100 border border-slate-300">
                  {{ et.nombre }}
                </span>
                <span v-if="!j.etiquetas || j.etiquetas.length===0">—</span>
              </div>
            </td>
            <td class="px-3 py-2">
              <span class="px-2 py-0.5 rounded text-xs" :class="estatusClass(j.estatus)">{{ j.estatus }}</span>
            </td>
            <td class="px-3 py-2">{{ fmtDate(j.fecha_inicio) }}</td>
            <td class="px-3 py-2">{{ fmtMoney(j.monto) }}</td>
            <td class="px-3 py-2">{{ formatPeriodos(j) }}</td>
            <td class="px-3 py-2">
              <div class="flex gap-2">
                <Link :href="route('juicios.edit', j.id)" class="px-3 py-1 rounded bg-amber-500 text-white">Editar</Link>
                <Link :href="route('juicios.etapas.index', { juicio: j.id })" class="px-3 py-1 rounded bg-indigo-600 text-white">+ Etapas</Link>
                <Link
  :href="route('juicios.show', j.id)"
  class="px-3 py-1 rounded border text-gray-700 hover:bg-gray-50 mr-2"
>
  Ver juicio
</Link>

              </div>
            </td>
          </tr>
          <tr v-if="juicios.data.length === 0">
            <td colspan="10" class="px-3 py-6 text-center text-gray-500">Sin resultados.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación (simple) -->
    <!-- Paginación -->
<div class="flex flex-wrap items-center gap-2">
  <Link
    v-for="l in juicios.links"
    :key="l.label"
    :href="l.url || ''"
    class="px-3 py-1 rounded border"
    :class="[
      { 'bg-indigo-600 text-white border-indigo-600': l.active,
        'text-gray-700': !l.active },
      { 'pointer-events-none opacity-50': !l.url }
    ]"
    preserve-scroll
  >
    <span>{{ decodeLabel(l.label) }}</span>
  </Link>
</div>
  </div>
</template>
