<script setup lang="ts">
import TopNavLayout from '@/layouts/TopNavLayout.vue'
import { useForm, router } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

type Etapa = {
  id?: number
  etapa: string
  nombre?: string
  fecha_inicio: string
  dias_vencimiento?: number|null
  fecha_vencimiento?: string|null
  abogado_id?: number|null
  comentarios?: string|null
  estatus: string
}

const props = defineProps<{
  revision: any,
  etapas: Etapa[],
  etapasCatalogo: string[]   // 👈 NUEVO: viene desde el controlador
}>()

// formulario de creación
const form = useForm<Etapa>({
  etapa: '',
  nombre: '',
  fecha_inicio: '',
  dias_vencimiento: 0,
  fecha_vencimiento: '',
  abogado_id: null,
  comentarios: '',
  estatus: 'PENDIENTE',
})

const vencePreview = computed(() => {
  if (!form.fecha_inicio || !form.dias_vencimiento) return ''
  const d = new Date(form.fecha_inicio)
  d.setDate(d.getDate() + Number(form.dias_vencimiento))
  return d.toISOString().slice(0,10)
})

function submit(){
  form.post(route('revisiones.etapas.store', props.revision.id), { preserveScroll:true })
}

function destroyRow(id:number){
  if(!confirm('¿Eliminar etapa?')) return
  router.delete(route('revisiones.etapas.destroy',[props.revision.id, id]), { preserveScroll:true })
}

watch(()=>form.orden,(n)=>{
  const sug = props.sugeridas.find(s=>s.orden===Number(n))
  if (sug && !form.nombre) form.nombre = sug.nombre
})
</script>

<template>
  <TopNavLayout>
    <div class="bg-white border rounded p-5">
      <h1 class="text-2xl font-semibold text-center mb-3">
        CAPTURA DE ETAPA DE REVISIONES
      </h1>

      <div class="text-sm text-gray-600 mb-4">
        <strong>ID REVISIÓN: #{{ revision.id }}</strong>
        &nbsp;CLIENTE: <strong>{{ revision.cliente?.nombre }}</strong>
        <span v-if="revision.revision"> / {{ revision.revision }}</span>
      </div>

      <!-- Formulario -->
      <form @submit.prevent="submit" class="grid md:grid-cols-2 gap-4 bg-indigo-50/40 p-4 rounded">
        <div>
          <div class="space-y-1">
  <label class="block text-sm font-medium">Etapa</label>
  <select v-model="form.etapa" class="border rounded px-2 py-1 w-full">
    <option value="" disabled>Seleccione…</option>
    <option v-for="(e, idx) in props.etapasCatalogo" :key="idx" :value="e">
      {{ e }}
    </option>
  </select>
  <p v-if="form.errors.etapa" class="text-xs text-red-600">{{ form.errors.etapa }}</p>
</div>

        </div>

        <div>
          <label class="block text-sm font-medium">Fecha</label>
          <input v-model="form.fecha_inicio" type="date" class="mt-1 w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="block text-sm font-medium">Vencimiento (días)</label>
          <div class="flex items-center gap-3">
            <input v-model.number="form.dias_vencimiento" type="number" min="1"
                   class="mt-1 w-24 border rounded px-3 py-2">
            <span class="text-sm text-gray-600">Vence: <strong>{{ vencePreview || '—' }}</strong></span>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium">Comentarios</label>
          <textarea v-model="form.comentarios" rows="3" class="mt-1 w-full border rounded px-3 py-2"
                    placeholder="Escriba…"/>
        </div>

        <div>
          <label class="block text-sm font-medium">Estatus</label>
          <select v-model="form.estatus" class="mt-1 w-full border rounded px-3 py-2">
            <option value="pendiente">Pendiente</option>
            <option value="en_proceso">En proceso</option>
            <option value="atendido">Atendido</option>
            <option value="cerrado">Cerrado</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium">Abogado</label>
          <select v-model="form.abogado_id" class="mt-1 w-full border rounded px-3 py-2">
            <option value="">—</option>
            <option v-for="a in props.abogados" :key="a.id" :value="a.id">{{ a.nombre }}</option>
          </select>
        </div>

        <div class="md:col-span-2 flex justify-end">
          <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
            Guardar
          </button>
        </div>
      </form>

      <!-- Tabla -->
      <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-800 text-white">
            <tr>
              <th class="text-left px-4 py-2">Fecha inicio</th>
              <th class="text-left px-4 py-2">Etapa</th>
              <th class="text-left px-4 py-2">Vencimiento</th>
              <th class="text-left px-4 py-2">Usuario captura</th>
              <th class="text-left px-4 py-2">Estatus</th>
              <th class="text-left px-4 py-2">Comentarios</th>
              <th class="text-right px-4 py-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in props.etapas" :key="e.id" class="border-b">
              <td class="px-4 py-2">{{ e.fecha_inicio ?? '—' }}</td>
              <td class="px-4 py-2">{{ e.orden }}. {{ e.nombre }}</td>
              <td class="px-4 py-2">{{ e.vence ?? '—' }}</td>
              <td class="px-4 py-2">{{ e.usuario?.name ?? '—' }}</td>
              <td class="px-4 py-2">{{ e.estatus.toUpperCase() }}</td>
              <td class="px-4 py-2">{{ e.comentarios ?? '—' }}</td>
              <td class="px-4 py-2 text-right">
                <button @click="destroyRow(e.id)" class="px-2 py-1 rounded border text-xs text-red-600 hover:bg-red-50">
                  Eliminar
                </button>
              </td>
            </tr>
            <tr v-if="props.etapas.length===0">
              <td colspan="7" class="px-4 py-6 text-center text-gray-500">Sin etapas</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </TopNavLayout>
</template>
