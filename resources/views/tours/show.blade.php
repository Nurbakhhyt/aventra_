@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <a href="{{ route('tours.index') }}" class="btn btn-link mb-3">← Барлық турларға қайту</a>

        <div class="card">
            <div class="row g-0">
                @if($tour->images->first())
                    <div class="col-md-6">
                        <img src="{{ asset('storage/' . $tour->images->first()->image_path) }}" class="img-fluid rounded-start" alt="Tour Image">
                    </div>
                @endif
                <div class="col-md-6">
                    <div class="card-body">
                        <h3 class="card-title">{{ $tour->name }}</h3>
                        <p class="card-text">{{ $tour->description }}</p>
                        <p class="card-text"><strong>Бағасы:</strong> {{ $tour->price }} ₸</p>
                        <p class="card-text"><strong>Өтетін күні:</strong> {{ $tour->date }}</p>
                        <p class="card-text"><strong>Орналасуы:</strong> {{ $tour->location->name ?? '—' }}</p>
                        <p class="card-text"><strong>Гид:</strong> {{ $tour->user->name ?? '—' }}</p>

                        @if($tour->volume > 0)
                            <form action="{{ route('bookingsTour.create') }}" method="GET" class="mt-4">
                                <input type="hidden" name="tour_id" value="{{ $tour->id }}">
                                <button type="submit" class="btn btn-success">Брондау</button>
                            </form>
                        @else
                            <div class="alert alert-warning mt-4">Бұл турда бос орын қалмады.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($bookings->count())
            <div class="mt-5">
                <h4>Менің брондарым</h4>
                <ul class="list-group">
                    @foreach($bookings as $booking)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $booking->seats }} орын — <strong>{{ $booking->status }}</strong> ({{ $booking->created_at->format('d.m.Y') }})
                            @if($booking->status === 'pending')
                                <a href="{{ route('paypal.pay', $booking->id) }}" class="btn btn-sm btn-outline-primary">Төлеу</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
