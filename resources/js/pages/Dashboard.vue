<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/TopNavLayout.vue'

const props = defineProps<{
  metrics: { total:number; proceso:number; autorizados:number; concluidos:number }
  proximosVencimientos: Array<{
    id:number
    etapa:string
    fecha_vencimiento:string|null
    estatus:string
    etiqueta?: { id:number, nombre:string } | null
    juicio?: { id:number, nombre?:string|null } | null
  }>
  ultimosJuicios: Array<{
    id:number
    nombre?:string|null
    estatus:'juicio'|'autorizado'|'en_proceso'|'concluido'
    created_at:string
    fecha_inicio?:string|null
    monto?:string|null
    cliente?: { id:number, nombre:string } | null
  }>
}>()

const fmtDate = (v:any) => v ? new Intl.DateTimeFormat('es-MX').format(new Date(v)) : '—'
const fmtMoney = (v:any) => {
  if (v === null || v === undefined || v === '') return '—'
  const n = Number(v); if (Number.isNaN(n)) return String(v)
  return new Intl.NumberFormat('es-MX', { style:'currency', currency:'MXN' }).format(n)
}
</script>

<template>
    <AppLayout></AppLayout>

  <div class="p-6 space-y-6">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-800">Jurídico · Panel de control</h1>
      <div class="flex gap-2">
        <Link :href="route('juicios.create')"
              class="inline-flex items-center gap-2 px-3 py-2 rounded bg-indigo-600 text-white hover:bg-blue-700">
          ➕ Nuevo Juicio
        </Link>
        <Link :href="route('juicios.index')"
              class="inline-flex items-center gap-2 px-3 py-2 rounded border dark:border-indigo-600 dark:text-indigo-900">
          📄 Ver Juicios
        </Link>
      </div>
    </div>

    <!-- Tarjetas de métricas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="rounded-lg border bg-white p-4 dark:bg-gray-800 dark:border-gray-700">
        <div class="text-sm text-gray-500 dark:text-gray-400">Total de Juicios</div>
        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ props.metrics.total }}</div>
      </div>
      <div class="rounded-lg border bg-white p-4 dark:bg-gray-800 dark:border-gray-700">
        <div class="text-sm text-gray-500 dark:text-gray-400">En proceso</div>
        <div class="mt-2 text-3xl font-bold text-blue-600">{{ props.metrics.proceso }}</div>
      </div>
      <div class="rounded-lg border bg-white p-4 dark:bg-gray-800 dark:border-gray-700">
        <div class="text-sm text-gray-500 dark:text-gray-400">Autorizados</div>
        <div class="mt-2 text-3xl font-bold text-green-600">{{ props.metrics.autorizados }}</div>
      </div>
      <div class="rounded-lg border bg-white p-4 dark:bg-gray-800 dark:border-gray-700">
        <div class="text-sm text-gray-500 dark:text-gray-400">Concluidos</div>
        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ props.metrics.concluidos }}</div>
      </div>
    </div>

    <!-- Paneles: próximos vencimientos y últimos juicios -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <!-- Próximos vencimientos -->
      <div class="rounded-lg border bg-white dark:bg-gray-800 dark:border-gray-700">
        <div class="px-4 py-3 border-b dark:border-gray-700">
          <h2 class="font-semibold text-gray-900 dark:text-gray-100">Próximos vencimientos (15 días)</h2>
        </div>
        <div class="p-4">
          <table class="min-w-full text-sm">
            <thead class="text-left text-gray-100 dark:text-gray-400">
              <tr>
                <th class="py-2">Vence</th>
                <th class="py-2">Etapa</th>
                <th class="py-2">Etiqueta</th>
                <th class="py-2">Juicio</th>
                <th class="py-2"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="e in props.proximosVencimientos" :key="e.id" class="border-t dark:border-gray-700">
                <td class="py-2 font-medium text-gray-900 dark:text-gray-100">{{ fmtDate(e.fecha_vencimiento) }}</td>
                <td class="py-2 font-medium text-gray-900 dark:text-gray-100">{{ e.etapa }}</td>
                <td class="py-2 font-medium text-gray-900 dark:text-gray-100">{{ e.etiqueta?.nombre ?? '—' }}</td>
                <td class="py-2 font-medium text-gray-900 dark:text-gray-100">
                  <Link v-if="e.juicio" :href="route('etapas.index', e.juicio.id)"
                        class="text-indigo-600 hover:underline">
                    {{ e.juicio.nombre ?? ('#'+e.juicio.id) }}
                  </Link>
                </td>
                <td class="py-2 text-right">
                  <Link v-if="e.juicio" :href="route('etapas.index', e.juicio.id)"
                        class="inline-flex items-center px-2 py-1 rounded border text-xs dark:border-gray-600 dark:text-gray-100">
                    Ver
                  </Link>
                </td>
              </tr>
              <tr v-if="props.proximosVencimientos.length === 0">
                <td colspan="5" class="py-6 text-center text-gray-500">Sin vencimientos próximos</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Últimos juicios -->
      <div class="rounded-lg border bg-white dark:bg-gray-800 dark:border-gray-700">
        <div class="px-4 py-3 border-b dark:border-gray-700">
          <h2 class="font-semibold text-gray-900 dark:text-gray-100">Últimos juicios</h2>
        </div>
        <div class="p-4">
          <table class="min-w-full text-sm">
            <thead class="text-left text-gray-500 dark:text-gray-400">
              <tr>
                <th class="py-2">Creado</th>
                <th class="py-2">Nombre</th>
                <th class="py-2">Cliente</th>
                <th class="py-2">Monto</th>
                <th class="py-2 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="j in props.ultimosJuicios" :key="j.id" class="border-t dark:border-gray-700">
                <td class="py-2 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100">{{ fmtDate(j.created_at) }}</td>
                <td class="py-2">
                  <div class="font-medium text-gray-900 dark:text-gray-100">{{ j.nombre ?? ('#'+j.id) }}</div>
                  <div class="text-xs text-gray-500">{{ j.estatus }}</div>
                </td>
                <td class="py-2 font-medium text-gray-900 dark:text-gray-100">{{ j.cliente?.nombre ?? '—' }}</td>
                <td class="py-2 text-right font-medium text-gray-900 dark:text-gray-100">{{ fmtMoney(j.monto) }}</td>
                <td class="py-2 text-right">
                  <Link :href="route('etapas.index', j.id)"
                        class="inline-flex items-center px-2 py-1 rounded border text-xs dark:border-gray-600 dark:text-gray-100">
                    Etapas
                  </Link>
                </td>
              </tr>
              <tr v-if="props.ultimosJuicios.length === 0">
                <td colspan="5" class="py-6 text-center text-gray-500">Sin registros recientes</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
