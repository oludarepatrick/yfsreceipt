@extends('layouts.app')

@section('title','Primary Payments Statement')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Primary Student Payments Statement</h3>
        <div>
            <a href="{{ route('admin.statements.payments.pdf', request()->query()) }}" class="btn btn-outline-secondary me-1">
                <i class="bi bi-file-earmark-pdf"></i> Download PDF
            </a>
            
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#emailModal">
                <i class="bi bi-envelope"></i> Email Statement
            </button>
        </div>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <select name="term" class="form-select">
                <option value="">-- Select Term --</option>
                @foreach($terms as $t)
                    <option value="{{ $t }}" @if(request('term') == $t) selected @endif>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="session" class="form-select">
                <option value="">-- Select Session --</option>
                @foreach($sessions as $s)
                    <option value="{{ $s }}" @if(request('session') == $s) selected @endif>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive" style="max-height:60vh; overflow:auto;">
                <table class="table table-striped table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Term</th>
                            <th>Session</th>
                            <th>Expected</th>
                            <th>AmountPaid</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $p)
                            <tr>
                                <td>{{ $loop->iteration + (($payments->currentPage() - 1) * $payments->perPage()) }}</td>
                                <td>{{ optional($p->payment_date)->format('d M Y, h:i A') }}</td>
                                <td>{{ optional($p->receipt->student)->firstname }} {{ optional($p->receipt->student)->lastname }}</td>
                                <td>{{ optional($p->receipt->student)->class }}</td>
                                <td>{{ optional($p->receipt)->term }}</td>
                                <td>{{ optional($p->receipt)->session }}</td>
                                <td>₦{{ number_format($p->receipt->total_expected, 2) }}</td>
                                <td>₦{{ number_format($p->amount_paid, 2) }}</td>
                                <td>{{ ucfirst($p->payment_method) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <strong>Total:</strong> ₦{{ number_format($total,2) }}
                </div>
                <div>
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <form method="POST" action="{{ route('admin.statements.payments.email') }}">
      @csrf
      <input type="hidden" name="term" value="{{ request('term') }}">
      <input type="hidden" name="session" value="{{ request('session') }}">
      <input type="hidden" name="from" value="{{ request('from') }}">
      <input type="hidden" name="to" value="{{ request('to') }}">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Email Statement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Recipient Name</label>
            <input type="text" name="firstname" class="form-control">
          </div>
          <div class="mb-3">
            <label>Recipient Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
          <button class="btn btn-primary" type="submit">Send</button>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection
