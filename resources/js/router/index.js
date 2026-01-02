import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Layouts
const DefaultLayout = () => import('@/layouts/DefaultLayout.vue')
const AuthLayout = () => import('@/layouts/AuthLayout.vue')
const AdminLayout = () => import('@/layouts/AdminLayout.vue')

// Public Pages
const Home = () => import('@/views/Home.vue')
const Search = () => import('@/views/Search.vue')
const Categories = () => import('@/views/Categories.vue')
const CategoryListings = () => import('@/views/CategoryListings.vue')
const ListingDetail = () => import('@/views/ListingDetail.vue')
const UserProfile = () => import('@/views/UserProfile.vue')
const StaticPage = () => import('@/views/StaticPage.vue')

// Auth Pages
const Login = () => import('@/views/auth/Login.vue')
const Register = () => import('@/views/auth/Register.vue')
const ForgotPassword = () => import('@/views/auth/ForgotPassword.vue')
const ResetPassword = () => import('@/views/auth/ResetPassword.vue')

// User Dashboard Pages
const Dashboard = () => import('@/views/dashboard/Dashboard.vue')
const MyListings = () => import('@/views/dashboard/MyListings.vue')
const CreateListing = () => import('@/views/dashboard/CreateListing.vue')
const EditListing = () => import('@/views/dashboard/EditListing.vue')
const Favorites = () => import('@/views/dashboard/Favorites.vue')
const Messages = () => import('@/views/dashboard/Messages.vue')
const Conversation = () => import('@/views/dashboard/Conversation.vue')
const Settings = () => import('@/views/dashboard/Settings.vue')
const MyReviews = () => import('@/views/dashboard/MyReviews.vue')
const SavedSearches = () => import('@/views/dashboard/SavedSearches.vue')
const Notifications = () => import('@/views/dashboard/Notifications.vue')

// Admin Pages
const AdminDashboard = () => import('@/views/admin/Dashboard.vue')
const AdminUsers = () => import('@/views/admin/Users.vue')
const AdminListings = () => import('@/views/admin/Listings.vue')
const AdminCategories = () => import('@/views/admin/Categories.vue')
const AdminReports = () => import('@/views/admin/Reports.vue')
const AdminSettings = () => import('@/views/admin/Settings.vue')
const AdminConversations = () => import('@/views/admin/Conversations.vue')
const AdminContactViews = () => import('@/views/admin/ContactViews.vue')

const routes = [
  // Public routes
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: { title: 'Home' }
  },
  {
    path: '/search',
    name: 'search',
    component: Search,
    meta: { title: 'Search' }
  },
  {
    path: '/categories',
    name: 'categories',
    component: Categories,
    meta: { title: 'Categories' }
  },
  {
    path: '/category/:slug',
    name: 'category',
    component: CategoryListings,
    meta: { title: 'Category' }
  },
  {
    path: '/listing/:slug',
    name: 'listing',
    component: ListingDetail,
    meta: { title: 'Listing' }
  },
  {
    path: '/user/:id',
    name: 'user-profile',
    component: UserProfile,
    meta: { title: 'User Profile' }
  },
  {
    path: '/page/:slug',
    name: 'static-page',
    component: StaticPage,
    meta: { title: 'Page' }
  },

  // Auth routes
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { title: 'Login', guest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { title: 'Register', guest: true }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPassword,
    meta: { title: 'Forgot Password', guest: true }
  },
  {
    path: '/reset-password/:token',
    name: 'reset-password',
    component: ResetPassword,
    meta: { title: 'Reset Password', guest: true }
  },

  // Dashboard routes (protected)
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { title: 'Dashboard', requiresAuth: true }
  },
  {
    path: '/my-listings',
    name: 'my-listings',
    component: MyListings,
    meta: { title: 'My Listings', requiresAuth: true }
  },
  {
    path: '/sell',
    name: 'create-listing',
    component: CreateListing,
    meta: { title: 'Post Ad' } // No auth required - modal handles authentication
  },
  {
    path: '/listing/:id/edit',
    name: 'edit-listing',
    component: EditListing,
    meta: { title: 'Edit Listing', requiresAuth: true }
  },
  {
    path: '/favorites',
    name: 'favorites',
    component: Favorites,
    meta: { title: 'Favorites', requiresAuth: true }
  },
  {
    path: '/messages',
    name: 'messages',
    component: Messages,
    meta: { title: 'Messages', requiresAuth: true }
  },
  {
    path: '/messages/:uuid',
    name: 'conversation',
    component: Conversation,
    meta: { title: 'Conversation', requiresAuth: true }
  },
  {
    path: '/settings',
    name: 'settings',
    component: Settings,
    meta: { title: 'Settings', requiresAuth: true }
  },
  {
    path: '/my-reviews',
    name: 'my-reviews',
    component: MyReviews,
    meta: { title: 'My Reviews', requiresAuth: true }
  },
  {
    path: '/saved-searches',
    name: 'saved-searches',
    component: SavedSearches,
    meta: { title: 'Saved Searches', requiresAuth: true }
  },
  {
    path: '/notifications',
    name: 'notifications',
    component: Notifications,
    meta: { title: 'Notifications', requiresAuth: true }
  },

  // Admin routes with layout
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: '',
        name: 'admin-dashboard',
        component: AdminDashboard,
        meta: { title: 'Admin Dashboard' }
      },
      {
        path: 'users',
        name: 'admin-users',
        component: AdminUsers,
        meta: { title: 'Manage Users' }
      },
      {
        path: 'listings',
        name: 'admin-listings',
        component: AdminListings,
        meta: { title: 'Manage Listings' }
      },
      {
        path: 'categories',
        name: 'admin-categories',
        component: AdminCategories,
        meta: { title: 'Manage Categories' }
      },
      {
        path: 'reports',
        name: 'admin-reports',
        component: AdminReports,
        meta: { title: 'Reports' }
      },
      {
        path: 'settings',
        name: 'admin-settings',
        component: AdminSettings,
        meta: { title: 'Settings' }
      },
      {
        path: 'conversations',
        name: 'admin-conversations',
        component: AdminConversations,
        meta: { title: 'Conversations' }
      },
      {
        path: 'contact-views',
        name: 'admin-contact-views',
        component: AdminContactViews,
        meta: { title: 'Contact Views' }
      },
    ]
  },

  // 404
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFound.vue'),
    meta: { title: 'Not Found' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }
    return { top: 0 }
  }
})

// Navigation guards
let authInitialized = false

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Set page title
  document.title = to.meta.title
    ? `${to.meta.title} | Lentloads Marketplace`
    : 'Lentloads Marketplace'

  // On first load, try to fetch user if we have a token
  if (!authInitialized && authStore.token) {
    authInitialized = true
    try {
      await authStore.fetchUser()
    } catch (e) {
      // Token invalid, user will be redirected to login if needed
    }
  }
  authInitialized = true

  // Check if route requires auth
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  // Check if route requires admin
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    return next({ name: 'home' })
  }

  // Redirect authenticated users away from guest pages
  if (to.meta.guest && authStore.isAuthenticated) {
    return next({ name: 'dashboard' })
  }

  next()
})

export default router
