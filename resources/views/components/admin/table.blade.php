@props([
    'headers' => [],
    'striped' => false,
    'hover' => true
])

<div class="table-responsive">
    <table {{ $attributes->merge(['class' => 'table table-modern']) }}>
        @if(count($headers) > 0)
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @elseif(isset($head))
            <thead>
                {{ $head }}
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
        @if(isset($foot))
            <tfoot>
                {{ $foot }}
            </tfoot>
        @endif
    </table>
</div>
