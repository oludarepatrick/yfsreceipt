@extends('layouts.app')

@section('title', 'Student Receipt')

@section('content')
<div class="container py-5">
    <div class="card shadow-lg rounded-4">
        @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

        <div class="card-header bg-gradient-primary text-white text-center py-4">
            <h3 class="fw-bold custom-name mb-0">Student Receipt</h3>
        </div>
        <div class="card-body p-4">

            {{-- Student Info --}}
            <div class="mb-4">
                <h5 class="fw-bold">Student: {{ $receipt->student->firstname }} {{ $receipt->student->lastname }}</h5>
                <p class="text-muted mb-1">Class: {{ $receipt->student->class }}</p>
                <p class="text-muted">Session: {{ $receipt->session }} | Term: {{ $receipt->term }}</p>
            </div>

            {{-- Fees Breakdown --}}
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tuition</th>
                        <th>Uniform</th>
                        <th>Exam Fee</th>
                        <th>Others</th>
                        <th>Discount</th>
                        <th>Outstanding</th>
                        <th>Total Expected</th>
                        <th>Amount Paid</th>
                        <th>Balance Due</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>₦{{ number_format($receipt->tuition) }}</td>
                        <td>₦{{ number_format($receipt->uniform) }}</td>
                        <td>₦{{ number_format($receipt->exam_fee) }}</td>
                        <td>₦{{ number_format($receipt->external_money) }}</td>
                        <td>₦{{ number_format($receipt->discount) }}</td>
                        <td class="fw-bold text-danger">₦{{ number_format($receipt->previous_balance) }}</td>
                        <td class="fw-bold text-primary">₦{{ number_format($receipt->total_expected) }}</td>
                        <td class="fw-bold text-success">₦{{ number_format($receipt->amount_paid) }}</td>
                        <td class="fw-bold text-danger">₦{{ number_format($receipt->amount_due) }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Payments History --}}
            <h5 class="mt-5">Payment History</h5>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount Paid</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receipt->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('d M Y, h:i A') }}</td>
                            <td>₦{{ number_format($payment->amount_paid) }}</td>
                            <td>{{ ucfirst($payment->payment_method) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex gap-2">
                <!-- Download PDF -->
                <a href="{{ route('students.receipts.pdf', $receipt->id) }}" 
                class="btn btn-outline-success rounded-pill px-3">
                    <i class="bi bi-download"></i> Download PDF
                </a>

                <!-- Send Email -->
                <form action="{{ route('students.receipts.email', $receipt->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-envelope"></i> Email Receipt
                    </button>
                </form>

                <!-- Send Reminder (only if balance is due) -->
                @if($receipt->amount_due > 0)
                <form action="{{ route('students.receipts.reminder', $receipt->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning rounded-pill px-3">
                        <i class="bi bi-bell"></i> Send Reminder
                    </button>
                </form>
                @endif
            </div>

            <div class="text-end mt-4">
                <span class="text-muted small">Last Updated: {{ $receipt->updated_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
<style>
    .custom-name {
    color: #49032cff;
    }
</style>
@endsection
