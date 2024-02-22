@php
    /** @var \Illuminate\Support\Collection $airports */
@endphp
So this is what we're working with:<br />
<table class='table table-auto mx-auto w-1/2 border'>
    <tr class='border'>
        <td class='p-2 text-gray-800'>Departure</td>
        <td class='p-2 text-gray-600'>{{$airports['departureAirport']->first()}}</td>
    </tr>
    <tr class='border'>
        <td class='p-2 text-gray-800'>Destination</td>
        <td class='p-2 text-gray-600'>{{$airports['destinationAirport']->first()}}</td>
    </tr>
    <tr class='border'>
        <td class='p-2 text-gray-800'>Dest Alts</td>
        <td class='p-2 text-gray-600'>{{$airports['destinationAlternates']->implode(',')}}</td>
    </tr>
    <tr class='border'>
        <td class='p-2 text-gray-800'>Firs</td>
        <td class='p-2 text-gray-600'>{{$airports['firs']->implode(','}}</td>
    </tr>
    <tr class='border'>
        <td class='p-2 text-gray-800'>En-route Alts</td>
        <td class='p-2 text-gray-600'>{{$airports['enrouteAlternates']->implode(',')}}</td>
    </tr>
    <tr>
        <td class='p-2 text-gray-800'>TO Alts</td>
        <td class='p-2 text-gray-600'>{{$airports['takeoffAlternate']->first()}}</td>
    </tr>
</table>