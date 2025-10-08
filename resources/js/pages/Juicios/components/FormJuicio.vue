<script setup lang="ts">
import { reactive, computed } from 'vue'

// Props: el form (useForm) y catálogos
const props = defineProps<{
  form: any
  clientes: { id: number; nombre: string }[]
  autoridades: { id: number; nombre: string }[]
  abogados: { id: number; nombre: string }[]
  etiquetas: { id: number; nombre: string }[]
}>()

// Emit para que el padre controle el envío
const emit = defineEmits<{
  (e: 'submit'): void
}>()

// ====== Lógica para periodos ======
type Periodo = { anio:number; meses:number[] }
const MESES = [
  { v:1, label:'Enero' },{ v:2,label:'Febrero' },{ v:3,label:'Marzo' },
  { v:4,label:'Abril' },{ v:5,label:'Mayo' },{ v:6,label:'Junio' },
  { v:7,label:'Julio' },{ v:8,label:'Agosto' },{ v:9,label:'Septiembre' },
  { v:10,label:'Octubre' },{ v:11,label:'Noviembre' },{ v:12,label:'Diciembre' },
]

const aniosDisponibles = computed(() => {
  const y = new Date().getFullYear()
  const arr:number[] = []
  for (let i = y+1; i >= y-20; i--) arr.push(i)
  return arr
})

const nuevoAnio = reactive<{ value:number|'' }>({ value:'' })
const aniosElegidos = computed(() => props.form.periodos.map((p:Periodo)=>p.anio))

function addAnio() {
  if (!nuevoAnio.value) return
  const val = Number(nuevoAnio.value)
  if (aniosElegidos.value.includes(val)) return
  props.form.periodos.push({ anio: val, meses: [] })
  nuevoAnio.value = ''
}
function rmAnio(i:number) {
  props.form.periodos.splice(i,1)
}
function toggleMes(i:number, mes:number) {
  const arr = props.form.periodos[i].meses
  const idx = arr.indexOf(mes)
  if (idx === -1) arr.push(mes); else arr.splice(idx,1)
  arr.sort((a,b)=>a-b)
}

const err = (key:string) => (props.form.errors as Record<string,string>)[key] ?? ''

function onSubmit() {
  emit('submit')
}
</script>

<template>
  <form @submit.prevent="onSubmit" class="space-y-6">
    <!-- Nombre -->
    <div>
      <label class="block text-sm font-medium">Nombre</label>
      <input v-model="form.nombre" type="text" class="border rounded px-2 py-1 w-full" />
      <p v-if="form.errors.nombre" class="text-red-600 text-xs">{{ form.errors.nombre }}</p>
    </div>

    <!-- Tipo -->
    <div>
      <label class="block text-sm font-medium">Tipo</label>
      <select v-model="form.tipo" class="border rounded px-2 py-1 w-full">
        <option value="nulidad">Nulidad</option>
        <option value="revocacion">Revocación</option>
      </select>
    </div>

    <!-- Cliente -->
    <div>
      <label class="block text-sm font-medium">Cliente</label>
      <select v-model="form.cliente_id" class="border rounded px-2 py-1 w-full">
        <option value="">—</option>
        <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre }}</option>
      </select>
      <p v-if="form.errors.cliente_id" class="text-red-600 text-xs">{{ form.errors.cliente_id }}</p>
    </div>

    <!-- Autoridad -->
    <div>
      <label class="block text-sm font-medium">Autoridad</label>
      <select v-model="form.autoridad_id" class="border rounded px-2 py-1 w-full">
        <option value="">—</option>
        <option v-for="a in autoridades" :key="a.id" :value="a.id">{{ a.nombre }}</option>
      </select>
    </div>

    <!-- Fecha inicio -->
    <div>
      <label class="block text-sm font-medium">Fecha de inicio</label>
      <input type="date" v-model="form.fecha_inicio" class="border rounded px-2 py-1 w-full" />
    </div>

    <!-- Monto -->
    <div>
      <label class="block text-sm font-medium">Monto</label>
      <input type="number" v-model="form.monto" class="border rounded px-2 py-1 w-full" />
    </div>

    <!-- Observaciones monto -->
    <div>
      <label class="block text-sm font-medium">Observaciones monto</label>
      <textarea v-model="form.observaciones_monto" class="border rounded px-2 py-1 w-full"></textarea>
    </div>

    <!-- Resolución impugnada -->
    <div>
      <label class="block text-sm font-medium">Resolución impugnada</label>
      <input v-model="form.resolucion_impugnada" class="border rounded px-2 py-1 w-full" />
    </div>

    <!-- Garantía -->
    <div>
      <label class="block text-sm font-medium">Garantía</label>
      <input v-model="form.garantia" class="border rounded px-2 py-1 w-full" />
    </div>

    <!-- Número Juicio -->
    <div>
      <label class="block text-sm font-medium">Número Juicio</label>
      <input v-model="form.numero_juicio" class="border rounded px-2 py-1 w-full" />
    </div>

    <!-- Número Expediente -->
    <div>
      <label class="block text-sm font-medium">Número Expediente</label>
      <input v-model="form.numero_expediente" class="border rounded px-2 py-1 w-full" />
    </div>

    <!-- Estatus -->
    <div>
      <label class="block text-sm font-medium">Estatus</label>
      <select v-model="form.estatus" class="border rounded px-2 py-1 w-full">
        <option value="juicio">Juicio</option>
        <option value="autorizado">Autorizado</option>
        <option value="en_proceso">En proceso</option>
        <option value="concluido">Concluido</option>
      </select>
    </div>

    <!-- Abogado -->
    <div>
      <label class="block text-sm font-medium">Abogado</label>
      <select v-model="form.abogado_id" class="border rounded px-2 py-1 w-full">
        <option value="">—</option>
        <option v-for="ab in abogados" :key="ab.id" :value="ab.id">{{ ab.nombre }}</option>
      </select>
    </div>

    <!-- Etiquetas -->
    <div>
      <label class="block text-sm font-medium">Etiquetas</label>
      <select v-model="form.etiquetas" multiple class="border rounded px-2 py-1 w-full">
        <option v-for="e in etiquetas" :key="e.id" :value="e.id">{{ e.nombre }}</option>
      </select>
    </div>

    <!-- ====== BLOQUE PERIODOS ====== -->
    <div class="space-y-4">
      <h3 class="font-semibold">Periodos</h3>

      <div class="flex items-end gap-3">
        <div class="grow">
          <label>Año</label>
          <select v-model="nuevoAnio.value" class="border rounded px-2 py-1 w-full">
            <option value="">Seleccione…</option>
            <option v-for="y in aniosDisponibles" :key="y" :value="y" :disabled="aniosElegidos.includes(y)">
              {{ y }}
            </option>
          </select>
        </div>
        <button type="button" @click="addAnio" class="px-3 py-1 rounded bg-emerald-600 text-white">Añadir año</button>
      </div>

      <p v-if="err('periodos')" class="text-red-600 text-xs">{{ err('periodos') }}</p>

      <div v-for="(p,i) in form.periodos" :key="p.anio" class="border rounded p-4 space-y-3">
        <div class="flex justify-between items-center">
          <span class="font-semibold">Meses de {{ p.anio }}</span>
          <button type="button" class="text-red-600 text-sm" @click="rmAnio(i)">Quitar</button>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
          <label v-for="m in MESES" :key="m.v" class="inline-flex items-center gap-2 border rounded px-2 py-1">
            <input type="checkbox" :checked="p.meses.includes(m.v)" @change="toggleMes(i, m.v)" />
            {{ m.label }}
          </label>
        </div>
        <p v-if="err(`periodos.${i}.meses`)" class="text-red-600 text-xs">
          {{ err(`periodos.${i}.meses`) }}
        </p>
      </div>
    </div>
    <!-- ============================= -->

    <div class="flex justify-end">
      <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
        Guardar
      </button>
    </div>
  </form>
</template>
