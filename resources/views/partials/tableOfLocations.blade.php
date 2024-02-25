@php
    /** @var \App\DTO\AtcFlightPlan $icaoLocations */
@endphp
So this is what we're working with:<br />
<table class='table table-auto mx-auto w-1/2 border'>
    <tr class='border'>
        <td class='p-2 text-gray-800'>Departure</td>
        <td class='p-2 text-gray-600'>{{$icaoLocations->departureAirport->first()}}</td>
    </tr>
    <tr class='border'>
        <td class='p-2 text-gray-800'>Destination</td>
        <td class='p-2 text-gray-600'>{{$icaoLocations->destinationAirport->first()}}</td>
    </tr>
    <tr class='border'>
        <td class='p-2 text-gray-800'>Dest Alts</td>
        <td class='p-2 text-gray-600'>{{$icaoLocations->destinationAlternate->implode(',')}}</td>
    </tr>
    <tr class='border'>
        <td class='p-2 text-gray-800'>Firs</td>
        <td class='p-2 text-gray-600'>{{$icaoLocations->firs->implode(',')}}</td>
    </tr>
    <tr class='border'>
        <td class='p-2 text-gray-800'>En-route Alts</td>
        <td class='p-2 text-gray-600'>{{$icaoLocations->enrouteAlternates->implode(',')}}</td>
    </tr>
    <tr>
        <td class='p-2 text-gray-800'>TO Alts</td>
        <td class='p-2 text-gray-600'>{{$icaoLocations->takeoffAlternate->first()}}</td>
    </tr>
</table>