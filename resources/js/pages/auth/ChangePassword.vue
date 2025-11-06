<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import TopNavLayout from '@/layouts/TopNavLayout.vue'

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.put(route('password.update'), {
    onSuccess: () => {
      form.reset('current_password', 'password', 'password_confirmation')
    },
  })
}
</script>

<template>
  <TopNavLayout>
    <div class="max-w-lg mx-auto mt-10 bg-white rounded-2xl shadow-lg p-10">
      <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
          Cambiar contraseña
        </h1>

        <Link
          :href="route('dashboard')"
          class="text-sm text-indigo-600 hover:text-indigo-800"
        >
          Volver
        </Link>
      </div>

      <!-- Mensaje de éxito -->
      <div
        v-if="$page.props.flash?.success"
        class="mb-6 rounded-lg bg-green-50 text-green-800 px-4 py-3 text-sm"
      >
        {{ $page.props.flash.success }}
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Contraseña actual -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Contraseña actual
          </label>
          <input
            type="password"
            v-model="form.current_password"
            class="w-full h-12 px-4 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-base bg-gray-50"
            autocomplete="current-password"
          />
          <p v-if="form.errors.current_password" class="mt-2 text-sm text-red-600">
            {{ form.errors.current_password }}
          </p>
        </div>

        <!-- Nueva contraseña -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Nueva contraseña
          </label>
          <input
            type="password"
            v-model="form.password"
            class="w-full h-12 px-4 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-base bg-gray-50"
            autocomplete="new-password"
          />
          <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
            {{ form.errors.password }}
          </p>
        </div>

        <!-- Confirmación -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Confirmar nueva contraseña
          </label>
          <input
            type="password"
            v-model="form.password_confirmation"
            class="w-full h-12 px-4 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-base bg-gray-50"
            autocomplete="new-password"
          />
        </div>

        <div class="pt-6 flex justify-end">
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-indigo-600 text-white text-base font-semibold shadow hover:bg-indigo-700 transition disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing">Guardando...</span>
            <span v-else>Guardar contraseña</span>
          </button>
        </div>
      </form>
    </div>
  </TopNavLayout>
</template>
