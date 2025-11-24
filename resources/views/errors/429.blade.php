@extends('errors::illustrated')

@section('title', __('Terlalu Banyak Permintaan'))
@section('code', '429')

@section('heading', 'Terlalu Banyak Permintaan')

@section('message')
    Maaf, Anda melakukan terlalu banyak permintaan ke server kami. Mohon tunggu sebentar sebelum mencoba lagi.
@endsection
