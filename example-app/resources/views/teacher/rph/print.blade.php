<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->title }} · SchoolHub</title>
    @vite(['resources/js/app.js'])
    @include('teacher.rph._document-styles')
    <style>
        body { margin: 0; background: #e2e8f0; }
        .page { background: #fff; max-width: 210mm; margin: 24px auto; padding: 18mm 16mm; box-shadow: 0 1px 4px rgb(0 0 0 / .15); }
        .toolbar { max-width: 210mm; margin: 16px auto 0; display: flex; gap: 8px; font-family: Arial, sans-serif; }
        .toolbar button, .toolbar a { font-size: 15px; padding: 10px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; color: #0f172a; text-decoration: none; cursor: pointer; }
        .toolbar button { background: #1b6ac9; border-color: #1b6ac9; color: #fff; font-weight: bold; }
        @page { size: A4; margin: 15mm; }
        @media print {
            body { background: #fff; }
            .page { margin: 0; padding: 0; box-shadow: none; max-width: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button type="button" data-print>Print / Save as PDF</button>
        <a href="{{ route('teacher.rph.show', $document) }}">Back to RPH</a>
    </div>

    <div class="page">
        @include('teacher.rph._document')

        <div class="rph-footer">
            <span>Prepared by: {{ $document->teacher->user->name }}</span>
            <span>Printed on {{ now()->format('j F Y') }}</span>
        </div>
    </div>
</body>
</html>
