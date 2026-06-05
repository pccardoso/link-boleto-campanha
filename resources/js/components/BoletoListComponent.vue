<template>

  <div v-if="boletos.length" class="space-y-3">

    <div v-for="(boleto, index) in boletos" :key="index"
      class="bg- border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition duration-200">

      <div v-if="loading" class="space-y-3 mb-4">
        <div v-for="n in 1" :key="n" class="border border-neutral-300 rounded-xl p-4 animate-pulse">
          <div class="h-6 bg-gray-200 rounded w-1/2 mb-3"></div>
          <div class="h-4 bg-gray-200 rounded w-1/3"></div>
        </div>
      </div>
      <div v-else class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <div class="bg-green-50 border border-green-200 rounded-lg p-2">
            <span class="text-sm font-medium text-green-700 uppercase tracking-wide">
                Valor do Boleto
            </span>

            <p class="text-3xl font-bold text-green-800 mt-1">
                {{ formatCurrency(valorFinal) }}
            </p>
        </div>

        <div v-if="isExpired(boleto.data_vencimento_original)"
          class="mt-3 rounded-xl border border-red-300 bg-red-50 p-4 shadow-sm transition-all">

          <div class="w-full">
            <button @click="$emit('actionLinkSurvey', boleto)"
              class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold text-lg py-4 rounded-lg transition-all shadow-md px-4">
              Enviar Vídeo
            </button>
          </div>

        </div>
      </div>
    </div>

  </div>

  <!-- Estado vazio
  <div v-else class="text-center text-gray-500 py-10">
    <i class="fa-solid fa-barcode text-[30px]"></i> <br>
    <label for="emptyBolet">Nenhum boleto encontrado.</label>
  </div> -->
</template>

<script>

import { getVehicle } from '../api/vehicle';

export default {
  name: "BoletoListComponent",
  props: {
    boletos: {
      type: Array,
      required: true,
      default: () => []
    },
    state: {
      type: String,
      required: true
    }
  },
  emits: ['actionUpdateMaturity', 'actionLinkSurvey'],
  data() {
    return {
      copiedIndex: null,
      valorFinal: 0,
      loading: true
    }
  },
  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      }).format(parseFloat(value))
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('pt-BR', {
        timeZone: 'UTC'
      })
    },

    isExpired(date) {
      const vencimento = new Date(date)
      vencimento.setHours(23, 59, 59, 999)

      return vencimento < new Date()
    },

    daysLate(date) {
      const today = new Date()

      const onlyDate = date.split('T')[0] // 2026-03-10
      const [year, month, day] = onlyDate.split('-')

      const targetDate = new Date(year, month - 1, day)

      today.setHours(0, 0, 0, 0)

      const diffInMs = today - targetDate
      const diffInDays = diffInMs / (1000 * 60 * 60 * 24)

      return diffInDays > 0 ? Math.floor(diffInDays) : 0
    },

    async copyPix(value, index) {
      try {
        await navigator.clipboard.writeText(value)

        this.copiedIndex = index

        setTimeout(() => {
          this.copiedIndex = null
        }, 2000)

      } catch (error) {
        console.error('Erro ao copiar:', error)
        alert('Não foi possível copiar o Pix.')
      }
    },
    atualizarBoleto(bolet) {

      this.$emit('actionUpdateMaturity', bolet);

    },
    async getDataVehicle() {

      const placa =
        this.boletos?.[0]?.veiculos?.[0]?.placa ??
        this.boletos?.[0]?.veiculo?.[0]?.placa ??
        '';

      const chassi = this.boletos?.[0]?.veiculos?.[0]?.chassi ??
        this.boletos?.[0]?.veiculo?.[0]?.chassi ??
        '';

      console.log(placa, chassi, placa || chassi);

      if (placa || chassi) {
        const response = await getVehicle(placa || chassi, this.state);
        const responseData = response.data;

        const valorFixo = responseData[0].valor_fixo;

        this.valorFinal = (() => {
          switch (true) {
            case valorFixo <= 100:
              return 50;
            case valorFixo <= 150:
              return 75;
            case valorFixo <= 200:
              return 100;
            case valorFixo <= 300:
              return 150;
            case valorFixo <= 450:
              return 200;
            default:
              return 225;
          }
        })();

        this.loading = false;

      }

    }
  },
  async mounted() {

    await this.getDataVehicle();


  }
}
</script>