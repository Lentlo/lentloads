/**
 * Mobile Router Configuration
 * Uses hash-based routing for Capacitor compatibility.
 * This is a separate router for mobile apps only.
 */

import { createRouter, createWebHashHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

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
    meta: { title: 'Post Ad' }
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

  // 404 - catch all
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFound.vue'),
    meta: { title: 'Not Found' }
  }
]

// Always use hash history for mobile
const router = createRouter({
  history: createWebHashHistory(),
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
    ? `${to.meta.title} | Lentlo Ads`
    : 'Lentlo Ads'

  // On first load, try to fetch user if we have a token
  if (!authInitialized && authStore.token) {
    authInitialized = true
    try {
      await authStore.fetchUser()
    } catch (e) {
      // Token invalid - clear it
      localStorage.removeItem('token')
    }
  }
  authInitialized = true

  // Check if route requires auth
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  // Redirect authenticated users away from guest pages
  if (to.meta.guest && authStore.isAuthenticated) {
    return next({ name: 'dashboard' })
  }

  next()
})

export default router
