<template>

    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
        @click.self="close"
    >

        <div
            class="bg-white rounded-2xl shadow-xl flex flex-col overflow-hidden"
            :class="modalSize"
        >

            <!-- HEADER -->
            <div
                v-if="$slots.header"
                class="px-6 py-4 border-b border-gray-200 flex justify-between items-center"
            >

                <div class="font-semibold text-lg">
                    <slot name="header" />
                </div>

                <button
                    @click="close"
                    class="text-gray-400 hover:text-black"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>

            </div>

            <!-- BODY -->
            <div class="p-6 overflow-y-auto flex-1">

                <slot name="body" />

            </div>

            <!-- FOOTER -->
            <div
                v-if="$slots.footer"
                class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2"
            >
                <slot name="footer" />
            </div>

        </div>

    </div>

</template>

<script>

export default {

    name: 'GenericModal',

    props: {

        size: {
            type: String,
            default: 'md'
        }

    },

    emits: ['close'],

    computed: {

        modalSize() {

            const sizes = {

                sm: 'w-[500px] max-w-[95vw]',

                md: 'w-[800px] max-w-[95vw]',

                lg: 'w-[1200px] max-w-[95vw]'

            }

            return sizes[this.size] || sizes.md

        }

    },

    methods: {

        close() {
            this.$emit('close')
        }

    }

}

</script>