<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Manage Categories</h1>
      <button @click="showModal(null)" class="btn-primary">
        <PlusIcon class="w-5 h-5 mr-2" />
        Add Category
      </button>
    </div>

    <!-- Categories Tree -->
    <div class="card">
      <div v-if="loading" class="p-8 text-center text-gray-500">
        Loading...
      </div>
      <div v-else-if="!categories.length" class="p-8 text-center text-gray-500">
        No categories yet. Create your first category!
      </div>
      <div v-else class="divide-y">
        <div
          v-for="category in categories"
          :key="category.id"
          class="p-4"
        >
          <!-- Parent Category -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <span class="text-2xl">{{ category.icon }}</span>
              <div>
                <p class="font-semibold text-gray-900">{{ category.name }}</p>
                <p class="text-sm text-gray-500">
                  {{ category.listings_count || 0 }} listings
                  <span v-if="category.children?.length">
                    • {{ category.children.length }} subcategories
                  </span>
                </p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="showModal(category)"
                class="p-1 text-blue-600 hover:bg-blue-50 rounded"
                title="Edit"
              >
                <PencilIcon class="w-5 h-5" />
              </button>
              <button
                @click="showModal(null, category.id)"
                class="p-1 text-green-600 hover:bg-green-50 rounded"
                title="Add Subcategory"
              >
                <PlusIcon class="w-5 h-5" />
              </button>
              <button
                @click="deleteCategory(category)"
                class="p-1 text-red-600 hover:bg-red-50 rounded"
                title="Delete"
              >
                <TrashIcon class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- Subcategories -->
          <div v-if="category.children?.length" class="mt-3 ml-10 space-y-2">
            <div
              v-for="child in category.children"
              :key="child.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex items-center gap-3">
                <span>{{ child.icon || '📁' }}</span>
                <div>
                  <p class="font-medium text-gray-900">{{ child.name }}</p>
                  <p class="text-sm text-gray-500">{{ child.listings_count || 0 }} listings</p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="showModal(child)"
                  class="p-1 text-blue-600 hover:bg-blue-100 rounded"
                  title="Edit"
                >
                  <PencilIcon class="w-4 h-4" />
                </button>
                <button
                  @click="deleteCategory(child)"
                  class="p-1 text-red-600 hover:bg-red-100 rounded"
                  title="Delete"
                >
                  <TrashIcon class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Modal -->
    <div v-if="showCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="showCategoryModal = false"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">
          {{ editingCategory?.id ? 'Edit Category' : 'Add Category' }}
        </h3>

        <form @submit.prevent="saveCategory" class="space-y-4">
          <div>
            <label class="label">Name</label>
            <input v-model="form.name" type="text" class="input" required />
          </div>

          <div>
            <label class="label">Slug</label>
            <input v-model="form.slug" type="text" class="input" placeholder="auto-generated if empty" />
          </div>

          <div>
            <label class="label">Icon (emoji)</label>
            <input v-model="form.icon" type="text" class="input" placeholder="📱" />
          </div>

          <div v-if="!editingCategory?.id">
            <label class="label">Parent Category</label>
            <select v-model="form.parent_id" class="input">
              <option :value="null">None (Top Level)</option>
              <option v-for="cat in parentCategories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="label">Description</label>
            <textarea v-model="form.description" rows="2" class="input"></textarea>
          </div>

          <div class="flex items-center gap-4">
            <label class="flex items-center gap-2">
              <input v-model="form.is_active" type="checkbox" class="rounded text-primary-600" />
              Active
            </label>
            <label class="flex items-center gap-2">
              <input v-model="form.is_featured" type="checkbox" class="rounded text-primary-600" />
              Featured
            </label>
          </div>

          <!-- Custom Fields -->
          <div class="border-t pt-4">
            <div class="flex items-center justify-between mb-3">
              <label class="label mb-0">Custom Fields</label>
              <button type="button" @click="addCustomField" class="text-sm text-primary-600 hover:underline">
                + Add Field
              </button>
            </div>
            <div v-for="(field, index) in form.custom_fields" :key="index" class="flex gap-2 mb-2">
              <input
                v-model="field.name"
                type="text"
                class="input flex-1"
                placeholder="Field name"
              />
              <select v-model="field.type" class="input w-32">
                <option value="text">Text</option>
                <option value="number">Number</option>
                <option value="select">Select</option>
                <option value="checkbox">Checkbox</option>
              </select>
              <button
                type="button"
                @click="removeCustomField(index)"
                class="p-2 text-red-600 hover:bg-red-50 rounded"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div class="flex gap-2 pt-4">
            <button type="button" @click="showCategoryModal = false" class="btn-secondary flex-1">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="btn-primary flex-1">
              {{ saving ? 'Saving...' : 'Save Category' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

const loading = ref(true)
const saving = ref(false)
const categories = ref([])
const showCategoryModal = ref(false)
const editingCategory = ref(null)

const form = reactive({
  name: '',
  slug: '',
  icon: '',
  parent_id: null,
  description: '',
  is_active: true,
  is_featured: false,
  custom_fields: [],
})

const parentCategories = computed(() => {
  return categories.value.filter(c => !c.parent_id)
})

const fetchCategories = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/categories')
    categories.value = response.data.data
  } finally {
    loading.value = false
  }
}

const showModal = (category = null, parentId = null) => {
  editingCategory.value = category

  if (category) {
    form.name = category.name
    form.slug = category.slug
    form.icon = category.icon || ''
    form.parent_id = category.parent_id
    form.description = category.description || ''
    form.is_active = category.is_active
    form.is_featured = category.is_featured
    form.custom_fields = category.custom_fields || []
  } else {
    form.name = ''
    form.slug = ''
    form.icon = ''
    form.parent_id = parentId
    form.description = ''
    form.is_active = true
    form.is_featured = false
    form.custom_fields = []
  }

  showCategoryModal.value = true
}

const addCustomField = () => {
  form.custom_fields.push({ name: '', type: 'text', options: [] })
}

const removeCustomField = (index) => {
  form.custom_fields.splice(index, 1)
}

const saveCategory = async () => {
  saving.value = true
  try {
    const payload = {
      name: form.name,
      slug: form.slug || null,
      icon: form.icon || null,
      parent_id: form.parent_id,
      description: form.description || null,
      is_active: form.is_active,
      is_featured: form.is_featured,
      custom_fields: form.custom_fields.filter(f => f.name),
    }

    if (editingCategory.value?.id) {
      await api.put(`/admin/categories/${editingCategory.value.id}`, payload)
      toast.success('Category updated')
    } else {
      await api.post('/admin/categories', payload)
      toast.success('Category created')
    }

    showCategoryModal.value = false
    fetchCategories()
  } catch (error) {
    toast.error('Failed to save category')
  } finally {
    saving.value = false
  }
}

const deleteCategory = async (category) => {
  const hasChildren = category.children?.length > 0
  const message = hasChildren
    ? `Delete "${category.name}" and all its subcategories? This cannot be undone.`
    : `Delete "${category.name}"? This cannot be undone.`

  if (!confirm(message)) return

  try {
    await api.delete(`/admin/categories/${category.id}`)
    toast.success('Category deleted')
    fetchCategories()
  } catch (error) {
    toast.error('Failed to delete category')
  }
}

onMounted(() => {
  fetchCategories()
})
</script>
