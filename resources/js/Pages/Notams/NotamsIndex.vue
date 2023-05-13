<script setup>
import {Head, useForm} from "@inertiajs/vue3";
import {ref, defineProps, onMounted} from "vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";

const props = defineProps({
    session_id: {
        type: String,
        required: true,
    },
});

const form = useForm({
    flight_plan: "",
});

let progressMessage = ref();
let progressType = ref();
let result = ref('');

onMounted(() => {
    Echo.channel(props.session_id)
        .listen(
            "\\App\\Events\\NotamProcessingEvent",
            (event) => {
                progressMessage.value = event.message;
                progressType.value = event.type;
            }
        )
        .listen(
            "\\App\\Events\\NotamResultEvent",
            (event) => {
                result.value = event.data;
            }
        );
});

const submit = () => {
    progressMessage.value = form.errors.flight_plan === '' ? "Beginning ATC Flightplan Analysis" : '';
    progressType.value = "success";
    form.post(route("notam.store"), {
        onFinish: () => form.reset("flight_plan"),
    });
};

const closeModal = () => {
    result.value = '';
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
                                <img src='/images/just-notams-logo-210.png' alt="My Image" />
                                <div class="block pl-2 font-semibold text-xl self-start text-gray-700">
                                    <h2 class="leading-relaxed">Submit your ATC flightplan!</h2>
                                    <p class="text-sm text-gray-500 font-normal leading-relaxed">Yeah, seriously. Just
                                        copy
                                        and
                                        paste it in here.</p>
                                </div>
                            </div>
                        </div>
                        <div class="divide-y divide-gray-200">
                            <div class="py-8 text-base leading-6 space-y-4 text-gray-700 sm:text-lg sm:leading-7">
                                <div class="flex flex-col">
                                    <label class="leading-loose">ATC FlightPlan</label>
                                    <textarea
                                        v-model="form.flight_plan"
                                        required
                                        rows="15"
                                        class="px-4 py-2 border focus:ring-gray-500 focus:border-gray-900 w-full sm:text-sm border-gray-300 rounded-md focus:outline-none text-gray-600"
                                        placeholder="Paste here!"></textarea>
                                    <InputError
                                        class="mt-3"
                                        :message="form.errors.flight_plan"
                                    />

                                </div>
                            </div>
                            <div
                                v-if="progressMessage"
                                :class="{
                'bg-green-300': progressType === 'success',
                'bg-red-300': progressType === 'error',
            }"
                                class="mb-6 rounded p-2 text-center"
                                v-html="progressMessage"
                            ></div>
                            <div class="pt-4 flex items-center space-x-4">
                                <button
                                    @click="progressMessage = ''"
                                    type="reset"
                                    class="bg-gray-100 flex justify-center items-center w-full text-gray-900 px-4 py-3 rounded-md focus:outline-none">
                                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Reset
                                </button>
                                <button
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                    type="submit"
                                    class="bg-cyan-700 flex justify-center items-center w-full text-white px-4 py-3 rounded-md focus:outline-none">
                                    Click and Pray
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </form>
            <Modal :show="result !== ''" @close="closeModal" max-width="3xl">
                <div v-for="(airport, name) in result" class="p-6">
                    <h1 class="p-3 text-xl text-center text-gray-700">Airport {{name}}</h1>
                    <table class="table w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-white uppercase bg-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3">Notam ID</th>
                            <th class="px-6 py-3">Code</th>
                            <th class="px-6 py-3">Notam Name</th>
                            <th class="px-6 py-3">Explanation</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="odd:bg-white even:bg-gray-100" v-for="notam in airport">
                            <td class="px-6 py-3" v-text="notam.id"></td>
                            <td class="px-6 py-3" v-text="notam.TagCode"></td>
                            <td class="px-6 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white" v-text="notam.TagName"></td>
                            <td class="px-6 py-3" v-text="notam.Explanation"></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </Modal>
        </div>
</template>

<style scoped>

</style>