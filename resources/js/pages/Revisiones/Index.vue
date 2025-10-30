<script setup lang="ts">
import { reactive, watch, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import debounce from 'lodash/debounce'
import TopNavLayout from '@/layouts/TopNavLayout.vue'

type Opcion = { value: string; label: string }
type OptIdNombre = { id: number; nombre: string }
type OptIdName = { id: number; name: string }

type Row = {
  id: number
  tipo: string
  sociedad: string | null
  sociedad_id: number | null
  autoridad: string | null
  autoridad_id: number | null
  periodo: string | null
  estatus: string
  empresa_compulsada?: string | null
  observaciones?: string | null
  usuario?: string | null
  usuario_id?: number | null
  nombre?: string | null // nombre de la última etapa (si lo mandas)
  ultima_etapa: string | null
}

const props = defineProps<{
  auth?: Auth,                     // 👈 llega desde HandleInertiaRequests.share
  revisiones: { data: Row[]; links: any[] }
  filters: { tipo?: string; sociedad_id?: string | number; usuario_id?: string | number; autoridad_id?: string | number; estatus?: string; q?: string }
  options: {
    tipos: Opcion[]
    sociedades: OptIdNombre[]
    autoridades: OptIdNombre[]
    usuarios: OptIdName[]
    estatus: Opcion[]
  }
}>()

// --- permisos sin $can global ---


// --- estado de filtros (reactivo) ---
const f = reactive({
  tipo: props.filters.tipo ?? '',
  sociedad_id: props.filters.sociedad_id ? String(props.filters.sociedad_id) : '',
  usuario_id: props.filters.usuario_id ? String(props.filters.usuario_id) : '',
  autoridad_id: props.filters.autoridad_id ? String(props.filters.autoridad_id) : '',
  estatus: props.filters.estatus ?? '',
  q: props.filters.q ?? '',
})

function cleanParams() {
  const p: Record<string, string> = {}
  if (f.tipo) p.tipo = f.tipo
  if (f.sociedad_id) p.sociedad_id = f.sociedad_id
  if (f.usuario_id) p.usuario_id = f.usuario_id
  if (f.autoridad_id) p.autoridad_id = f.autoridad_id
  if (f.estatus) p.estatus = f.estatus
  if (f.q) p.q = f.q
  return p
}

function push() {
  router.get(route('revisiones.index'), cleanParams(), {
    preserveState: true,
    replace: true,
    only: ['revisiones', 'filters'],
  })
}

watch(() => [f.tipo, f.sociedad_id, f.usuario_id, f.autoridad_id, f.estatus], push)
const debouncedSearch = debounce(push, 400)
watch(() => f.q, debouncedSearch)

function resetFilters() {
  f.tipo = ''
  f.sociedad_id = ''
  f.usuario_id = ''
  f.autoridad_id = ''
  f.estatus = ''
  f.q = ''
  router.get(route('revisiones.index'), {}, { preserveState: true, replace: true, only: ['revisiones', 'filters'] })
}
// Permisos robustos (sin $can global)
type CanShape = string[] | Record<string, boolean> | undefined

function can(perm: string): boolean {
  // usePage() puede no tener props en el primer tick → protege todo con optional chaining
  const pg: any = usePage()                       // no tipar agresivo aquí
  const c: CanShape = pg?.props?.value?.auth?.can // puede ser array u objeto… o nada

  if (Array.isArray(c)) return c.includes(perm)
  if (c && typeof c === 'object') return Boolean((c as Record<string, boolean>)[perm])
  return false // si aún no hay props, mejor esconder
}
const canList = computed<string[]>(() => props.auth?.can ?? [])
const $can = (perm: string) => canList.value.includes(perm)
</script>

<template>
  <TopNavLayout></TopNavLayout>
  <br><br>
  <div class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-3">
    <select v-model="f.tipo" class="border rounded px-2 py-1">
      <option value="">Tipo…</option>
      <option v-for="t in options.tipos" :key="t.value" :value="t.value">{{ t.label }}</option>
    </select>

    <select v-model="f.sociedad_id" class="border rounded px-2 py-1">
      <option value="">Sociedad…</option>
      <option v-for="s in options.sociedades" :key="s.id" :value="String(s.id)">{{ s.nombre }}</option>
    </select>

    <select v-model="f.autoridad_id" class="border rounded px-2 py-1">
      <option value="">Autoridad…</option>
      <option v-for="a in options.autoridades" :key="a.id" :value="String(a.id)">{{ a.nombre }}</option>
    </select>

    <select v-model="f.usuario_id" class="border rounded px-2 py-1">
      <option value="">Usuario captura…</option>
      <option v-for="u in options.usuarios" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
    </select>

    <select v-model="f.estatus" class="border rounded px-2 py-1">
      <option value="">Estatus…</option>
      <option v-for="e in options.estatus" :key="e.value" :value="e.value">{{ e.label }}</option>
    </select>

    <input v-model="f.q" type="search" placeholder="Buscar en observaciones…" class="border rounded px-3 py-1" />
  </div>

  <div class="mb-3">
    <button @click="resetFilters" class="text-sm text-gray-600 hover:underline">Limpiar filtros</button>
  </div>

  <table class="min-w-full text-sm">
    <thead>
      <tr class="text-left border-b">
        <th class="py-2 pr-3">Tipo</th>
        <th class="py-2 pr-3">Sociedad</th>
        <th class="py-2 pr-3">Autoridad</th>
        <th class="py-2 pr-3">Periodo</th>
        <th class="py-2 pr-3">Estatus</th>
        <th class="py-2 pr-3">Empresa compulsada</th>
        <th class="py-2 pr-3">Observaciones</th>
        <th class="py-2 pr-3">Ultima etapa</th>
        <th class="py-2 pr-3">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="rev in revisiones.data" :key="rev.id" class="border-b">
        <td class="py-2 pr-3">{{ rev.tipo }}</td>
        <td class="py-2 pr-3">{{ rev.sociedad || '—' }}</td>
        <td class="py-2 pr-3">{{ rev.autoridad || '—' }}</td>
        <td class="py-2 pr-3">{{ rev.periodo || '—' }}</td>
        <td class="py-2 pr-3">
          <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">{{ rev.estatus }}</span>
        </td>
        <td class="py-2 pr-3">{{ rev.empresa_compulsada || '—' }}</td>
        <td class="py-2 pr-3">{{ rev.observaciones || '—' }}</td>
        <td class="py-2 pr-3">
  <div v-if="rev.ultima_etapa">
    <div class="font-medium">{{ rev.ultima_etapa.nombre }}</div>
    <div class="text-xs text-gray-500">
      {{ rev.ultima_etapa.fecha_inicio || 'sin fecha' }}
      <span v-if="rev.ultima_etapa.fecha_vencimiento"> · vence: {{ rev.ultima_etapa.fecha_vencimiento }}</span>
    </div>
    <span
      v-if="rev.ultima_etapa.estatus"
      class="mt-0.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700"
    >
      {{ rev.ultima_etapa.estatus.toLowerCase() }}
    </span>
  </div>
  <span v-else>—</span>
</td>
        <td class="py-2 pr-3 space-x-2">
          <!-- Etapas: parámetro posicional; protege si no hay id -->
      

           <Link
    :href="route('revisiones.etapas.index', { revision: rev.id })"
    class="inline-flex items-center px-3 py-1.5 rounded border text-indigo-700 border-indigo-300 hover:bg-indigo-50"
  >
    Etapas
  </Link>
            <Link v-if="$can('editar revisiones')"  :href="route('revisiones.edit', rev.id)" class="text-blue-600 hover:underline">Editar</Link>
            <Link v-if="$can('eliminar revisiones')"  as="button" method="delete" :href="route('revisiones.destroy', rev.id)" class="text-red-600 hover:underline">Eliminar</Link>
        </td>
      </tr>
    </tbody>
  </table>

  <!-- Tu paginación si ya la tienes, usa revisiones.links -->
</template>
