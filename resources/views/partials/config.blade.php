@php
    use Torgodly\Connex\Support\ConnexScriptConfig;
@endphp
<script>
    window.ConnexLoginConfig = @json(ConnexScriptConfig::toArray());
</script>
