<script setup lang="ts">
import { onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue'

const props = defineProps<{
  revisiones: {
    data: Array<{
      id: number
      idempresa?: number|null
      empresa?: { id:number, razonsocial:string } | null
      autoridad?: { id:number, nombre:string } | null
      revision: string
      periodo_desde?: string|null
      periodo_hasta?: string|null
      rev_gabinete?: boolean
      rev_domiciliaria?: boolean
      rev_electronica?: boolean
      rev_secuencial?: boolean
      estatus: string
    }>
    links: Array<{ url:string|null, label:string, active:boolean }>
  }
}>()

onMounted(() => {
  //console.log('REVISIONS', props.revisiones.data)
})

const fmt = (d?: string|null) => d ? new Date(d).toLocaleDateString('es-MX') : '—'
</script>

<template>
 <TopNavLayout></TopNavLayout>
 <Link
        :href="route('revisiones.create')"
        class="rounded-md bg-indigo-600 py-1.5 px-3 border border-transparent text-center text-sm text-white transition-all shadow-sm hover:shadow focus:bg-indigo-700 focus:shadow-none active:bg-indigo-700 hover:bg-indigo-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none"
      >
        <span class="text-lg">＋</span>
        Nueva revisión
      </Link>
      
  <div class="bg-white rounded shadow overflow-x-auto">
    <br>
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left px-4 py-2">Tipo</th>
          <th class="text-left px-4 py-2">Sociedad</th>
          <th class="text-left px-4 py-2">Autoridad</th>
          <th class="text-left px-4 py-2">Nombre</th>
          <th class="text-left px-4 py-2">Periodo</th>
          <th class="text-left px-4 py-2">Estatus</th>
          <th class="text-right px-4 py-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="rev in props.revisiones.data" :key="rev.id" class="border-t">
          <!-- Tipo (primer flag que esté en true) -->
          <td class="px-4 py-2">
            <span v-if="rev.rev_gabinete">Gabinete</span>
            <span v-else-if="rev.rev_domiciliaria">Domiciliaria</span>
            <span v-else-if="rev.rev_electronica">Electrónica</span>
            <span v-else-if="rev.rev_secuencial">Secuencial</span>
            <span v-else>—</span>
          </td>

          <!-- Sociedad -->
          <td class="px-4 py-2">
            {{ rev.empresa?.razonsocial ?? '—' }}
          </td>

          <!-- Autoridad -->
          <td class="px-4 py-2">
            {{ rev.autoridad?.nombre ?? '—' }}
          </td>
           <td class="px-4 py-2">
            {{ rev.revision ?? '—' }}
          </td>

          <!-- Periodo -->
          <td class="px-4 py-2">
            {{ fmt(rev.periodo_desde) }} <span v-if="rev.periodo_desde || rev.periodo_hasta">al</span> {{ fmt(rev.periodo_hasta) }}
          </td>

          <!-- Estatus -->
          <td class="px-4 py-2">
            <span class="px-2 py-0.5 rounded text-xs"
              :class="{
                'bg-indigo-100 border border-indigo-300': rev.estatus==='en_juicio',
                'bg-yellow-100 border border-yellow-300': rev.estatus==='pendiente',
                'bg-blue-100 border border-blue-300': rev.estatus==='en_proceso',
                'bg-green-100 border border-green-300': rev.estatus==='autorizado',
                'bg-gray-200 border border-gray-300': rev.estatus==='concluido',
              }"
            >
              {{ rev.estatus }}
            </span>
          </td>

          <td class="px-4 py-2 text-right space-x-2">
              <!-- Etapas -->
 <!-- Etapas -->
  <Link
    :href="route('revisiones.etapas.index', { revision: rev.id })"
    class="inline-flex items-center px-3 py-1.5 rounded border text-indigo-700 border-indigo-300 hover:bg-indigo-50"
  >
    Etapas
  </Link>
            <Link :href="route('revisiones.edit', rev.id)" class="text-blue-600 hover:underline">Editar</Link>
            <Link as="button" method="delete" :href="route('revisiones.destroy', rev.id)" class="text-red-600 hover:underline">Eliminar</Link>
          </td>
        </tr>

        <tr v-if="props.revisiones.data.length === 0">
          <td colspan="6" class="px-4 py-6 text-center text-gray-500">Sin resultados</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
