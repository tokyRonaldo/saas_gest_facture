import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import Login from '@/views/Login.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Dashboard from '@/views/Dashboard.vue'
import Forbidden from '@/views/Forbidden.vue'
import ClientsList from '@/views/clients/ClientsList.vue'
import ClientForm from '@/views/clients/ClientForm.vue'
import ProductsList from '@/views/products/ProductsList.vue'
import ProductForm from '@/views/products/ProductForm.vue'
import InvoicesList from '@/views/invoices/InvoicesList.vue'
import InvoiceForm from '@/views/invoices/InvoiceForm.vue'
import InvoiceDetail from '@/views/invoices/InvoiceDetail.vue'
import UsersList from '@/views/users/UsersList.vue'
import UserForm from '@/views/users/UserForm.vue'
import Profile from '../views/Profile.vue'
import Settings from '@/views/Settings.vue'

const routes = [
  { path: '/login', name: 'login', component: Login },
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      { path: '', name: 'dashboard', component: Dashboard, meta: { permission: 'dashboard.view', breadcrumb: [{ label: 'Dashboard' }] } },
      { path: 'produits', name: 'produits', component: ProductsList, meta: { permission: 'products.view', breadcrumb: [{ label: 'Produits' }] } },
      { path: 'produits/nouveau', name: 'produit-create', component: ProductForm, meta: { permission: 'products.create', breadcrumb: [{ label: 'Produits', to: '/produits' }, { label: 'Nouveau produit' }]  } },
      { path: 'produits/:id/modifier', name: 'produit-edit', component: ProductForm, meta: { permission: 'products.edit', breadcrumb: [{ label: 'Produits', to: '/produits' }, { label: 'Modifier produit' }] } },
      { path: 'factures', name: 'factures', component: InvoicesList, meta: { permission: 'invoices.view', breadcrumb: [{ label: 'Factures' }] } },
      { path: 'factures/nouvelle', name: 'facture-create', component: InvoiceForm, meta: { permission: 'invoices.create', breadcrumb: [{ label: 'Factures', to: '/factures' }, { label: 'Nouvelle facture' }] } },
      { path: 'factures/:id', name: 'facture-detail', component: InvoiceDetail, meta: { permission: 'invoices.view', breadcrumb: 'Facture' } },
      { path: 'factures/:id/modifier', name: 'facture-edit', component: InvoiceForm, meta: { permission: 'invoices.edit', breadcrumb: [{ label: 'Factures', to: '/factures' }, { label: 'Modifier facture' }] } },
      { path: 'utilisateurs', name: 'utilisateurs', component: UsersList, meta: { permission: 'users.view', breadcrumb: [{ label: 'Utilisateurs' }] } },
      { path: 'utilisateurs/nouveau', name: 'utilisateur-create', component: UserForm, meta: { permission: 'users.create', breadcrumb: [{ label: 'Utilisateurs', to: '/utilisateurs' }, { label: 'Nouvel utilisateur' }] } },
      { path: 'utilisateurs/:id/modifier', name: 'utilisateur-edit', component: UserForm, meta: { permission: 'users.edit', breadcrumb: [{ label: 'Utilisateurs', to: '/utilisateurs' }, { label: 'Modifier utilisateur' }] } },
      {
        path: 'profil', name: 'profil', component: Profile,
        meta: { requiresAuth: true, breadcrumb: [{ label: 'Mon profil' }] },
      },
      {
        path: 'clients', name: 'clients', component: ClientsList,
        meta: { permission: 'clients.view', breadcrumb: [{ label: 'Clients' }] },
      },
      {
        path: 'clients/nouveau', name: 'client-create', component: ClientForm,
        meta: { permission: 'clients.create', breadcrumb: [{ label: 'Clients', to: '/clients' }, { label: 'Nouveau client' }] },
      },
      {
        path: 'clients/:id/modifier', name: 'client-edit', component: ClientForm,
        meta: { permission: 'clients.edit', breadcrumb: [{ label: 'Clients', to: '/clients' }, { label: 'Modifier client' }] },
      },

      {
        path: 'parametres', name: 'parametres', component: Settings,
        meta: { permission: 'settings.manage', breadcrumb: [{ label: 'Paramètres' }] },
      },
    ],
  },
  { path: '/403', name: 'forbidden', component: Forbidden },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  if (!auth.user) await auth.fetchUser()

  if (to.meta.requiresAuth && !auth.isAuthenticated) return '/login'
  if (to.meta.permission && !auth.can(to.meta.permission)) return '/403'
})

export default router