@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <h2 class="mb-4">Менің брондарым</h2>

        @if($bookings->count())
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Тур</th>
                        <th>Күні</th>
                        <th>Орын саны</th>
                        <th>Жалпы баға</th>
                        <th>Статус</th>
                        <th>Әрекет</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($bookings as $index => $booking)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $booking->tour->name ?? '—' }}</td>
                            <td>{{ $booking->tour->date ?? '—' }}</td>
                            <td>{{ $booking->seats }}</td>
                            <td>{{ $booking->seats * ($booking->tour->price ?? 0) }} ₸</td>
                            <td>
                                @if($booking->is_paid === true)
                                    <span class="badge bg-success">Төленді</span>
                                @elseif($booking->is_paid === false)
                                    <span class="badge bg-danger">Бас тартылды</span>
                                @else
                                    <span class="badge bg-warning text-dark">Күту</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->status === 'pending')
                                    <a href="{{ route('paypal.pay', $booking->id) }}" class="btn btn-sm btn-outline-primary">Төлеу</a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info">Сізде ешқандай брондау жоқ.</div>
        @endif
    </div>
@endsection
