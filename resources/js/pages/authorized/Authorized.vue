<template>

    <div class="p-6">

      <h1 class="text-2xl font-bold mb-4 text-neutral-600">Placas Autorizadas</h1>

        <GenericTable
                :data="list"
                :columns="columns"
                :per-page="10"
            >
                <template #actions="{ row }">
                    <button @click="edit(row)">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </button>
                </template>
        </GenericTable>

        <GenericModal
            v-if="modalHistoryVisible"
            size="lg"
            @close="modalHistoryVisible = false"
        >
            <template #header>
                Histórico de Eventos - Placa: {{ plateCurrent }}
            </template>

            <template #body>
                
                <GenericTable
                    :data="historyCurrent"
                    :columns="columnsHistory"
                    :per-page="10"
                ></GenericTable>


            </template>

            <template #footer>
                <button @click="modalHistoryVisible = false">
                    Fechar
                </button>

            </template>
        </GenericModal>

    </div>

</template>

<script>

import axios from 'axios';
import GenericTable from '@/components/GenericTable.vue';
import { getAllAuthorizedWithHistory } from '@/api/authorized';
import GenericModal from '@/components/GenericModal.vue';

export default {

  name: "Authorized",
  components: {
    GenericTable,
    GenericModal
  },
  data(){
    return {
        usuarios: [],
        columns: [
            {
                key: 'id',
                label: 'ID'
            },
            {
                key: 'plate_number',
                label: 'Placa'
            },
            {
                key: 'vehicle_status',
                label: 'Status do Veículo'
            },
            {
                key: 'history',
                label: 'Histórico'
            },
            {
                key: 'name',
                label: 'Associado'
            },
            {
                key: 'fixed_value',
                label: 'Original'
            },
            {
                key: 'agreement_value',
                label: 'Campanha'
            }
        ],
        columnsHistory: [
            {
                key: 'id',
                label: 'Código'
            },
            {
                key: 'status',
                label: 'Evento'
            },
            {
                key: 'created_at',
                label: 'Data'
            }
            
        ],
        historyCurrent: [],
        historyList: [],
        modalHistoryVisible: false,
        plateCurrent: null
    }       
  },
  computed: {

    list() {
        return this.usuarios.map(user => ({
            id: user.id,
            plate_number: user.plate_number,
            vehicle_status: user.vehicle_status,
            name: user.name,
            fixed_value: this.formatMoney(user.fixed_value),
            agreement_value: this.formatMoney(user.agreement_value),
            history: user.history.length
        }));
    }

  },
  methods: {

    edit(row) {

        this.historyCurrent = [];
        this.plateCurrent = row.plate_number;

        this.usuarios.find(user => {
            if(user.id === row.id){
                this.historyCurrent = user.history.map(event => ({
                    id: event.id,
                    status: event.status === 1 ? 'Boleto Aberto' : 'Boleto Pago',
                    created_at: new Date(event.created_at).toLocaleString()
                }));
            }
        });
      
        this.modalHistoryVisible = true;

    },
    formatMoney(value) {

        if (value === null || value === undefined) {
            return 'R$ 0,00'
        }

        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(Number(value))
    }

  },
  async beforeMount() {
  
    const response = await getAllAuthorizedWithHistory();
    this.usuarios = response.data;

  },

}

</script>