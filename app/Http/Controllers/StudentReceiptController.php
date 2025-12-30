<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentReceipts;
use App\Models\StudentPayments;
use App\Models\SchoolFee;
use App\Models\School;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReceiptMail;
use Illuminate\Validation\Rules;
//use App\Services\ZeptoMailService;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class StudentReceiptController extends Controller
{
    // Show receipt creation form
    public function create($studentId)
    {
        $student = User::findOrFail($studentId);

        // Get expected fees from SchoolFee table
        $expectedFees = SchoolFee::where('class', $student->class)
            ->where('term', $student->term)
            ->where('session', $student->session)
            ->first();

            if (!$expectedFees) {
                // fallback: get latest fees for that class only
                $expectedFees = SchoolFee::where('class', $student->class)
                    ->orderByDesc('id')
                    ->first();
            }

        return view('students.receipts.create', compact('student', 'expectedFees'));
    }

    // Store receipt
 public function store(Request $request, $studentId)
{
    $student = User::findOrFail($studentId);

    // Pull active session and term
    $school = School::first();
    $currentSession = $school->session ?? null;
    $currentTerm    = $school->term ?? null;

    // Get expected fee from SchoolFee table
    $expectedFee = SchoolFee::where('class', $student->class)
        ->where('term', $currentTerm)
        ->where('session', $currentSession)
        ->first();

    // Use value from form if provided, otherwise fallback to system expected fee
    $totalExpected = $request->filled('total_expected')
        ? $request->total_expected
        : ($expectedFee ? $expectedFee->total : 0);

    // Find existing receipt for this student/term/session
    $receipt = StudentReceipts::where('student_id', $student->id)
        ->where('term', $currentTerm)
        ->where('session', $currentSession)
        ->first();

    if (!$receipt) {
        // First payment → create new receipt
        $receipt = StudentReceipts::create([
            'student_id'     => $student->id,
            'term'           => $currentTerm,
            'session'        => $currentSession,
            'tuition'        => 0,
            'uniform'        => 0,
            'exam_fee'       => 0,
            'discount'       => $request->discount ?? 0,
            'total_expected' => $totalExpected,
            'amount_paid'    => 0,
            'amount_due'     => $totalExpected - ($request->discount ?? 0),
        ]);
    }

    // ✅ Update specific fee columns
    if ($request->filled('tuition')) {
        $receipt->increment('tuition', $request->tuition);
    }
    if ($request->filled('uniform')) {
        $receipt->increment('uniform', $request->uniform);
    }
    if ($request->filled('exam_fee')) {
        $receipt->increment('exam_fee', $request->exam_fee);
    }

    // Save payment record
    StudentPayments::create([
        'receipt_id'     => $receipt->id,
        'amount_paid'    => $request->amount_paid,
        'payment_method' => $request->payment_method,
        'payment_date'   => now(),
    ]);

    // Recalculate totals
    $totalPaid = StudentPayments::where('receipt_id', $receipt->id)->sum('amount_paid');
    $receipt->update([
        'amount_paid' => $totalPaid,
        'amount_due'  => $totalExpected - ($receipt->discount ?? 0) - $totalPaid,
    ]);

    return redirect()->route('students.index')->with('success', 'Payment recorded successfully!');
}





    // Record another payment
    public function addPayment(Request $request, $receiptId)
    {
        $receipt = StudentReceipts::findOrFail($receiptId);

        StudentPayments::create([
            'receipt_id'   => $receipt->id,
            'amount_paid'       => $request->amount_paid,
            'payment_date' => now(),
        ]);

        // Update receipt
        $receipt->amount_paid += $request->amount_paid;
        $receipt->amount_due = $receipt->total_expected - $receipt->discount - $receipt->amount_paid;
        $receipt->save();

        return back()->with('success', 'Payment recorded successfully!');
    }

    public function show(StudentReceipts $receipt)
    {
        return view('students.receipts.show', compact('receipt'));
    }

    public function downloadPdf($id)
    {
        $receipt = StudentReceipts::with(['student', 'payments'])->findOrFail($id);
        $school  = School::first();

        $pdf = Pdf::loadView('students.receipts.pdf', compact('receipt', 'school'));

        return $pdf->download("receipt-{$receipt->id}.pdf");
    }

   public function emailReceipt($id)
    {
        $receipt = StudentReceipts::with(['student', 'payments'])->findOrFail($id);
        $school  = School::first();

        // generate PDF
        $pdf = Pdf::loadView('students.receipts.pdf', compact('receipt', 'school'))->output();

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'authorization' => 'Zoho-enczapikey ' . env('ZEPTOMAIL_API_KEY'),
                    'accept'        => 'application/json',
                    'content-Type'  => 'application/json',
                ])
                ->timeout(30)
                ->post(env('ZEPTOMAIL_URL') . '/v1.1/email/template', [
                    "template_key" => "mail-receipt", // <-- must match exactly in ZeptoMail
                    "from" => [
                        "address" => "development@schooldrive.com.ng", // must be verified in ZeptoMail
                        "name"    => "School Receipt"
                    ],
                    "to" => [
                        ["email_address" => ["address" => $receipt->student->email ?? 'no-reply@schooldrive.com.ng']]
                    ],
                    "merge_info" => [
                        "firstname"    => $receipt->student->firstname ?? 'N/A',
                        "term"         => $receipt->term ?? 'N/A',
                        "session"      => $receipt->session ?? 'N/A',
                        "class"        => $receipt->student->class ?? 'N/A',
                        "amount_paid"  => $receipt->payments->sum('amount_paid') ?? 0,
                        "amount_due"   => $receipt->amount_due ?? 0,
                    ],
                    "attachments" => [
                        [
                            "name"      => "receipt-{$receipt->id}.pdf",
                            "mime_type" => "application/pdf",
                            "content"   => base64_encode($pdf),
                        ]
                    ]
                ]);

            if ($response->failed()) {
                \Log::error('ZeptoMail error: ' . $response->body());
                return back()->with('error', 'Failed to send receipt.');
            }

            return back()->with('success', 'Receipt emailed successfully.');
        } catch (\Exception $e) {
            \Log::error('Receipt email exception: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            //dd('Exception: ' . $e->getMessage());
        }
    }


    public function sendPaymentReminder($id)
    {
        $receipt = StudentReceipts::with(['student', 'payments'])->findOrFail($id);
        $school  = School::first();

        $response = Http::withoutVerifying()
            ->withHeaders([
                'authorization' => 'Zoho-enczapikey ' . env('ZEPTOMAIL_API_KEY'),
                'accept'        => 'application/json',
                'content-type'  => 'application/json',
            ])->timeout(30)
            ->post(env('ZEPTOMAIL_URL') . '/v1.1/email/template', [
                "template_key" => "mail-reminder", // 👈 your reminder template
                "from" => [
                    "address" => "development@schooldrive.com.ng",
                    "name"    => "School Reminder"
                ],
                "to" => [
                    ["email_address" => ["address" => $receipt->student->email]]
                ],
                "merge_info" => [
                    "firstname"   => $receipt->student->firstname,
                    "term"        => $receipt->term ?? 'N/A',
                    "session"     => $receipt->session ?? 'N/A',
                    "class"       => $receipt->student->class ?? 'N/A',
                    "amount_due"  => $receipt->amount_due ?? 0,
                    "amount_paid"  => $receipt->payments->sum('amount_paid'),
                ]
            ]);

        if ($response->failed()) {
            return back()->with('error', 'Failed to send reminder: ' . $response->body());
        }

        return back()->with('success', 'Balance reminder sent successfully.');
    }



}
