<script setup>
import { ref, onMounted, computed } from 'vue'
import { Line, Pie } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, ArcElement } from 'chart.js'
import { dashboardApi } from '@/api/dashboard'
import { useAuthStore } from '@/stores/auth'
import StatCard from '@/components/StatCard.vue'

//on l'utiliser antérieurement pas visible //Line ou Pie utilise par exemple LineElement pour déssiner les lignes entre chaque point
ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, ArcElement)

const auth = useAuthStore()
const isAdmin = computed(() => auth.user?.roles?.includes('admin'))

const data = ref(null)
const loading = ref(true)

function formatMontant(m) {
  return new Intl.NumberFormat('fr-FR').format(m) + ' Ar'
}

const lineChartData = computed(() => ({
  labels: data.value?.ca_mensuel.map(m => m.mois) || [],
  datasets: [{
    label: 'Chiffre d\'affaires',
    data: data.value?.ca_mensuel.map(m => m.total) || [],
    borderColor: '#2563eb',
    backgroundColor: 'rgba(37, 99, 235, 0.1)',
    tension: 0.3,
    fill: true,
  }],
}))

const statutColors = { envoyee: '#3b82f6', payee: '#22c55e', annulee: '#ef4444' }
const statutLabels = { envoyee: 'Envoyées', payee: 'Payées', annulee: 'Annulées' }

const pieChartData = computed(() => ({
  labels: data.value?.factures_par_statut.map(s => statutLabels[s.statut]) || [],
  datasets: [{
    data: data.value?.factures_par_statut.map(s => s.total) || [],
    backgroundColor: data.value?.factures_par_statut.map(s => statutColors[s.statut]) || [],
  }],
}))

async function fetchDashboard() {
  loading.value = true
  const { data: res } = isAdmin.value ? await dashboardApi.admin() : await dashboardApi.simple()
  data.value = res
  loading.value = false
}

onMounted(fetchDashboard)
</script>

<template>
  <div v-if="loading" class="text-center py-12 text-slate-400">Chargement du dashboard...</div>

  <div v-else>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
      <p class="text-sm text-slate-500">Résumé général de ton activité</p>
    </div>

    <!-- Vue ADMIN -->
    <template v-if="isAdmin">
      <div class="grid grid-cols-4 gap-4 mb-6">
        <StatCard label="Chiffre d'affaires" :value="formatMontant(data.chiffre_affaires)"
          icon="$" icon-bg="bg-green-100" icon-color="text-green-700" :evolution="data.chiffre_affaires_evolution" />
        <StatCard label="Factures émises" :value="data.factures_emises"
          icon="📄" icon-bg="bg-blue-100" icon-color="text-blue-700" :evolution="data.factures_emises_evolution" />
        <StatCard label="Paiements pendants" :value="formatMontant(data.paiements_pendants)"
          icon="⏱️" icon-bg="bg-orange-100" icon-color="text-orange-700" :evolution="null" />
        <StatCard label="Clients actifs" :value="data.clients_actifs"
          icon="👥" icon-bg="bg-purple-100" icon-color="text-purple-700" :evolution="null" />
      </div>

      <div class="grid grid-cols-3 gap-4">
        <div class="col-span-2 bg-white rounded-xl border border-slate-200 p-5">
          <h3 class="font-medium text-slate-900 mb-4">Revenus mensuels</h3>
          <Line :data="lineChartData" :options="{ responsive: true, plugins: { legend: { display: false } } }" />
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
          <h3 class="font-medium text-slate-900 mb-4">Factures par statut</h3>
          <Pie :data="pieChartData" :options="{ responsive: true }" />
        </div>
      </div>

      <div class="bg-white rounded-xl border border-slate-200 p-5 mt-4">
        <h3 class="font-medium text-slate-900 mb-4">Top clients</h3>
        <div v-for="(c, i) in data.top_clients" :key="i" class="flex justify-between py-2 border-b border-slate-100 last:border-0">
          <span class="text-sm text-slate-700">{{ c.nom }}</span>
          <span class="text-sm font-medium text-slate-900">{{ formatMontant(c.total) }}</span>
        </div>
      </div>
    </template>

    <!-- Vue USER / COMMERCIAL -->
    <template v-else>
      <div class="grid grid-cols-4 gap-4 mb-6">
        <StatCard label="Mes factures" :value="data.nombre_factures" icon="📄" icon-bg="bg-blue-100" icon-color="text-blue-700" />
        <StatCard label="Montant facturé" :value="formatMontant(data.montant_facture)" icon="$" icon-bg="bg-slate-100" icon-color="text-slate-700" />
        <StatCard label="Montant payé" :value="formatMontant(data.montant_paye)" icon="✓" icon-bg="bg-green-100" icon-color="text-green-700" />
        <StatCard label="Reste à percevoir" :value="formatMontant(data.montant_restant)" icon="⏱️" icon-bg="bg-orange-100" icon-color="text-orange-700" />
      </div>

      <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="font-medium text-slate-900 mb-4">Dernières factures</h3>
        <div v-for="f in data.dernieres_factures" :key="f.id" class="flex justify-between py-2 border-b border-slate-100 last:border-0 text-sm">
          <span>{{ f.numero || 'Brouillon' }} — {{ f.client.nom }}</span>
          <span class="font-medium">{{ formatMontant(f.total_ttc) }}</span>
        </div>
      </div>
    </template>
  </div>
</template>