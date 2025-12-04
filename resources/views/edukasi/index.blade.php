@extends('layouts.main')
@section('bodyClass', 'mb-10')
@section('content')
    <div class="edu-wrapper container mt-4">

        <h2 class="text-center mb-4 edu-title">Edukasi Pengelolaan Sampah</h2>

        <div class="row justify-content-center">
            @foreach ($modules as $m)
                <div class="col-md-6">
                    <div class="edu-card">
                        <div class="edu-bg" style="background-image: url('{{ asset($m['image']) }}')">
                            <div class="edu-overlay"></div>
                            <div class="edu-text">{{ $m['title'] }}</div>
                        </div>
                        <a href="{{ route('edukasi.show', $m['slug']) }}" class="edu-btn">Selengkapnya &gt;&gt;&gt;</a>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
@endsection
