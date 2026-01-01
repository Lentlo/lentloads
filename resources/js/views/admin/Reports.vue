<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-6">
      <div class="flex flex-wrap gap-4">
        <select v-model="filters.status" @change="fetchReports" class="input w-auto">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="reviewed">Reviewed</option>
          <option value="resolved">Resolved</option>
          <option value="dismissed">Dismissed</option>
        </select>
        <select v-model="filters.type" @change="fetchReports" class="input w-auto">
          <option value="">All Types</option>
          <option value="listing">Listings</option>
          <option value="user">Users</option>
        </select>
        <select v-model="filters.reason" @change="fetchReports" class="input w-auto">
          <option value="">All Reasons</option>
          <option value="spam">Spam/Scam</option>
          <option value="prohibited">Prohibited Item</option>
          <option value="offensive">Offensive Content</option>
          <option value="harassment">Harassment</option>
          <option value="other">Other</option>
        </select>
      </div>
    </div>

    <!-- Reports List -->
    <div class="card overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Report</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Reason</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Reporter</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-if="loading">
            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
              Loading...
            </td>
          </tr>
          <tr v-else-if="!reports.length">
            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
              No reports found
            </td>
          </tr>
          <tr v-for="report in reports" :key="report.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded flex items-center justify-center"
                  :class="report.reportable_type === 'listing' ? 'bg-blue-100' : 'bg-purple-100'"
                >
                  <TagIcon v-if="report.reportable_type === 'listing'" class="w-5 h-5 text-blue-600" />
                  <UserIcon v-else class="w-5 h-5 text-purple-600" />
                </div>
                <div>
                  <p class="font-medium text-gray-900">
                    {{ report.reportable_type === 'listing' ? report.reportable?.title : report.reportable?.name }}
                  </p>
                  <p class="text-sm text-gray-500">{{ report.reportable_type }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              <span class="badge bg-red-100 text-red-700">{{ formatReason(report.reason) }}</span>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ report.reporter?.name }}</td>
            <td class="px-4 py-3">
              <span
                class="badge"
                :class="getStatusClass(report.status)"
              >
                {{ report.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ formatDate(report.created_at) }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <button
                  @click="viewReport(report)"
                  class="p-1 text-gray-600 hover:bg-gray-100 rounded"
                  title="View Details"
                >
                  <EyeIcon class="w-5 h-5" />
                </button>
                <button
                  v-if="report.status === 'pending'"
                  @click="resolveReport(report)"
                  class="p-1 text-green-600 hover:bg-green-50 rounded"
                  title="Resolve"
                >
                  <CheckIcon class="w-5 h-5" />
                </button>
                <button
                  v-if="report.status === 'pending'"
                  @click="dismissReport(report)"
                  class="p-1 text-red-600 hover:bg-red-50 rounded"
                  title="Dismiss"
                >
                  <XMarkIcon class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="p-4 border-t flex items-center justify-between">
        <p class="text-sm text-gray-500">
          Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, total) }} of {{ total }} reports
        </p>
        <div class="flex gap-2">
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="btn-secondary btn-sm"
          >
            Previous
          </button>
          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === lastPage"
            class="btn-secondary btn-sm"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Report Detail Modal -->
    <div v-if="selectedReport" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="selectedReport = null"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Report Details</h3>
          <button @click="selectedReport = null" class="p-1 hover:bg-gray-100 rounded">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <p class="text-sm text-gray-500">Reported Item</p>
            <p class="font-medium">
              {{ selectedReport.reportable_type === 'listing' ? selectedReport.reportable?.title : selectedReport.reportable?.name }}
            </p>
          </div>

          <div>
            <p class="text-sm text-gray-500">Reason</p>
            <span class="badge bg-red-100 text-red-700">{{ formatReason(selectedReport.reason) }}</span>
          </div>

          <div v-if="selectedReport.description">
            <p class="text-sm text-gray-500">Description</p>
            <p class="text-gray-700">{{ selectedReport.description }}</p>
          </div>

          <div>
            <p class="text-sm text-gray-500">Reported By</p>
            <p class="font-medium">{{ selectedReport.reporter?.name }}</p>
            <p class="text-sm text-gray-500">{{ selectedReport.reporter?.email }}</p>
          </div>

          <div>
            <p class="text-sm text-gray-500">Reported At</p>
            <p>{{ formatDateTime(selectedReport.created_at) }}</p>
          </div>

          <div v-if="selectedReport.status !== 'pending'" class="pt-4 border-t">
            <p class="text-sm text-gray-500">Resolution</p>
            <p class="font-medium">{{ selectedReport.status }}</p>
            <p v-if="selectedReport.admin_notes" class="text-sm text-gray-600 mt-1">
              {{ selectedReport.admin_notes }}
            </p>
          </div>
        </div>

        <div v-if="selectedReport.status === 'pending'" class="flex gap-2 mt-6 pt-4 border-t">
          <button @click="dismissReport(selectedReport); selectedReport = null" class="btn-secondary flex-1">
            Dismiss
          </button>
          <button @click="resolveReport(selectedReport); selectedReport = null" class="btn-primary flex-1">
            Take Action
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import dayjs from 'dayjs'
import {
  TagIcon,
  UserIcon,
  EyeIcon,
  CheckIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

const loading = ref(true)
const reports = ref([])
const currentPage = ref(1)
const lastPage = ref(1)
const perPage = ref(20)
const total = ref(0)
const selectedReport = ref(null)

const filters = reactive({
  status: '',
  type: '',
  reason: '',
})

const formatDate = (date) => dayjs(date).format('MMM D, YYYY')
const formatDateTime = (date) => dayjs(date).format('MMM D, YYYY h:mm A')

const formatReason = (reason) => {
  const reasons = {
    spam: 'Spam/Scam',
    prohibited: 'Prohibited Item',
    duplicate: 'Duplicate',
    offensive: 'Offensive Content',
    harassment: 'Harassment',
    scam: 'Scammer',
    fake_profile: 'Fake Profile',
    wrong_category: 'Wrong Category',
    other: 'Other',
  }
  return reasons[reason] || reason
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    reviewed: 'bg-blue-100 text-blue-800',
    resolved: 'bg-green-100 text-green-800',
    dismissed: 'bg-gray-100 text-gray-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const fetchReports = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/reports', {
      params: {
        page: currentPage.value,
        status: filters.status,
        type: filters.type,
        reason: filters.reason,
      }
    })
    reports.value = response.data.data
    currentPage.value = response.data.meta.current_page
    lastPage.value = response.data.meta.last_page
    perPage.value = response.data.meta.per_page
    total.value = response.data.meta.total
  } finally {
    loading.value = false
  }
}

const goToPage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    currentPage.value = page
    fetchReports()
  }
}

const viewReport = (report) => {
  selectedReport.value = report
}

const resolveReport = async (report) => {
  const action = prompt('What action did you take? (e.g., "Removed listing", "Banned user")')
  if (!action) return

  try {
    await api.post(`/admin/reports/${report.id}/resolve`, { notes: action })
    toast.success('Report resolved')
    fetchReports()
  } catch (error) {
    toast.error('Failed to resolve report')
  }
}

const dismissReport = async (report) => {
  if (!confirm('Dismiss this report? No action will be taken.')) return

  try {
    await api.post(`/admin/reports/${report.id}/dismiss`)
    toast.success('Report dismissed')
    fetchReports()
  } catch (error) {
    toast.error('Failed to dismiss report')
  }
}

onMounted(() => {
  fetchReports()
})
</script>
