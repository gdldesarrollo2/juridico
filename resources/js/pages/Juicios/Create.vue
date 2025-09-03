<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps<{
  catalogos: {
    clientes: Array<{id:number,nombre:string}>
    autoridades: Array<{id:number,nombre:string}>
    abogados: Array<{id:number,nombre:string}>
    etiquetas: Array<{id:number,nombre:string}>
    tipos: Array<{value:string,label:string}>
    estatuses: Array<{value:string,label:string}>
  }
  defaults: {
    estatus: string
  }
}>()

const form = useForm({
  nombre: '',
  tipo: '',
  cliente_id: '',
  autoridad_id: '',
  abogado_id: '',
  fecha_inicio: '',
  monto: '',
  observaciones_monto: '',
  resolucion_impugnada: '',
  garantia: '',
  numero_juicio: '',
  numero_expediente: '',
  estatus: props.defaults.estatus,
  etiquetas: [] as number[],
})

function submit() {
  form.post(route('juicios.store'))
}
</script>

<template>
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Nuevo Juicio</h1>

    <form @submit.prevent="submit" class="space-y-4 bg-white dark:bg-gray-800 rounded shadow p-6">
      <!-- Nombre -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre/Identificador</label>
        <input v-model="form.nombre" type="text"
               class="mt-1 w-full border rounded px-3 py-2
                      bg-white text-gray-900
                      dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700"
               :class="{'border-red-500': form.errors.nombre}" />
        <div v-if="form.errors.nombre" class="text-red-400 text-xs">{{ form.errors.nombre }}</div>
      </div>

      <!-- Tipo -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
        <div class="flex gap-4 mt-1">
          <label v-for="t in props.catalogos.tipos" :key="t.value" class="flex items-center gap-1 text-gray-900 dark:text-gray-100">
            <input type="radio" v-model="form.tipo" :value="t.value" class="text-blue-600 dark:text-blue-400" />
            {{ t.label }}
          </label>
        </div>
      </div>

      <!-- Cliente -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliente</label>
        <select v-model="form.cliente_id"
                class="mt-1 w-full border rounded px-3 py-2
                       bg-white text-gray-900
                       dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
          <option value="">—</option>
          <option v-for="c in props.catalogos.clientes" :key="c.id" :value="c.id">{{ c.nombre }}</option>
        </select>
      </div>

      <!-- Autoridad -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Autoridad</label>
        <select v-model="form.autoridad_id"
                class="mt-1 w-full border rounded px-3 py-2
                       bg-white text-gray-900
                       dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
          <option value="">—</option>
          <option v-for="a in props.catalogos.autoridades" :key="a.id" :value="a.id">{{ a.nombre }}</option>
        </select>
      </div>

      <!-- Abogado -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Abogado</label>
        <select v-model="form.abogado_id"
                class="mt-1 w-full border rounded px-3 py-2
                       bg-white text-gray-900
                       dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
          <option value="">—</option>
          <option v-for="a in props.catalogos.abogados" :key="a.id" :value="a.id">{{ a.nombre }}</option>
        </select>
      </div>

      <!-- Etiquetas -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Etiquetas</label>
        <div class="flex flex-wrap gap-2 mt-1">
          <label v-for="e in props.catalogos.etiquetas" :key="e.id" class="flex items-center gap-1 text-gray-900 dark:text-gray-100">
            <input type="checkbox" :value="e.id" v-model="form.etiquetas" />
            {{ e.nombre }}
          </label>
        </div>
      </div>

      <!-- Fecha inicio -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de inicio</label>
        <input v-model="form.fecha_inicio" type="date"
               class="mt-1 w-full border rounded px-3 py-2
                      bg-white text-gray-900
                      dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700" />
      </div>

      <!-- Monto -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monto</label>
        <input v-model="form.monto" type="number" step="0.01"
               class="mt-1 w-full border rounded px-3 py-2
                      bg-white text-gray-900
                      dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700" />
      </div>

      <!-- Observaciones monto -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones monto</label>
        <textarea v-model="form.observaciones_monto"
                  class="mt-1 w-full border rounded px-3 py-2
                         bg-white text-gray-900
                         dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700"></textarea>
      </div>

      <!-- Resto de inputs (resolución, garantía, números, estatus) igual con bg/text en dark -->
      <!-- ... copia el mismo patrón con dark:bg-gray-900 dark:text-gray-100 ... -->

      <!-- Botones -->
      <div class="flex gap-2 justify-end">
        <Link href="/juicios" class="px-4 py-2 rounded border text-gray-800 dark:text-gray-100 dark:border-gray-600">
          Cancelar
        </Link>
        <button type="submit"
                class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700"
                :disabled="form.processing">
          Guardar
        </button>
      </div>
    </form>
  </div>
</template>
