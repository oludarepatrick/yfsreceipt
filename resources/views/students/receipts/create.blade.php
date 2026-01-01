@extends('layouts.app')
@section('title', 'Create Receipt')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Create Receipt for {{ $student->firstname }} {{ $student->lastname }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('students.receipts.store', $student->id) }}">
                @csrf

                <div class="row g-2 mb-4 p-3 bg-light border rounded">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Previous Balance</label>
                        <input type="text" name="previous_balance" class="form-control form-control-sm bg-white" value="{{ $expectedFees->previous_balance ?? 0 }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Total Expected (Fees Setup)</label>
                        <input type="text" name="total_expected1" class="form-control form-control-sm bg-white" value="{{ $expectedFees->total ?? 0 }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-primary">Total Expected + Outstanding</label>
                        <input type="text" name="total_expected" class="form-control form-control-sm fw-bold border-primary bg-white" value="{{ ($expectedFees->total ?? 0) + ($expectedFees->previous_balance ?? 0) }}" readonly>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small">Tuition</label>
                        <input type="number" name="tuition" class="form-control calc-field" required placeholder="0">
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small">Uniform</label>
                        <input type="number" name="uniform" class="form-control calc-field" required placeholder="0">
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small">Exam Fee</label>
                        <input type="number" name="exam_fee" class="form-control calc-field" required placeholder="0">
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small">Stationeries</label>
                        <input type="number" name="stationeries" class="form-control calc-field" placeholder="0">
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small">Others</label>
                        <input type="number" name="others" class="form-control calc-field" placeholder="0">
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small text-danger">Discount</label>
                        <input type="number" name="discount" class="form-control calc-field text-danger" value="0">
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small fw-bold">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>

                <div class="row mt-4 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white fw-bold">Total Amount Paid</span>
                            <input type="number" name="amount_paid" class="form-control form-control-lg fw-bold bg-light" required readonly>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="bi bi-printer"></i> Generate Receipt
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Calculation Script --}}
<script>
    function calculateAmountPaid() {
        let tuition = parseFloat(document.querySelector('[name="tuition"]').value) || 0;
        let uniform = parseFloat(document.querySelector('[name="uniform"]').value) || 0;
        let exam    = parseFloat(document.querySelector('[name="exam_fee"]').value) || 0;
        let stationeries = parseFloat(document.querySelector('[name="stationeries"]').value) || 0;
        let others  = parseFloat(document.querySelector('[name="others"]').value) || 0;
        let discount = parseFloat(document.querySelector('[name="discount"]').value) || 0;

        let total = (tuition + uniform + exam + stationeries + others) - discount;
        document.querySelector('[name="amount_paid"]').value = total >= 0 ? total : 0;
    }

    document.querySelectorAll('.calc-field').forEach(el => {
        el.addEventListener('input', calculateAmountPaid);
    });

    // Calculate Outstanding Balance plus Total Expected
    const prevInput = document.querySelector('[name="previous_balance"]');
    const expectedInput = document.querySelector('[name="total_expected1"]');
    const outstandingInput = document.querySelector('[name="total_expected"]');

    function updateOutstanding() {
        let prev = parseFloat(prevInput.value) || 0;
        let expected = parseFloat(expectedInput.value) || 0;
        
        outstandingInput.value = prev + expected;
    }

    // Update whenever user types in either field
    prevInput.addEventListener('keyup', updateOutstanding);
    expectedInput.addEventListener('keyup', updateOutstanding);

</script>
@endsection