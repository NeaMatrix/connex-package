@php
    use Torgodly\Connex\Support\ConnexPaths;
@endphp
@include('connex::partials.config')
<script>{!! file_get_contents(ConnexPaths::javascript('connex-login.js')) !!}</script>
