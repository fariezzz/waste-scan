@extends('layouts.main')

@section('content')
    <div class="container my-4">

        <div class="edu-detail-header">
            <div class="edu-detail-bg" style="background-image: url('{{ asset($module['image']) }}')">
                <div class="edu-detail-overlay"></div>
                <h1 class="edu-detail-title">{{ $module['title'] }}</h1>
            </div>
        </div>

        <div class="edu-content mt-4">
            @include($contentView)
        </div>

    </div>
@endsection
