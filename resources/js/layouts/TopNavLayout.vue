<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

// estado del menú móvil (hamburguesa)
const mobileOpen = ref(false)

// items de navegación
const nav = [
  { label: 'Panel de inicio', to: '/dashboard' },
  { label: 'Juicios',   to: '/juicios' },
  { label: 'Clientes',  to: '/clientes' },
  { label: 'Abogados',  to: '/abogados' },
  { label: 'Revisiones',  to: '/revisiones' },
   { label: 'Calendario',  to: '/calendario' },
  { label: 'Usuarios',  to: '/seguridad/usuarios' },
  { label: 'Etapas',  to: '/etapas/panel' }

  // agrega más aquí…
]

// activa por ruta (si usas Ziggy, ver nota abajo)
const isActive = (path: string) => location.pathname.startsWith(path)

// clases utilitarias
const linkBase   = 'px-3 py-2 rounded-md text-sm font-medium'
const linkActive = 'bg-gray-900 text-white'
const linkIdle   = 'text-gray-700 hover:bg-gray-200'
</script>

<template>
  <!-- Header fijo -->
  <header class="fixed inset-x-0 top-0 z-50 bg-white/95 backdrop-blur border-b border-gray-200">
    <nav class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
      <!-- Brand -->
      <div class="flex items-center gap-2">
        <span class="text-2xl">⚖️</span>
        <span class="font-semibold">JURIDICO</span>
      </div>

      <!-- Links (desktop) -->
      <div class="hidden md:flex items-center gap-1">
        <template v-for="item in nav" :key="item.to">
          <Link :href="item.to"
                :class="[linkBase, isActive(item.to) ? linkActive : linkIdle]">
            {{ item.label }}
          </Link>
        </template>
      </div>

      <!-- Right zone (usuario / acciones) -->
      <div class="hidden md:flex items-center gap-3">
        <span class="text-sm text-gray-600 truncate max-w-[200px]">
          {{ ($page.props as any).auth?.user?.name ?? '' }}
        </span>
        <!-- Ejemplo botón acción -->
       <Link 
  :href="route('logout')" 
  method="post" 
  as="button"
  class="px-3 py-2 rounded-md text-sm bg-red-400 text-white hover:bg-red-600">
  Cerrar sesión
</Link>
      </div>

      <!-- Botón hamburguesa (móvil) -->
      <button
        class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded hover:bg-gray-100"
        @click="mobileOpen = !mobileOpen"
        aria-label="Abrir menú"
      >
        <!-- ícono simple -->
        <svg class="h-6 w-6 text-gray-800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
          <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </nav>

    <!-- Menú móvil desplegable -->
    <div
      class="md:hidden border-t border-gray-200 bg-white"
      v-show="mobileOpen"
    >
      <div class="px-3 py-2 space-y-1">
        <template v-for="item in nav" :key="item.to">
          <Link :href="item.to"
                class="block px-3 py-2 rounded-md text-base"
                :class="isActive(item.to) ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100'"
                @click="mobileOpen = false">
            {{ item.label }}
          </Link>
        </template>

        <div class="pt-2 mt-2 border-t border-gray-200">
          <div class="px-3 py-1 text-xs uppercase tracking-wide text-gray-500">Usuario</div>
          <div class="px-3 py-2 text-sm text-gray-700">
            {{ ($page.props as any).auth?.user?.name ?? '' }}
          </div>
          <Link :href="route?.('juicios.create') ?? '/juicios/create'"
                class="block mx-3 my-2 px-3 py-2 rounded-md text-sm text-center bg-indigo-600 text-white hover:bg-indigo-700"
                @click="mobileOpen = false">
            Nuevo juicio
          </Link>
        </div>
      </div>
    </div>
  </header>

  <!-- Contenido: padding-top para no quedar debajo del header fijo -->
  <div class="pt-14">
    <main class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8 py-6">
      <slot />
    </main>
  </div>
  
</template>
