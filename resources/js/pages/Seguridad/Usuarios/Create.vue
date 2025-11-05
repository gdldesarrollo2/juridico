<script setup lang="ts">
import TopNavLayout from '@/layouts/TopNavLayout.vue';
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps<{
  roles: string[]
}>()

const form = useForm({
  name: '',
  email: '',
  role: '',
})

const submit = () => {
  form.post(route('users.roles.store'))
}
</script>

<template>
    <TopNavLayout></TopNavLayout>
  <div class="min-h-screen bg-slate-50">
    <!-- Barra superior -->
    <header class="flex items-center justify-between px-6 pt-6 pb-4 max-w-5xl mx-auto">
      <div>
        <h1 class="text-3xl font-bold text-slate-900">Nuevo usuario</h1>
        <p class="mt-1 text-sm text-slate-500">
          Registra un usuario y asígnale un rol para acceder al sistema.
        </p>
      </div>

      <Link
        :href="route('users.roles.index')"
        class="text-sm font-medium text-slate-500 hover:text-slate-800 transition"
      >
        Volver
      </Link>
    </header>

    <!-- Card -->
    <main class="px-4 pb-12">
      <div
        class="max-w-5xl mx-auto bg-white shadow-sm rounded-2xl border border-slate-100 px-8 py-6 sm:px-10 sm:py-8"
      >
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Nombre -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              Nombre
            </label>
            <input
              v-model="form.name"
              type="text"
              class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                     placeholder-slate-400"
              placeholder="Nombre completo"
            />
            <p
              v-if="form.errors.name"
              class="mt-1 text-xs text-red-500"
            >
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              Email
            </label>
            <input
              v-model="form.email"
              type="email"
              class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                     placeholder-slate-400"
              placeholder="correo@ejemplo.com"
            />
            <p
              v-if="form.errors.email"
              class="mt-1 text-xs text-red-500"
            >
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Rol -->
          <div class="sm:w-1/2">
            <label class="block text-sm font-medium text-slate-700 mb-1">
              Rol
            </label>
            <select
              v-model="form.role"
              class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                     bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Seleccione…</option>
              <option
                v-for="r in props.roles"
                :key="r"
                :value="r"
              >
                {{ r }}
              </option>
            </select>
            <p
              v-if="form.errors.role"
              class="mt-1 text-xs text-red-500"
            >
              {{ form.errors.role }}
            </p>
          </div>

          <!-- Nota -->
          <div class="pt-1 text-xs text-slate-400">
            El usuario recibirá un correo para establecer su contraseña.
          </div>

          <!-- Botones -->
          <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 mt-4">
            <Link
              :href="route('users.roles.index')"
              class="text-sm font-medium text-slate-500 hover:text-slate-800 transition"
            >
              Cancelar
            </Link>

            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center justify-center rounded-lg px-5 py-2 text-sm font-semibold
                     text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60
                     disabled:cursor-not-allowed shadow-sm transition"
            >
              <span v-if="!form.processing">Guardar</span>
              <span v-else>Guardando…</span>
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>
