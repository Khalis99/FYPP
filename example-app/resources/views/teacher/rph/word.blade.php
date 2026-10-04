{{-- HTML that Microsoft Word opens as an editable document (.doc) --}}
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="utf-8">
    <title>{{ $document->title }}</title>
    @include('teacher.rph._document-styles')
    <style>@page { size: 21cm 29.7cm; margin: 2cm; }</style>
</head>
<body>
    @include('teacher.rph._document')
    <p class="rph-footer">Prepared by: {{ $document->teacher->user->name }}</p>
</body>
</html>
