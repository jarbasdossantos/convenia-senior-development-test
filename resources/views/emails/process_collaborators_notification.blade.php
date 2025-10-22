<!-- resources/views/emails/csv_processed.blade.php -->
<!doctype html>
<html>

<body>
    <p>Processamento realizado com sucesso.</p>
    <p>Registros processados com sucesso: {{ $processed }}</p>

    @if(!empty($errors))
    <p>Algumas linhas apresentaram problemas (listagem resumida):</p>
    <ul>
        @foreach(array_slice($errors, 0, 10) as $err)
        <li>Linha {{ $err['line'] }}: {{ $err['error'] }}</li>
        @endforeach
    </ul>
    <p>Total de erros: {{ count($errors) }}</p>
    @endif
</body>

</html>
