<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Settings</h1>

    <!-- Tabs -->
    <div class="flex gap-1 mb-6 border-b overflow-x-auto">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        class="px-4 py-2 font-medium border-b-2 -mb-px transition whitespace-nowrap"
        :class="activeTab === tab.id
          ? 'border-primary-600 text-primary-600'
          : 'border-transparent text-gray-500 hover:text-gray-700'"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- General Settings -->
    <div v-if="activeTab === 'general'" class="card p-6">
      <h2 class="text-lg font-semibold mb-4">General Settings</h2>
      <form @submit.prevent="saveSettings" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Site Name</label>
            <input v-model="settings.site_name" type="text" class="input" />
          </div>
          <div>
            <label class="label">Site Tagline</label>
            <input v-model="settings.site_tagline" type="text" class="input" placeholder="Short description" />
          </div>
        </div>
        <div>
          <label class="label">Site Description</label>
          <textarea v-model="settings.site_description" rows="2" class="input"></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Contact Email</label>
            <input v-model="settings.contact_email" type="email" class="input" />
          </div>
          <div>
            <label class="label">Contact Phone</label>
            <input v-model="settings.contact_phone" type="tel" class="input" />
          </div>
        </div>
        <div>
          <label class="label">Contact Address</label>
          <textarea v-model="settings.contact_address" rows="2" class="input"></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="label">Default Currency</label>
            <select v-model="settings.currency" class="input">
              <option value="INR">INR (₹)</option>
              <option value="USD">USD ($)</option>
              <option value="EUR">EUR (€)</option>
              <option value="GBP">GBP (£)</option>
              <option value="AED">AED (د.إ)</option>
            </select>
          </div>
          <div>
            <label class="label">Default Language</label>
            <select v-model="settings.language" class="input">
              <option value="en">English</option>
              <option value="hi">Hindi</option>
              <option value="ar">Arabic</option>
            </select>
          </div>
          <div>
            <label class="label">Timezone</label>
            <select v-model="settings.timezone" class="input">
              <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
              <option value="UTC">UTC</option>
              <option value="America/New_York">America/New_York (EST)</option>
              <option value="Europe/London">Europe/London (GMT)</option>
            </select>
          </div>
        </div>

        <!-- Maintenance Mode -->
        <div class="border-t pt-4 mt-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-red-600">Maintenance Mode</p>
              <p class="text-sm text-gray-500">Take the site offline for maintenance</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="settings.maintenance_mode" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-red-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
            </label>
          </div>
        </div>

        <button type="submit" :disabled="saving" class="btn-primary">
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </form>
    </div>

    <!-- Listing Settings -->
    <div v-if="activeTab === 'listings'" class="card p-6">
      <h2 class="text-lg font-semibold mb-4">Listing Settings</h2>
      <form @submit.prevent="saveSettings" class="space-y-4">
        <div class="flex items-center justify-between py-3 border-b">
          <div>
            <p class="font-medium">Require Approval</p>
            <p class="text-sm text-gray-500">New listings need admin approval before going live</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input v-model="settings.require_approval" type="checkbox" class="sr-only peer" />
            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
          </label>
        </div>

        <div class="flex items-center justify-between py-3 border-b">
          <div>
            <p class="font-medium">Auto-renew Listings</p>
            <p class="text-sm text-gray-500">Automatically renew listings before expiry</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input v-model="settings.auto_renew_listings" type="checkbox" class="sr-only peer" />
            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
          </label>
        </div>

        <div class="flex items-center justify-between py-3 border-b">
          <div>
            <p class="font-medium">Add Watermark to Images</p>
            <p class="text-sm text-gray-500">Add site watermark to listing images</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input v-model="settings.watermark_images" type="checkbox" class="sr-only peer" />
            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
          </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
          <div>
            <label class="label">Max Images per Listing</label>
            <input v-model.number="settings.max_images" type="number" min="1" max="20" class="input" />
          </div>
          <div>
            <label class="label">Max Image Size (MB)</label>
            <input v-model.number="settings.max_image_size" type="number" min="1" max="10" class="input" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Listing Expiry (days)</label>
            <input v-model.number="settings.listing_expiry_days" type="number" min="7" max="365" class="input" />
          </div>
          <div>
            <label class="label">Max Free Listings per User</label>
            <input v-model.number="settings.max_free_listings" type="number" min="0" class="input" />
            <p class="text-sm text-gray-500 mt-1">Set 0 for unlimited</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Min Title Length</label>
            <input v-model.number="settings.min_title_length" type="number" min="3" max="50" class="input" />
          </div>
          <div>
            <label class="label">Min Description Length</label>
            <input v-model.number="settings.min_description_length" type="number" min="10" max="500" class="input" />
          </div>
        </div>

        <button type="submit" :disabled="saving" class="btn-primary">
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </form>
    </div>

    <!-- Email Settings -->
    <div v-if="activeTab === 'email'" class="card p-6">
      <h2 class="text-lg font-semibold mb-4">Email Settings</h2>
      <form @submit.prevent="saveSettings" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">SMTP Host</label>
            <input v-model="settings.mail_host" type="text" class="input" placeholder="smtp.example.com" />
          </div>
          <div>
            <label class="label">SMTP Port</label>
            <input v-model="settings.mail_port" type="number" class="input" placeholder="587" />
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">SMTP Username</label>
            <input v-model="settings.mail_username" type="text" class="input" />
          </div>
          <div>
            <label class="label">SMTP Password</label>
            <input v-model="settings.mail_password" type="password" class="input" />
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">From Email</label>
            <input v-model="settings.mail_from_address" type="email" class="input" placeholder="noreply@example.com" />
          </div>
          <div>
            <label class="label">From Name</label>
            <input v-model="settings.mail_from_name" type="text" class="input" />
          </div>
        </div>
        <div>
          <label class="label">Encryption</label>
          <select v-model="settings.mail_encryption" class="input w-48">
            <option value="">None</option>
            <option value="tls">TLS</option>
            <option value="ssl">SSL</option>
          </select>
        </div>

        <div class="border-t pt-4 mt-4">
          <h3 class="font-medium mb-3">Email Notifications</h3>
          <div class="space-y-3">
            <label class="flex items-center gap-2">
              <input v-model="settings.email_on_new_listing" type="checkbox" class="rounded text-primary-600" />
              <span>Notify admin on new listing</span>
            </label>
            <label class="flex items-center gap-2">
              <input v-model="settings.email_on_new_user" type="checkbox" class="rounded text-primary-600" />
              <span>Notify admin on new user registration</span>
            </label>
            <label class="flex items-center gap-2">
              <input v-model="settings.email_on_report" type="checkbox" class="rounded text-primary-600" />
              <span>Notify admin on new report</span>
            </label>
          </div>
        </div>

        <button type="submit" :disabled="saving" class="btn-primary">
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </form>
    </div>

    <!-- SEO Settings -->
    <div v-if="activeTab === 'seo'" class="card p-6">
      <h2 class="text-lg font-semibold mb-4">SEO Settings</h2>
      <form @submit.prevent="saveSettings" class="space-y-4">
        <div>
          <label class="label">Default Meta Title</label>
          <input v-model="settings.meta_title" type="text" class="input" placeholder="Site name - Tagline" />
          <p class="text-sm text-gray-500 mt-1">Max 60 characters recommended</p>
        </div>
        <div>
          <label class="label">Default Meta Description</label>
          <textarea v-model="settings.meta_description" rows="3" class="input" placeholder="Brief description of your site"></textarea>
          <p class="text-sm text-gray-500 mt-1">Max 160 characters recommended</p>
        </div>
        <div>
          <label class="label">Meta Keywords</label>
          <input v-model="settings.meta_keywords" type="text" class="input" placeholder="classifieds, buy, sell, marketplace" />
        </div>

        <div class="border-t pt-4 mt-4">
          <h3 class="font-medium mb-3">Social Sharing</h3>
          <div>
            <label class="label">OG Image URL</label>
            <input v-model="settings.og_image" type="url" class="input" placeholder="https://example.com/og-image.jpg" />
            <p class="text-sm text-gray-500 mt-1">Default image for social sharing (1200x630 recommended)</p>
          </div>
        </div>

        <div class="border-t pt-4 mt-4">
          <h3 class="font-medium mb-3">Analytics</h3>
          <div>
            <label class="label">Google Analytics ID</label>
            <input v-model="settings.google_analytics_id" type="text" class="input" placeholder="G-XXXXXXXXXX" />
          </div>
          <div class="mt-4">
            <label class="label">Facebook Pixel ID</label>
            <input v-model="settings.facebook_pixel_id" type="text" class="input" placeholder="XXXXXXXXXXXXXXX" />
          </div>
        </div>

        <button type="submit" :disabled="saving" class="btn-primary">
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </form>
    </div>

    <!-- Social Links -->
    <div v-if="activeTab === 'social'" class="card p-6">
      <h2 class="text-lg font-semibold mb-4">Social Links</h2>
      <form @submit.prevent="saveSettings" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
              Facebook
            </label>
            <input v-model="settings.social_facebook" type="url" class="input" placeholder="https://facebook.com/yourpage" />
          </div>
          <div>
            <label class="label flex items-center gap-2">
              <svg class="w-5 h-5 text-pink-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
              Instagram
            </label>
            <input v-model="settings.social_instagram" type="url" class="input" placeholder="https://instagram.com/yourpage" />
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label flex items-center gap-2">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
              X (Twitter)
            </label>
            <input v-model="settings.social_twitter" type="url" class="input" placeholder="https://x.com/yourpage" />
          </div>
          <div>
            <label class="label flex items-center gap-2">
              <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
              YouTube
            </label>
            <input v-model="settings.social_youtube" type="url" class="input" placeholder="https://youtube.com/yourchannel" />
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-700" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
              LinkedIn
            </label>
            <input v-model="settings.social_linkedin" type="url" class="input" placeholder="https://linkedin.com/company/yourcompany" />
          </div>
          <div>
            <label class="label flex items-center gap-2">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              WhatsApp
            </label>
            <input v-model="settings.social_whatsapp" type="tel" class="input" placeholder="+1234567890" />
          </div>
        </div>

        <button type="submit" :disabled="saving" class="btn-primary">
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </form>
    </div>

    <!-- Pages -->
    <div v-if="activeTab === 'pages'" class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Static Pages</h2>
        <button @click="showPageModal(null)" class="btn-primary">
          <PlusIcon class="w-5 h-5 mr-2" />
          Add Page
        </button>
      </div>

      <div class="card overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Title</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Slug</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="page in pages" :key="page.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-900">{{ page.title }}</td>
              <td class="px-4 py-3 text-gray-600">/page/{{ page.slug }}</td>
              <td class="px-4 py-3">
                <span
                  class="badge"
                  :class="page.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ page.is_active ? 'Active' : 'Draft' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <button
                    @click="showPageModal(page)"
                    class="p-1 text-blue-600 hover:bg-blue-50 rounded"
                  >
                    <PencilIcon class="w-5 h-5" />
                  </button>
                  <button
                    @click="deletePage(page)"
                    class="p-1 text-red-600 hover:bg-red-50 rounded"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!pages.length">
              <td colspan="4" class="px-4 py-8 text-center text-gray-500">No pages created yet</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Banners -->
    <div v-if="activeTab === 'banners'" class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Banners</h2>
        <button @click="showBannerModal(null)" class="btn-primary">
          <PlusIcon class="w-5 h-5 mr-2" />
          Add Banner
        </button>
      </div>

      <!-- Banner Position Filter -->
      <div class="flex gap-2 flex-wrap">
        <button
          v-for="pos in bannerPositions"
          :key="pos.value"
          @click="bannerFilter = pos.value"
          class="px-3 py-1.5 text-sm rounded-full transition"
          :class="bannerFilter === pos.value
            ? 'bg-primary-600 text-white'
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
        >
          {{ pos.label }}
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="banner in filteredBanners"
          :key="banner.id"
          class="card overflow-hidden"
        >
          <img
            :src="banner.image_url"
            :alt="banner.title"
            class="w-full h-32 object-cover"
          />
          <div class="p-4">
            <div class="flex items-center justify-between mb-2">
              <h3 class="font-semibold truncate">{{ banner.title }}</h3>
              <span
                class="badge text-xs"
                :class="banner.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
              >
                {{ banner.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <p class="text-xs text-gray-500 mb-2">{{ getBannerPositionLabel(banner.position) }}</p>
            <p v-if="banner.link" class="text-xs text-gray-500 truncate mb-3">{{ banner.link }}</p>
            <div class="flex gap-2">
              <button @click="showBannerModal(banner)" class="btn-secondary btn-sm flex-1">
                Edit
              </button>
              <button @click="deleteBanner(banner)" class="btn-sm bg-red-100 text-red-700 flex-1">
                Delete
              </button>
            </div>
          </div>
        </div>
        <div v-if="!filteredBanners.length" class="col-span-full text-center py-8 text-gray-500">
          No banners found
        </div>
      </div>
    </div>

    <!-- Page Modal -->
    <div v-if="editingPage !== null" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="editingPage = null"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">{{ editingPage.id ? 'Edit Page' : 'Add Page' }}</h3>
        <form @submit.prevent="savePage" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Title</label>
              <input v-model="editingPage.title" type="text" class="input" required />
            </div>
            <div>
              <label class="label">Slug</label>
              <input v-model="editingPage.slug" type="text" class="input" placeholder="auto-generated" />
            </div>
          </div>
          <div>
            <label class="label">Content</label>
            <textarea v-model="editingPage.content" rows="12" class="input font-mono text-sm"></textarea>
            <p class="text-sm text-gray-500 mt-1">HTML is supported</p>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Meta Title</label>
              <input v-model="editingPage.meta_title" type="text" class="input" />
            </div>
            <div>
              <label class="label">Meta Description</label>
              <input v-model="editingPage.meta_description" type="text" class="input" />
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="editingPage.is_active" type="checkbox" class="rounded text-primary-600" />
            <label>Active</label>
          </div>
          <div class="flex gap-2">
            <button type="button" @click="editingPage = null" class="btn-secondary flex-1">Cancel</button>
            <button type="submit" :disabled="saving" class="btn-primary flex-1">
              {{ saving ? 'Saving...' : 'Save Page' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Banner Modal -->
    <div v-if="editingBanner !== null" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="editingBanner = null"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">{{ editingBanner.id ? 'Edit Banner' : 'Add Banner' }}</h3>
        <form @submit.prevent="saveBanner" class="space-y-4">
          <div>
            <label class="label">Title</label>
            <input v-model="editingBanner.title" type="text" class="input" required />
          </div>
          <div>
            <label class="label">Position</label>
            <select v-model="editingBanner.position" class="input" required>
              <option v-for="pos in bannerPositions.slice(1)" :key="pos.value" :value="pos.value">
                {{ pos.label }}
              </option>
            </select>
          </div>
          <div>
            <label class="label">Link (optional)</label>
            <input v-model="editingBanner.link" type="url" class="input" placeholder="https://" />
          </div>
          <div>
            <label class="label">Image</label>
            <input type="file" accept="image/*" @change="handleBannerImage" class="input" />
            <img v-if="editingBanner.image_url" :src="editingBanner.image_url" class="mt-2 h-24 rounded" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Order</label>
              <input v-model.number="editingBanner.order" type="number" class="input" />
            </div>
            <div class="flex items-center pt-6">
              <label class="flex items-center gap-2">
                <input v-model="editingBanner.is_active" type="checkbox" class="rounded text-primary-600" />
                Active
              </label>
            </div>
          </div>
          <div class="flex gap-2">
            <button type="button" @click="editingBanner = null" class="btn-secondary flex-1">Cancel</button>
            <button type="submit" :disabled="saving" class="btn-primary flex-1">
              {{ saving ? 'Saving...' : 'Save Banner' }}
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
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const tabs = [
  { id: 'general', label: 'General' },
  { id: 'listings', label: 'Listings' },
  { id: 'email', label: 'Email' },
  { id: 'seo', label: 'SEO' },
  { id: 'social', label: 'Social' },
  { id: 'pages', label: 'Pages' },
  { id: 'banners', label: 'Banners' },
]

const bannerPositions = [
  { value: '', label: 'All Positions' },
  { value: 'home_top', label: 'Home Top' },
  { value: 'home_middle', label: 'Home Middle' },
  { value: 'sidebar', label: 'Sidebar' },
  { value: 'listing_page', label: 'Listing Page' },
  { value: 'category_page', label: 'Category Page' },
]

const activeTab = ref('general')
const saving = ref(false)
const settings = reactive({
  // General
  site_name: '',
  site_tagline: '',
  site_description: '',
  contact_email: '',
  contact_phone: '',
  contact_address: '',
  currency: 'INR',
  language: 'en',
  timezone: 'Asia/Kolkata',
  maintenance_mode: false,
  // Listings
  require_approval: true,
  auto_renew_listings: false,
  watermark_images: false,
  max_images: 10,
  max_image_size: 5,
  listing_expiry_days: 30,
  max_free_listings: 5,
  min_title_length: 10,
  min_description_length: 50,
  // Email
  mail_host: '',
  mail_port: 587,
  mail_username: '',
  mail_password: '',
  mail_from_address: '',
  mail_from_name: '',
  mail_encryption: 'tls',
  email_on_new_listing: true,
  email_on_new_user: true,
  email_on_report: true,
  // SEO
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
  og_image: '',
  google_analytics_id: '',
  facebook_pixel_id: '',
  // Social
  social_facebook: '',
  social_instagram: '',
  social_twitter: '',
  social_youtube: '',
  social_linkedin: '',
  social_whatsapp: '',
})

const pages = ref([])
const banners = ref([])
const bannerFilter = ref('')
const editingPage = ref(null)
const editingBanner = ref(null)
const bannerImageFile = ref(null)

const filteredBanners = computed(() => {
  if (!bannerFilter.value) return banners.value
  return banners.value.filter(b => b.position === bannerFilter.value)
})

const getBannerPositionLabel = (position) => {
  const pos = bannerPositions.find(p => p.value === position)
  return pos ? pos.label : position
}

const fetchSettings = async () => {
  try {
    const response = await api.get('/admin/settings')
    const data = response.data.data || response.data

    // Handle both grouped and flat responses
    if (data && typeof data === 'object') {
      // If it's an array or grouped object, flatten it
      if (Array.isArray(data)) {
        data.forEach(item => {
          if (item.key && settings.hasOwnProperty(item.key)) {
            settings[item.key] = item.value
          }
        })
      } else {
        // Check if it's grouped by category
        Object.values(data).forEach(group => {
          if (Array.isArray(group)) {
            group.forEach(item => {
              if (item.key && settings.hasOwnProperty(item.key)) {
                settings[item.key] = item.value
              }
            })
          }
        })
        // Also try direct assignment
        Object.keys(data).forEach(key => {
          if (settings.hasOwnProperty(key)) {
            settings[key] = data[key]
          }
        })
      }
    }
  } catch (error) {
    console.error('Failed to fetch settings', error)
  }
}

const saveSettings = async () => {
  saving.value = true
  try {
    // Convert settings to array format expected by API
    const settingsArray = Object.entries(settings).map(([key, value]) => ({
      key,
      value: typeof value === 'boolean' ? (value ? '1' : '0') : value
    }))

    await api.put('/admin/settings', { settings: settingsArray })
    toast.success('Settings saved successfully')
  } catch (error) {
    toast.error('Failed to save settings')
    console.error(error)
  } finally {
    saving.value = false
  }
}

const fetchPages = async () => {
  try {
    const response = await api.get('/admin/settings/pages')
    pages.value = response.data.data || []
  } catch (error) {
    console.error('Failed to fetch pages', error)
  }
}

const showPageModal = (page) => {
  editingPage.value = page
    ? { ...page }
    : { title: '', slug: '', content: '', meta_title: '', meta_description: '', is_active: true }
}

const savePage = async () => {
  saving.value = true
  try {
    // Auto-generate slug if empty
    if (!editingPage.value.slug) {
      editingPage.value.slug = editingPage.value.title
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '')
    }

    if (editingPage.value.id) {
      await api.put(`/admin/settings/pages/${editingPage.value.id}`, editingPage.value)
    } else {
      await api.post('/admin/settings/pages', editingPage.value)
    }
    toast.success('Page saved')
    editingPage.value = null
    fetchPages()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save page')
  } finally {
    saving.value = false
  }
}

const deletePage = async (page) => {
  if (!confirm(`Delete "${page.title}"?`)) return
  try {
    await api.delete(`/admin/settings/pages/${page.id}`)
    toast.success('Page deleted')
    fetchPages()
  } catch (error) {
    toast.error('Failed to delete page')
  }
}

const fetchBanners = async () => {
  try {
    const response = await api.get('/admin/settings/banners')
    banners.value = response.data.data || []
  } catch (error) {
    console.error('Failed to fetch banners', error)
  }
}

const showBannerModal = (banner) => {
  editingBanner.value = banner
    ? { ...banner }
    : { title: '', link: '', position: 'home_top', order: 0, is_active: true }
  bannerImageFile.value = null
}

const handleBannerImage = (event) => {
  bannerImageFile.value = event.target.files[0]
  if (bannerImageFile.value) {
    editingBanner.value.image_url = URL.createObjectURL(bannerImageFile.value)
  }
}

const saveBanner = async () => {
  saving.value = true
  try {
    const formData = new FormData()
    formData.append('title', editingBanner.value.title)
    formData.append('link', editingBanner.value.link || '')
    formData.append('position', editingBanner.value.position)
    formData.append('order', editingBanner.value.order || 0)
    formData.append('is_active', editingBanner.value.is_active ? 1 : 0)
    if (bannerImageFile.value) {
      formData.append('image', bannerImageFile.value)
    }

    if (editingBanner.value.id) {
      formData.append('_method', 'PUT')
      await api.post(`/admin/settings/banners/${editingBanner.value.id}`, formData)
    } else {
      if (!bannerImageFile.value) {
        toast.error('Please select an image')
        saving.value = false
        return
      }
      await api.post('/admin/settings/banners', formData)
    }
    toast.success('Banner saved')
    editingBanner.value = null
    fetchBanners()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save banner')
  } finally {
    saving.value = false
  }
}

const deleteBanner = async (banner) => {
  if (!confirm(`Delete "${banner.title}"?`)) return
  try {
    await api.delete(`/admin/settings/banners/${banner.id}`)
    toast.success('Banner deleted')
    fetchBanners()
  } catch (error) {
    toast.error('Failed to delete banner')
  }
}

onMounted(() => {
  fetchSettings()
  fetchPages()
  fetchBanners()
})
</script>
