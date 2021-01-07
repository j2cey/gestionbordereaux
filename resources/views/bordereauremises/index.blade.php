@extends('app')

@section('app_content')

    <section class="section">

        <div class="container">

            <div class="tw-bg-white tw-shadow-md tw-rounded tw-px-3 md:tw-px-8 tw-pt-3 md:tw-pt-6 tw-pb-3 md:tw-pb-8 tw-mb-4">

                <div class="tw-mb-4">

                    <h2 class="tw-text-blue-600 tw-text-lg tw-font-bold tw-mb-3 tw-border-b tw-border-gray-400 tw-pb-2">Bordereaux de Remise</h2>

                    <!-- SEARCH FORM -->

                    <search-form
                        group="bordereauremise-search"
                        url="{{ route('bordereauremises.fetch') }}"
                        :params="{
                            search: '',
                            per_page: {{ $defaultPerPage }},
                            page: 1,
                            order_by: 'numero_transaction:asc',
                            dateremise_du: '',
                            dateremise_au: '',
                            localisation: '',
                            statut: '',
                            }"
                        v-slot="{ params, update, change, clear, processing }"
                    >

                        <form class="tw-grid tw-grid-cols-8 tw-col-gap-4 tw-pb-3 tw-border-b tw-border-gray-400">
                            <div class="tw-col-span-4 md:tw-col-span-2">
                                <label
                                    for="dateremise_du"
                                    class="tw-block tw-uppercase tw-tracking-wide tw-text-gray-700 tw-text-xs tw-font-bold tw-mb-2"
                                >
                                    Du
                                </label>
                                <div class="relative">
                                    <vue2-datepicker id="dateremise_du" v-model="params.dateremise_du" format="YYYY-MM-DD" @change="change"></vue2-datepicker>
                                </div>
                            </div>

                            <div class="tw-col-span-4 md:tw-col-span-2">
                                <label
                                    for="dateremise_au"
                                    class="tw-block tw-uppercase tw-tracking-wide tw-text-gray-700 tw-text-xs tw-font-bold tw-mb-2"
                                >
                                    Au
                                </label>
                                <div class="relative">
                                    <vue2-datepicker id="dateremise_au" v-model="params.dateremise_au" format="YYYY-MM-DD" @change="change"></vue2-datepicker>
                                </div>
                            </div>

{{--                            TODO: PB de rafraichissement des parametres de filtre--}}

                            <div class="tw-col-span-4 md:tw-col-span-2">
                                <label
                                    for="localisation"
                                    class="tw-block tw-uppercase tw-tracking-wide tw-text-gray-700 tw-text-xs tw-font-bold tw-mb-2"
                                >
                                    Localisation
                                </label>
                                <div class="tw-inline-flex">
                                    <div class="tw-relative">
                                        <select
                                            v-model="params.localisation"
                                            @change="change"
                                            id="localisation"
                                            class="tw-appearance-none tw-block tw-w-full tw-bg-gray-200 focus:tw-bg-white tw-text-gray-700 tw-border tw-border-gray-400 focus:tw-border-gray-500 tw-rounded-sm tw-py-3 tw-pl-4 tw-pr-8 tw-leading-tight focus:tw-outline-none"
                                        >
                                            <option
                                                v-for="localisation in {{ $localisations }}"
                                                :value="localisation.id"
                                            >@{{ localisation.titre }}</option>
                                        </select>
                                        <select-angle></select-angle>
                                    </div>
                                    <div class="input-group-append">
                                        <button type="button" id="localisation_clear" name="localisation_clear" class="btn btn-default" @click="[params.localisation= '', change()]"><i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="tw-col-span-4 md:tw-col-span-2">
                                <label
                                    for="statut"
                                    class="tw-block tw-uppercase tw-tracking-wide tw-text-gray-700 tw-text-xs tw-font-bold tw-mb-2"
                                >
                                    Statut
                                </label>
                                <div class="tw-inline-flex">
                                    <div class="tw-relative">
                                        <select
                                            v-model="params.statut"
                                            @change="change"
                                            id="statut"
                                            class="tw-appearance-none tw-block tw-w-full tw-bg-gray-200 focus:tw-bg-white tw-text-gray-700 tw-border tw-border-gray-400 focus:tw-border-gray-500 tw-rounded-sm tw-py-3 tw-pl-4 tw-pr-8 tw-leading-tight focus:tw-outline-none"
                                        >
                                            <option
                                                v-for="statut in {{ $statuts }}"
                                                :value="statut.id"
                                            >@{{ statut.titre }}</option>
                                        </select>
                                        <select-angle></select-angle>
                                    </div>
                                    <div class="input-group-append">
                                        <button type="button" id="statut_clear" name="statut_clear" class="btn btn-default" @click="[params.statut= '', change()]"><i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </search-form>

                    <!--/ SEARCH FORM -->


                    <!-- RESULTS -->

                    <search-results group="bordereauremise-search" v-slot="{ total, records }">

                        <div class="tw-text-sm">

                            <div class="tw-flex tw-flex-wrap tw-p-4 tw-bg-gray-700 tw-text-white tw-rounded-sm">
                                <div class="tw-flex-auto tw-pr-3">Total : @{{ total }}</div>
                            </div>

                            <template v-if="records.length">

                                    <table class="table-auto">
                                        <thead>
                                        <tr>
                                            <th class="tw-px-4 tw-py-2">Numéro Transaction</th>
                                            <th class="tw-px-4 tw-py-2">Mode Paiement</th>
                                            <th class="tw-px-4 tw-py-2">Type</th>
                                            <th class="tw-px-4 tw-py-2">Agence</th>
                                            <th class="tw-px-4 tw-py-2">Montant Total</th>
                                            <th class="tw-px-4 tw-py-2">Statut</th>
                                            <th class="tw-px-4 tw-py-2">Détails</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="record in records"
                                            :key="record.id"
                                            class="tw-px-4 tw-border-b tw-border-dashed tw-border-gray-400 tw-text-gray-700 hover:tw-bg-gray-100"
                                        >
                                            <td class="tw-px-4 tw-py-2">@{{ record.numero_transaction }}</td>
                                            <td class="tw-px-4 tw-py-2">@{{ record.modepaiement_titre }}</td>
                                            <td class="tw-px-4 tw-py-2">@{{ record.bordereauremise_type_titre }}</td>
                                            <td class="tw-px-4 tw-py-2">@{{ record.localisation_titre }}</td>
                                            <td class="tw-px-4 tw-py-2">@{{ record.montant_total | formatNumber }}</td>
                                            <td class="tw-px-4 tw-py-2">
                                                <span v-if="record.workflow_currentstep_code == 'step_end'" class="tw-text-xs tw-font-semibold tw-inline-block tw-py-1 tw-px-2 tw-rounded-full tw-text-green-600 tw-bg-green-200 last:tw-mr-0 tw-mr-1">@{{ record.workflow_currentstep_titre }}</span>
                                                <span v-else-if="record.workflow_currentstep_code == 'step_0'" class="tw-text-xs tw-font-semibold tw-inline-block tw-py-1 tw-px-2 tw-rounded-full tw-text-purple-600 tw-bg-purple-200 last:tw-mr-0 tw-mr-1">@{{ record.workflow_currentstep_titre }}</span>
                                                <span v-else-if="record.workflow_currentstep_code == 'step_1'" class="tw-text-xs tw-font-semibold tw-inline-block tw-py-1 tw-px-2 tw-rounded-full tw-text-indigo-600 tw-bg-indigo-200 last:tw-mr-0 tw-mr-1">@{{ record.workflow_currentstep_titre }}</span>
                                                <span v-else-if="record.workflow_currentstep_code == 'step_2'" class="tw-text-xs tw-font-semibold tw-inline-block tw-py-1 tw-px-2 tw-rounded-full tw-text-blue-600 tw-bg-blue-200 last:tw-mr-0 tw-mr-1">@{{ record.workflow_currentstep_titre }}</span>
                                                <span v-else class="tw-text-xs tw-font-semibold tw-inline-block tw-py-1 tw-px-2 tw-rounded-full tw-text-teal-600 tw-bg-teal-200 last:tw-mr-0 tw-mr-1">@{{ record.workflow_currentstep_titre }}</span>
                                            </td>
                                            <td class="tw-px-4 tw-py-2"><a
                                                    :href="record.edit_url"
                                                    class="tw-inline-block tw-mr-3 tw-text-green-500"
                                                >
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                </a></td>
                                        </tr>

                                        </tbody>
                                    </table>

                            </template>

                            <div
                                v-else
                                class="tw-flex tw-flex-wrap tw-p-4 border-b tw-border-dashed tw-border-gray-400 tw-text-gray-700"
                            >
                                Aucune donnée disponible
                            </div>

                        </div>

                    </search-results>

                    <!--/ RESULTS -->


                    <!-- PAGINATION -->

                    <search-pagination group="bordereauremise-search" :always-show="true"></search-pagination>

                    <!--/ PAGINATION -->

                </div>

            </div>
        </div>

    </section>

@endsection
