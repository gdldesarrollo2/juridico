<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'

type Opcion = { value: string|number; label: string }
const props = defineProps<{
  catalogos: {
    clientes: Array<{id:number; nombre:string}>
    autoridades: Array<{id:number; nombre:string}>
    etiquetas: Array<{id:number; nombre:string}>
    abogados: Array<{id:number; nombre:string}>
    tipos: Opcion[]
    estatuses: Opcion[]
  },
  defaults: { estatus: string }
}>()

const form = useForm({
  nombre: '',
  tipo: 'nulidad',
  cliente_id: null as number|null,
  autoridad_id: null as number|null,
  fecha_inicio: '',
  monto: null as number|null,
  observaciones_monto: '',
  resolucion_impugnada: '',
  garantia: '',
  numero_juicio: '',
  numero_expediente: '',
  estatus: props.defaults.estatus,
  abogado_id: null as number|null,
  etiquetas: [] as number[],
})

function submit() {
  form.post(route('juicios.store'))
}
</script>

<template>
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-3xl font-semibold mb-6">Juicios y Recursos de Revocación</h1>

    <div class="space-y-5">
      <div>
        <label class="block text-sm font-medium">Nombre/identificador</label>
        <input v-model="form.nombre" class="mt-1 w-full border rounded px-3 py-2" placeholder="JUICIO #2 ARMONIA VALLARTA" />
        <div v-if="form.errors.nombre" class="text-red-600 text-sm">{{ form.errors.nombre }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium">Etiqueta(s)</label>
        <select multiple v-model="form.etiquetas" class="mt-1 w-full border rounded px-3 py-2 h-28">
          <option v-for="e in props.catalogos.etiquetas" :key="e.id" :value="e.id">{{ e.nombre }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium">Tipo</label>
        <div class="mt-2 flex gap-6">
          <label class="inline-flex items-center gap-2">
            <input type="radio" value="nulidad" v-model="form.tipo" />
            <span>Juicio de nulidad</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="radio" value="revocacion" v-model="form.tipo" />
            <span>Recurso de revocación</span>
          </label>
        </div>
        <div v-if="form.errors.tipo" class="text-red-600 text-sm">{{ form.errors.tipo }}</div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Cliente</label>
          <select v-model="form.cliente_id" class="mt-1 w-full border rounded px-3 py-2">
            <option :value="null" disabled>Selecciona cliente…</option>
            <option v-for="c in props.catalogos.clientes" :key="c.id" :value="c.id">{{ c.nombre }}</option>
          </select>
          <div v-if="form.errors.cliente_id" class="text-red-600 text-sm">{{ form.errors.cliente_id }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium">Autoridad</label>
          <select v-model="form.autoridad_id" class="mt-1 w-full border rounded px-3 py-2">
            <option :value="null">—</option>
            <option v-for="a in props.catalogos.autoridades" :key="a.id" :value="a.id">{{ a.nombre }}</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium">Resolución impugnada</label>
        <input v-model="form.resolucion_impugnada" class="mt-1 w-full border rounded px-3 py-2" placeholder="Escriba…" />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Fecha inicio</label>
          <input type="date" v-model="form.fecha_inicio" class="mt-1 w-full border rounded px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm font-medium">Monto</label>
          <input type="number" step="0.01" min="0" v-model.number="form.monto" class="mt-1 w-full border rounded px-3 py-2" placeholder="$ Escriba monto" />
          <div v-if="form.errors.monto" class="text-red-600 text-sm">{{ form.errors.monto }}</div>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium">Observaciones monto</label>
        <textarea v-model="form.observaciones_monto" class="mt-1 w-full border rounded px-3 py-2" rows="3" placeholder="Escriba…"></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium">Garantía</label>
        <input v-model="form.garantia" class="mt-1 w-full border rounded px-3 py-2" placeholder="Placeholder" />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Número de juicio</label>
          <input v-model="form.numero_juicio" class="mt-1 w-full border rounded px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm font-medium">Número de expediente</label>
          <input v-model="form.numero_expediente" class="mt-1 w-full border rounded px-3 py-2" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Estatus general</label>
          <select v-model="form.estatus" class="mt-1 w-full border rounded px-3 py-2">
            <option v-for="s in props.catalogos.estatuses" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium">Abogado</label>
          <select v-model="form.abogado_id" class="mt-1 w-full border rounded px-3 py-2">
            <option :value="null">—</option>
            <option v-for="p in props.catalogos.abogados" :key="p.id" :value="p.id">{{ p.nombre }}</option>
          </select>
        </div>
      </div>

      <div class="pt-2">
        <button @click="submit" :disabled="form.processing"
                class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
          Guardar
        </button>
      </div>
    </div>
  </div>
</template>
