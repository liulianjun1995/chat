@extends('home.layout.main')

@section('content')

@endsection

@section('script')
    <script>
        ROOT.URL_LOGIN = "{{ route('chat-login') }}"
        ROOT.URL_LOGOUT = "{{ route('chat-logout') }}"
        ROOT.VM = null
        ROOT.SESSIONID = "{{ request()->session()->getId() }}"
        require(['lib/app']);
    </script>
@stop