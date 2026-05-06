<h1>Maintenance Work Sheet</h1>
<h2>{{ $mwsPart->title }} - {{ $mwsPart->part_number }}</h2>

<div class="info-grid">
    <div><strong>Customer:</strong> {{ $mwsPart->customer_name ?? '-' }}</div>
    <div><strong>Serial Number:</strong> {{ $mwsPart->serial_number }}</div>
</div>

@foreach($mwsPart->sections->sortBy('order') as $section)

    <h3>{{ $section->title }}</h3>

    {{-- TYPE: TABLE --}}
    @if($section->type === 'table')
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Description</th>
                    <th>Man</th>
                    <th>Hours</th>
                    <th>Tech</th>
                    <th>Insp</th>
                </tr>
            </thead>
            <tbody>
                @foreach($section->items as $item)
                <tr>
                    <td>{{ $item->no }}</td>
                    <td>{{ $item->content }}</td>
                    <td>{{ $item->man ?? '-' }}</td>
                    <td>{{ $item->hours ?? '-' }}</td>
                    <td>{{ $item->tech ?? '-' }}</td>
                    <td>{{ $item->insp ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- TYPE: LIST --}}
    @if($section->type === 'list')
        <ul>
            @foreach($section->items as $item)
                <li>{{ $item->content }}</li>
            @endforeach
        </ul>
    @endif

    {{-- TYPE: PARAGRAPH --}}
    @if($section->type === 'text')
        @foreach($section->items as $item)
            <p>{{ $item->content }}</p>
        @endforeach
    @endif

    {{-- TYPE: CAUTION --}}
    @if($section->type === 'caution')
        @foreach($section->items as $item)
            <div style="color:red; font-weight:bold;">
                ⚠ {{ $item->content }}
            </div>
        @endforeach
    @endif

@endforeach