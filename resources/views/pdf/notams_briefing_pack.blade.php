@php
    use \Illuminate\Support\Collection;
        /** @var Collection<string, \Illuminate\Support\Collection{
         *     departureAirport: \Illuminate\Support\Collection<string>,
         *     destinationAirport: \Illuminate\Support\Collection<string>,
         *     destinationAlternates: \Illuminate\Support\Collection<string>,
         *     enrouteAlternates: \Illuminate\Support\Collection<string>,
         *     takeoffAlternate: \Illuminate\Support\Collection<string>,
         * }> $filteredNotams
         */
@endphp
        <!DOCTYPE html>
<html lang="en">

<head>
    <title>Notam Briefing Pack</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite('resources/js/app.js')
</head>

<body>
<div class="mx-auto w-[290mm] border-0">
    <div class="text-4xl p-4 font-sans text-gray-800">Primary Notams</div>
    @foreach($filteredNotams['primary'] as $category => $categoryData)
        @foreach($categoryData as $airportId => $notams)
            <table class="table-auto mb-16 {{ $category === 'departureAirport' ? 'border-indigo-800' : 'border-sky-600' }}  border-2 w-full">
                <thead>
                <tr>
                    <th colspan="4"
                        class="{{ $category === 'departureAirport' ? 'bg-indigo-800' : 'bg-sky-600' }} py-4 px-4">
                        <span class="text-gray-100 {{ $category === 'departureAirport' ? 'bg-indigo-600' : 'bg-sky-500' }} p-3 text-2xl float-right rounded">{{  str($category)->studly()->value() }}</span>
                        <span class="font-extrabold text-white text-2xl p-3 float-left">{{$airportId}}</span>
                    </th>
                </tr>
                </thead>
                <tbody class="bg-transparent">
                @foreach($notams as $notam)
                    <tr>
                        <td class="py-2 px-4">{{$notam['code']}}</td>
                        <td class="py-2 px-4 font-bold">{{$notam['type']}}</td>
                        <td class="py-2 px-4 italic">{{$notam['id']}}</td>
                        <td class="py-2 px-4 whitespace-break-spaces">{{$notam['summary']}}</td>
                    </tr>
                @endforeach
                <tr class="h-10">
                    <td colspan="4">
                </tr>
                <tr>
                    <td colspan="4" class="bg-gray-800 text-white text-center">
                        Dark Notams: {{count($filteredNotams['appendix'][$category][$airportId])}} in APPENDIX
                    </td>
                </tr>
                </tbody>
            </table>
        @endforeach
    @endforeach


    <div style="page-break-before:always;" class="text-4xl p-4 font-sans text-gray-800">Appendix Notams</div>
    @foreach($filteredNotams['appendix'] as $category => $categoryData)
        @foreach($categoryData as $airportId => $notams)
            <table class="table-auto mb-16 border-gray-800 border-2 w-full ">
                <thead>
                <tr>
                    <th class="bg-gray-800 py-4 px-4">
                        <span class="text-gray-100 bg-gray-400 p-3 text-2xl float-right rounded">{{  str($category)->studly()->value() }}</span>
                        <span class="font-extrabold text-white text-2xl p-3 float-left">{{$airportId}}</span>
                    </th>
                </tr>
                </thead>
                <tbody class="bg-transparent">
                @foreach($notams as $notam)
                    <tr class="break-before-auto">
                        <td>
                            <table class="w-full">
                                <tr>
                                    <td class="py-2 px-4">{{$notam['code']}}</td>
                                    <td class="py-2 px-4 font-bold">{{$notam['type']}}</td>
                                    <td class="py-2 px-4 italic">{{$notam['id']}}</td>
                                    <td class="py-2 px-4 whitespace-break-spaces text-lg">{{$notam['summary']}}</td>
                                </tr>
                                <tr class="border-b-2 border-b-gray-600">
                                    <td colspan="4" class="whitespace-pre font-mono p-2 text-sm">{{$notam['structure']['all']}}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endforeach
    @endforeach
</div>
</body>

</html>
