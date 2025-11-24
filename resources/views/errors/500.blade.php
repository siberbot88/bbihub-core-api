@extends('errors::illustrated')

@section('title', __('Kesalahan Server'))
@section('code', '500')

@section('heading', 'Terjadi Kesalahan Server')

@section('message')
    Maaf, ada masalah teknis di sisi server kami. Tim kami sedang bekerja untuk memperbaikinya.
@endsection
