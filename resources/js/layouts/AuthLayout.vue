<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Simple header with logo -->
    <header class="bg-white border-b">
      <div class="container-app py-4">
        <router-link to="/" class="inline-block">
          <h1 class="text-2xl font-bold text-primary-600">Lentloads</h1>
        </router-link>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center p-4">
      <slot />
    </main>

    <!-- Simple footer -->
    <footer class="py-4 text-center text-sm text-gray-500">
      <p>&copy; {{ new Date().getFullYear() }} Lentloads Marketplace. All rights reserved.</p>
    </footer>
  </div>
</template>
