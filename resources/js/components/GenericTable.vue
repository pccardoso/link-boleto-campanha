<template>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <!-- HEADER -->
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">

            <input
                v-model="search"
                type="text"
                placeholder="Pesquisar..."
                class="px-3 py-2 border rounded-lg w-72"
            >

            <span class="text-sm text-gray-500">
                {{ filteredData.length }} registros
            </span>

        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">

            <table class="w-full">

                <thead>

                    <tr class="bg-gray-50">

                        <th
                            v-for="column in columns"
                            :key="column.key"
                            class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
                        >
                            {{ column.label }}
                        </th>

                        <th
                            v-if="$slots.actions"
                            class="px-4 py-3 text-center"
                        >
                            Ações
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <tr
                        v-for="row in paginatedData"
                        :key="row.id"
                        class="border-t hover:bg-gray-50"
                    >

                        <td
                            v-for="column in columns"
                            :key="column.key"
                            class="px-4 py-3 text-sm"
                        >
                            {{ getValue(row, column.key) }}
                        </td>

                        <td
                            v-if="$slots.actions"
                            class="px-4 py-3 text-center"
                        >
                            <slot
                                name="actions"
                                :row="row"
                            />
                        </td>

                    </tr>

                    <tr v-if="!paginatedData.length">

                        <td
                            :colspan="columns.length + 1"
                            class="text-center py-10 text-gray-400"
                        >
                            Nenhum registro encontrado
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

        <!-- PAGINAÇÃO -->
        <div
            class="flex justify-between items-center p-4 border-t border-gray-200"
            v-if="totalPages > 1"
        >

            <button
                @click="currentPage--"
                :disabled="currentPage === 1"
                class="px-3 py-2 border rounded"
            >
                Anterior
            </button>

            <span>
                Página {{ currentPage }} de {{ totalPages }}
            </span>

            <button
                @click="currentPage++"
                :disabled="currentPage === totalPages"
                class="px-3 py-2 border rounded"
            >
                Próxima
            </button>

        </div>

    </div>

</template>

<script>

export default {

    name: 'GenericTable',

    props: {

        data: {
            type: Array,
            default: () => []
        },

        columns: {
            type: Array,
            default: () => []
        },

        perPage: {
            type: Number,
            default: 10
        }

    },

    data() {

        return {
            search: '',
            currentPage: 1
        }

    },

    computed: {

        filteredData() {

            if (!this.search) {
                return this.data
            }

            return this.data.filter(row => {

                return Object.values(row).some(value => {

                    return String(value)
                        .toLowerCase()
                        .includes(this.search.toLowerCase())

                })

            })

        },

        totalPages() {

            return Math.ceil(
                this.filteredData.length / this.perPage
            )

        },

        paginatedData() {

            const start =
                (this.currentPage - 1) * this.perPage

            const end =
                start + this.perPage

            return this.filteredData.slice(start, end)

        }

    },

    methods: {

        getValue(row, key) {

            return key
                .split('.')
                .reduce((obj, item) => obj?.[item], row)

        }

    },

    watch: {

        search() {
            this.currentPage = 1
        }

    }

}

</script>