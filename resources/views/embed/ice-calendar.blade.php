<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Ice Rental Calendar</title>
    @vite(['resources/js/embed-calendar.js'])
    <style>
        html, body { height: 100%; margin: 0; }
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            color: #111827;
            background: #fff;
        }
        .embed { display: flex; flex-direction: column; height: 100%; padding: 12px; box-sizing: border-box; }
        #ice-calendar { flex: 1; min-height: 0; transition: opacity .15s; }
        #ice-calendar.is-loading { opacity: .6; }
        .fc .fc-toolbar { flex-wrap: wrap; gap: 8px; }
        .fc .fc-toolbar-title { font-size: 1.15rem; }
        .fc .fc-button { padding: .25rem .6rem; font-size: .85rem; }
        @media (max-width: 639px) {
            .fc .fc-toolbar-chunk:nth-child(2) { order: -1; flex-basis: 100%; text-align: center; }
            .fc .fc-toolbar-title { font-size: 1rem; }
        }
        .ice-event { overflow: hidden; padding: 1px 2px; line-height: 1.25; }
        .ice-event-time { font-size: .75em; opacity: .9; }
        .ice-event-title { font-weight: 600; }
        .ice-event-sheets { font-size: .75em; opacity: .9; }
        .fc-daygrid-event .ice-event { white-space: normal; }
        .legend { display: flex; flex-wrap: wrap; gap: 12px; padding-top: 8px; font-size: 12px; color: #4b5563; }
        .legend span::before {
            content: ''; display: inline-block; width: 10px; height: 10px; margin-right: 4px;
            border-radius: 2px; background: var(--swatch); vertical-align: -1px;
        }
    </style>
</head>
<body>
    <div class="embed">
        <div id="ice-calendar" data-feed-url="{{ route('api.ice-calendar') }}" data-sheet-count="{{ $sheetCount }}"></div>
        <div class="legend">
            @foreach (\App\Enums\EventType::cases() as $type)
                <span style="--swatch: {{ $type->hexColor() }}">{{ $type->getLabel() }}</span>
            @endforeach
        </div>
    </div>
</body>
</html>
