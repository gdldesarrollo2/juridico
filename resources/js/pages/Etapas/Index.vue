<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import TopNavLayout from '@/layouts/TopNavLayout.vue';
// --- Tipos auxiliares ---
type TipoJuicio = 'nulidad' | 'revocacion'
type Auth = {
  user?: { id:number; name:string; email?:string } | null
  roles?: string[]
  can?: string[]
}
const props = defineProps<{
    auth?: Auth,                     // 👈 llega desde HandleInertiaRequests.share
  catalogoEtapas: Record<TipoJuicio, string[]>
  juicio: {
    id: number
    tipo: TipoJuicio            // ⬅️ Asegúrate de enviar esto desde el backend
    nombre?: string
    cliente?: { id:number; nombre:string } | null
  }
  fecha_inicio_juicio?: string|null
  catalogos: {
    etiquetas: Array<{id:number; nombre:string}>
    abogados: Array<{id:number; nombre:string}>
    estatuses: Array<{value:string; label:string}>
  }
  etapas: Array<{
    id:number
    etiqueta?: { id:number; nombre:string } | null
    etapa:string
    abogado?: { id:number; nombre:string } | null
    rol?: string|null
    comentarios?: string|null
    dias_vencimiento:number
    fecha_vencimiento?: string|null
    estatus: string
    archivo_path?: string|null
    created_at:string
  }>
}>()
// Permisos seguros (sin usePage)
const canList = computed<string[]>(() => props.auth?.can ?? [])
const $can = (perm: string) => canList.value.includes(perm)
// === Opciones de etapa según el tipo del juicio ===
const opcionesEtapa = computed<string[]>(() => {
  return props.catalogoEtapas[props.juicio.tipo] ?? []
})

const form = useForm({
  etiqueta_id: '',
  etapa: '',                   // ← será una opción del catálogo
  abogado_id: '',
  rol: '',
  comentarios: '',
  fecha_inicio: '',
  dias_vencimiento: 0,
  fecha_vencimiento: '',
  estatus: 'en_tramite',
  archivo: null as File|null,
})

// Si el tipo cambia (o al montar), asegura que etapa tenga una opción válida
watch(
  () => [props.juicio.tipo, opcionesEtapa.value],
  () => {
    if (!opcionesEtapa.value.includes(form.etapa)) {
      form.etapa = opcionesEtapa.value[0] ?? ''
    }
  },
  { immediate: true, deep: true }
)

function submit() {
  form.post(route('juicios.etapas.store', props.juicio.id), { forceFormData: true })
}

const fmtDate = (v: any) => v ? new Intl.DateTimeFormat('es-MX').format(new Date(v)) : '—'
</script>

<template>
  <TopNavLayout></TopNavLayout>
  <div class="p-6 space-y-6">
    <div class="text-center">
      <h1 class="text-3xl font-semibold text-gray-900 dark:text-black-100">CAPTURA DE ETAPA DE JUICIO</h1>
      <p class="mt-2 text-gray-600 dark:text-gray-600">
        ID JUICIO: #{{ juicio.id }}
        <span v-if="juicio.cliente"> CLIENTE: {{ juicio.cliente.nombre }}</span>
        • <span class="capitalize">tipo: {{ juicio?.tipo }}</span>
      </p>
    </div>

    <!-- Formulario -->
    <form @submit.prevent="submit" class="bg-whiterounded shadow p-4 space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-600 dark:text-gray-600">Fecha de inicio de la etapa</label>
          <input v-model="form.fecha_inicio" type="date"
                 class="mt-1 w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-600 dark:text-gray-600">Etiqueta</label>
          <select v-model="form.etiqueta_id"
                  class="mt-1 w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
            <option value="">—</option>
            <option v-for="e in catalogos.etiquetas" :key="e.id" :value="e.id">{{ e.nombre }}</option>
          </select>
        </div>

        <!-- ⬇️ Etapa ahora es SELECT dependiente del tipo -->
        <div>
          <label class="block text-sm font-medium text-gray-600 dark:text-gray-600">
            Etapa (catálogo para {{ juicio.tipo }})
          </label>
          <select v-model="form.etapa"
                  class="mt-1 w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
            <option v-for="et in opcionesEtapa" :key="et" :value="et">{{ et }}</option>
          </select>
          <div v-if="form.errors.etapa" class="text-red-400 text-xs">{{ form.errors.etapa }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-600 dark:text-gray-600">Abogado</label>
          <select v-model="form.abogado_id"
                  class="mt-1 w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
            <option value="">—</option>
            <option v-for="u in catalogos.abogados" :key="u.id" :value="u.id">{{ u.nombre }}</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-600 dark:text-gray-600">Rol(opcional)</label>
          <input v-model="form.rol" type="text"
                 class="mt-1 w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700"
                 placeholder="Mensajero">
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-600 dark:text-gray-600">Comentarios/Descripción</label>
          <textarea v-model="form.comentarios"
                    class="mt-1 w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700"
                    rows="3"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-600 dark:text-gray-600">Días (para vencimiento)</label>
          <input v-model.number="form.dias_vencimiento" type="number" min="0"
                 class="mt-1 w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
          <p class="text-xs text-gray-500 mt-1">
            Fecha inicio del juicio: <strong>{{ props.fecha_inicio_juicio ?? '—' }}</strong>
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-600 dark:text-gray-600">Fecha de vencimiento (opcional)</label>
          <input v-model="form.fecha_vencimiento" type="date"
                 class="mt-1 w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
          <p class="text-xs text-gray-500 mt-1">Si se deja vacía, se calcula con fecha inicio + días.</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-600 dark:text-gray-600">Estatus</label>
          <select v-model="form.estatus"
                  class="mt-1 w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
            <option v-for="s in catalogos.estatuses" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-600 dark:text-gray-600">Archivo</label>
          <input type="file"
                 @change="e => form.archivo = (e.target as HTMLInputElement).files?.[0] ?? null"
                 class="mt-1 w-full text-sm file:mr-3 file:px-3 file:py-2 file:rounded file:border file:bg-gray-100 dark:file:bg-gray-300 file:cursor-pointer" />
        </div>
      </div>

      <div class="flex justify-end gap-2">
        <Link :href="route('juicios.index')" class="px-4 py-2 rounded border dark:border-gray-600 dark:text-gray-900">Volver</Link>
        <button  v-if="$can('crear etapa de juicio')" type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700" :disabled="form.processing">
          Guardar
        </button>
      </div>
    </form>

    <!-- Listado -->
    <div class="bg-white rounded shadow overflow-x-auto">
      <table class="min-w-full text-sm text-black">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left px-4 py-2">Fecha vencimiento</th>
            <th class="text-left px-4 py-2">Etiqueta</th>
            <th class="text-left px-4 py-2">Etapa</th>
            <th class="text-left px-4 py-2">Usuario</th>
            <th class="text-left px-4 py-2">Estatus</th>
            <th class="text-left px-4 py-2">Comentarios</th>
            <th class="text-left px-4 py-2">Archivo</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="e in props.etapas" :key="e.id" class="border-t dark:border-gray-700">
            <td class="px-4 py-2">{{ fmtDate(e.fecha_vencimiento) }}</td>
            <td class="px-4 py-2">{{ e.etiqueta?.nombre ?? '—' }}</td>
            <td class="px-4 py-2">{{ e.etapa }}</td>
            <td class="px-4 py-2">{{ e.abogado?.nombre ?? '—' }}</td>
            <td class="px-4 py-2">
              <span class="px-2 py-0.5 rounded text-xs"
                    :class="{
                      'bg-yellow-100 dark:bg-yellow-200/20 border border-yellow-300 dark:border-yellow-400': e.estatus==='en_tramite',
                      'bg-blue-100 dark:bg-blue-200/20 border border-blue-300 dark:border-blue-400': e.estatus==='en_juicio',
                      'bg-green-100 dark:bg-green-200/20 border border-green-300 dark:border-green-400': e.estatus==='concluido',
                      'bg-gray-200 dark:bg-gray-500/30 border border-gray-300 dark:border-gray-500': e.estatus==='cancelado',
                    }">
                {{ e.estatus }}
              </span>
            </td>
            <td class="px-4 py-2">{{ e.comentarios ?? '—' }}</td>
            <td class="px-4 py-2">
              <a v-if="e.archivo_path" :href="`/storage/${e.archivo_path}`" target="_blank" class="text-blue-600 hover:underline">Ver</a>
              <span v-else>—</span>
            </td>
          </tr>
          <tr v-if="props.etapas.length === 0">
            <td colspan="7" class="px-4 py-6 text-center text-gray-500">Sin etapas registradas</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
