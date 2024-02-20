<script setup>
import {useForm} from "@inertiajs/vue3";
import {nextTick, onMounted, ref, watchEffect} from "vue";
import InputError from "@/Components/InputError.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    session_id: {
        type: String,
        required: true,
    },
});

const form = useForm({
    flight_plan: "",
});

const messageBox = ref(null);
const fileKey = ref('');
let progressMessage = ref([]);
let result = ref('');
let expandedNotam = ref(null);

onMounted(() => {
    Echo.channel(props.session_id)
        .listen("\\App\\Events\\NotamProcessingEvent", (event) => {
            progressMessage.value.push({message: event.message, type: event.type});
            scrollToLatestUpdate();
        })
        .listen("\\App\\Events\\NotamResultEvent", (event) => {
            result.value = event.data;
        })
        .listen("\\App\\Events\\PdfResultEvent", (event) => {
            fileKey.value = event.key;
        });
});

const scrollToLatestUpdate = () => {
    const lastChildElement = messageBox.value?.lastElementChild;
    if (lastChildElement) {
        // Using nextTick to wait for DOM update before scrolling
        nextTick(() => {
            lastChildElement.scrollIntoView({
                behavior: "smooth",
            });
        });
    }
};

// watchEffect(() => {
//     if (fileKey.value !== '') {
//         const link = document.createElement('a');
//         link.href = `/download/${fileKey.value}`;
//         link.setAttribute('download', 'notampack.pdf');
//         link.style.display = 'none';
//         document.body.appendChild(link);
//         link.click();
//         document.body.removeChild(link);
//     }
// });


const tableColour = (notamSection, category) => {
    return {
        'odd:bg-sky-50 even:bg-sky-100': notamSection === 'primary' && category === 'departureAirport',
        'odd:bg-indigo-50 even:bg-indigo-100': notamSection === 'primary' && category === 'destinationAirport',
        'odd:bg-white even:bg-gray-100': notamSection === 'appendix',
    };
};

const submit = () => {
    progressMessage.value = [{message: "Beginning ATC Flightplan Analysis", type: 'success'}];
    form.post(route("notam.store"), {
        preserveState: true,
        //onFinish: () => form.reset("flight_plan"),
    });
};

const closeModal = () => {
    result.value = '';
    progressMessage.value = [];
};

const toggleDetails = (notamId) => {
    expandedNotam.value = expandedNotam.value === notamId ? null : notamId;
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 py-6 justify-center sm:py-12">
        <form @submit.prevent="submit">
            <div class="relative py-3 sm:max-w-4xl sm:mx-auto">
                <div class="relative px-4 py-10 bg-white mx-8 md:mx-0 shadow rounded-3xl sm:p-10">
                    <div class="max-w-2xl mx-auto">
                        <div class="flex items-center space-x-5">
                            <div class="space-x-5 rounded-full flex justify-center items-center text-2xl font-mono">
                                <img src="/images/just-notams-logo-210.png" alt="My Image" />
                                <div class="block pl-2 font-semibold text-xl self-start text-gray-700">
                                    <h2 class="leading-relaxed">Submit your ATC flightplan!</h2>
                                    <p class="text-sm text-gray-500 font-normal leading-relaxed">
                                        Yeah, seriously. Just copy and paste it in here.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="divide-y divide-gray-200">
                            <div class="py-8 text-base leading-6 space-y-4 text-gray-700 sm:text-lg sm:leading-7">
                                <div class="flex flex-col">
                                    <label class="leading-loose">ATC FlightPlan</label>
                                    <textarea v-model="form.flight_plan" required rows="15"
                                              class="px-4 py-2 border focus:ring-gray-500 focus:border-gray-900 w-full sm:text-sm border-gray-300 rounded-md focus:outline-none text-gray-600"
                                              placeholder="Paste here!"></textarea>
                                    <InputError class="mt-3" :message="form.errors.flight_plan" />
                                </div>
                            </div>
                            <div class="pt-4 flex items-center space-x-4">
                                <button @click="progressMessage = []" type="reset"
                                        class="bg-gray-100 flex justify-center items-center w-full text-gray-900 px-4 py-3 rounded-md focus:outline-none">
                                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Reset
                                </button>
                                <button :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                                        type="submit"
                                        class="bg-cyan-700 flex justify-center items-center w-full text-white px-4 py-3 rounded-md focus:outline-none">
                                    Click and Pray!
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <Modal :show="result !== '' || progressMessage.length > 0" @close="closeModal" max-width="4xl">
            <div v-if="fileKey !== ''" class="p-4 w-1/2 mx-auto bg-yellow-300 flex justify-center items-center m-3 rounded underline text-blue-800">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 mr-3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l3 3m0 0l3-3m-3 3V2.25" />
                </svg>

                <a :href="`/download/${fileKey}`" target="_blank">Download Notam Briefing Pack</a>
            </div>
            <div v-if="result !== ''" class="p-6">
                <div v-for="(notamResults, notamSection) in result" class="p-6"
                     :class="{'bg-gray-400': notamSection === 'appendix'}">
                    <h1 class="p-3 mb-2 text-3xl font-semibold uppercase text-center text-gray-700">
                        {{ notamSection === 'appendix' ? '(DARK)' : '' }}
                        {{ notamSection }}
                        NOTAMS
                    </h1>
                    <template v-for="(categoryData, category) in notamResults">
                        <template v-for="(notams, airportId) in categoryData">
                            <h2 class="p-3 text-xl capitalize text-gray-700 leading-10">
                                {{ category }} <span class="uppercase pl-5">{{ airportId }}</span>
                            </h2>
                            <table class="table-auto w-full text-sm text-left text-gray-600 dark:text-gray-400 mb-20">
                                <thead
                                    class="text-xs text-white uppercase bg-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3 w-28">Notam ID</th>
                                    <th class="px-6 py-3 w-12">Code</th>
                                    <th class="px-6 py-3 w-40">Category</th>
                                    <th class="px-6 py-3">Summary</th>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="notam in notams">
                                    <tr :class="tableColour(notamSection, category)">
                                        <td class="px-6 py-3 cursor-pointer whitespace-nowrap text-blue-600 underline"
                                            @click="toggleDetails(notam.id)">
                                            {{ notam.id }}
                                        </td>
                                        <td class="px-6 py-3" v-text="notam.code"></td>
                                        <td class="px-6 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                            v-text="notam.type"></td>
                                        <td class="px-6 py-3" v-text="notam.summary"></td>
                                    </tr>
                                    <tr v-if="expandedNotam === notam.id" class="bg-gray-100 text-gray-600">
                                        <td colspan="4" class="px-6 py-4 font-mono whitespace-pre"
                                            v-text="notam.structure.all"></td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </template>
                    </template>
                </div>
            </div>

            <div v-if="progressMessage.length > 0 && result ===''" class="p-6">
                <h1 class="p-3 mb-2 text-3xl font-semibold uppercase bg-green-100 text-center text-gray-700">
                    Status Updates
                </h1>
                <div class="max-h-96 pl-6 overflow-y-auto">
                    <ol class="list-outside list-disc space-y-2" ref="messageBox">
                        <li v-for="progress in progressMessage" class="p-2 rounded text-gray-700"
                            :class="{ 'odd:bg-gray-100 even:bg-gray-50': progress.type === 'success', 'bg-red-300': progress.type !== 'success' }"
                            v-html="progress.message"></li>
                    </ol>
                </div>
            </div>

        </Modal>
    </div>
</template>

<style scoped>

</style>