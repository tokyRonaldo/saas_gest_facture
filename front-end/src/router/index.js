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

const routes = [
  { path: '/login', name: 'login', component: Login },
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      { path: '', name: 'dashboard', component: Dashboard, meta: { permission: 'dashboard.view', breadcrumb: 'Dashboard' } },
      { path: 'clients', name: 'clients', component: ClientsList, meta: { permission: 'clients.view', breadcrumb: 'Clients' } },
      { path: 'clients/nouveau', name: 'client-create', component: ClientForm, meta: { permission: 'clients.create', breadcrumb: 'Nouveau client' } },
      { path: 'clients/:id/modifier', name: 'client-edit', component: ClientForm, meta: { permission: 'clients.edit', breadcrumb: 'Modifier client' } },
      { path: 'produits', name: 'produits', component: ProductsList, meta: { permission: 'products.view', breadcrumb: 'Produits' } },
      { path: 'produits/nouveau', name: 'produit-create', component: ProductForm, meta: { permission: 'products.create', breadcrumb: 'Nouveau produit' } },
      { path: 'produits/:id/modifier', name: 'produit-edit', component: ProductForm, meta: { permission: 'products.edit', breadcrumb: 'Modifier produit' } },
      { path: 'factures', name: 'factures', component: InvoicesList, meta: { permission: 'invoices.view', breadcrumb: 'Factures' } },
      { path: 'factures/nouvelle', name: 'facture-create', component: InvoiceForm, meta: { permission: 'invoices.create', breadcrumb: 'Nouvelle facture' } },
      { path: 'factures/:id', name: 'facture-detail', component: InvoiceDetail, meta: { permission: 'invoices.view', breadcrumb: 'Facture' } },
      { path: 'factures/:id/modifier', name: 'facture-edit', component: InvoiceForm, meta: { permission: 'invoices.edit', breadcrumb: 'Modifier facture' } },
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