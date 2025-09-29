<script setup lang="ts">
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

type Opcion = { id:number; nombre:string }
type JuicioDTO = {
  id?: number
  nombre?: string
  tipo?: 'nulidad'|'revocacion'
  cliente_id?: number|null
  autoridad_id?: number|null
  fecha_inicio?: string|null
  monto?: number|null
  observaciones_monto?: string|null
  resolucion_impugnada?: string|null
  garantia?: string|null
  numero_juicio?: string|null
  numero_expediente?: string|null
  estatus?: 'juicio'|'autorizado'|'en_proceso'|'concluido'
  abogado_id?: number|null
}

const props = defineProps<{
  mode: 'create'|'edit'
  // datos iniciales del juicio (vacíos para create)
  initial: JuicioDTO
  // catálogos
  clientes: Opcion[]
  autoridades: Opcion[]
  abogados: Opcion[]
  etiquetas: Opcion[]
  // etiquetas seleccionadas si estás en edit
  etiquetasSeleccionadas?: number[]
}>()

const isEdit = computed(()=> props.mode === 'edit')

// Inicializa el formulario con defaults + initial
const form = useForm({
  
  nombre: props.initial.nombre ?? '',
  tipo: props.initial.tipo ?? 'nulidad',
  cliente_id: props.initial.cliente_id ?? null,
  autoridad_id: props.initial.autoridad_id ?? null,
  fecha_inicio: props.initial.fecha_inicio ?? null,
  monto: props.initial.monto ?? null,
  observaciones_monto: props.initial.observaciones_monto ?? '',
  resolucion_impugnada: props.initial.resolucion_impugnada ?? '',
  garantia: props.initial.garantia ?? '',
  numero_juicio: props.initial.numero_juicio ?? '',
  numero_expediente: props.initial.numero_expediente ?? '',
  estatus: props.initial.estatus ?? 'juicio',
  abogado_id: props.initial.abogado_id ?? null,
  etiquetas: (props.etiquetasSeleccionadas ?? []) as number[], // importante: array
})

// URL de envío según modo
const submitUrl = computed(() => {
  if (isEdit.value) {
    try { return route('juicios.update', props.initial.id) }
    catch { return `/juicios/${props.initial.id}` }
  } else {
    try { return route('juicios.store') }
    catch { return `/juicios` }
  }
})

function submit(){
  if (isEdit.value) {
    form.put(submitUrl.value, {
      preserveScroll: true,
      onSuccess: () => {},
    })
  } else {
    form.post(submitUrl.value, {
      preserveScroll: true,
      onSuccess: () => form.reset('nombre','monto','observaciones_monto','resolucion_impugnada','garantia','numero_juicio','numero_expediente','fecha_inicio'),
    })
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Campos base -->
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Nombre</label>
        <input v-model="form.nombre" type="text" class="w-full rounded border border-slate-300" />
        <p v-if="form.errors.nombre" class="text-red-600 text-sm mt-1">{{ form.errors.nombre }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Tipo</label>
        <select v-model="form.tipo" class="w-full rounded border border-slate-300">
          <option value="nulidad">Nulidad</option>
          <option value="revocacion">Revocación</option>
        </select>
        <p v-if="form.errors.tipo" class="text-red-600 text-sm mt-1">{{ form.errors.tipo }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Cliente</label>
        <select v-model="form.cliente_id" class="w-full rounded border border-slate-300">
          <option :value="null">—</option>
          <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre }}</option>
        </select>
        <p v-if="form.errors.cliente_id" class="text-red-600 text-sm mt-1">{{ form.errors.cliente_id }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Autoridad</label>
        <select v-model="form.autoridad_id" class="w-full rounded border border-slate-300">
          <option :value="null">—</option>
          <option v-for="a in autoridades" :key="a.id" :value="a.id">{{ a.nombre }}</option>
        </select>
        <p v-if="form.errors.autoridad_id" class="text-red-600 text-sm mt-1">{{ form.errors.autoridad_id }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Fecha de inicio</label>
        <input v-model="form.fecha_inicio" type="date" class="w-full rounded border border-slate-300" />
        <p v-if="form.errors.fecha_inicio" class="text-red-600 text-sm mt-1">{{ form.errors.fecha_inicio }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Monto</label>
        <input v-model.number="form.monto" type="number" min="0" step="0.01" class="w-full rounded border border-slate-300" />
        <p v-if="form.errors.monto" class="text-red-600 text-sm mt-1">{{ form.errors.monto }}</p>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Observaciones del monto</label>
        <textarea v-model="form.observaciones_monto" rows="2" class="w-full rounded border border-slate-300"></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Resolución impugnada</label>
        <input v-model="form.resolucion_impugnada" type="text" class="w-full rounded border border-slate-300" />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Garantía</label>
        <input v-model="form.garantia" type="text" class="w-full rounded border border-slate-300" />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Número de juicio</label>
        <input v-model="form.numero_juicio" type="text" class="w-full rounded border border-slate-300" />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Número de expediente</label>
        <input v-model="form.numero_expediente" type="text" class="w-full rounded border border-slate-300" />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Estatus</label>
        <select v-model="form.estatus" class="w-full rounded border border-slate-300">
          <option value="juicio">Juicio</option>
          <option value="autorizado">Autorizado</option>
          <option value="en_proceso">En proceso</option>
          <option value="concluido">Concluido</option>
        </select>
        <p v-if="form.errors.estatus" class="text-red-600 text-sm mt-1">{{ form.errors.estatus }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Abogado</label>
        <select v-model="form.abogado_id" class="w-full rounded border border-slate-300">
          <option :value="null">—</option>
          <option v-for="ab in abogados" :key="ab.id" :value="ab.id">{{ ab.nombre }}</option>
        </select>
      </div>

      <!-- Multiselect simple de etiquetas (array de IDs) -->
      <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Etiquetas</label>
        <select multiple v-model="form.etiquetas" class="w-full rounded border border-slate-300 h-32">
          <option v-for="e in etiquetas" :key="e.id" :value="e.id">{{ e.nombre }}</option>
        </select>
        <p v-if="form.errors.etiquetas" class="text-red-600 text-sm mt-1">{{ form.errors.etiquetas }}</p>
      </div>
    </div>

    <div class="pt-2">
      <button :disabled="form.processing" @click="submit"
        class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50">
        {{ isEdit ? 'Actualizar' : 'Guardar' }}
      </button>
    </div>
  </div>
</template>
