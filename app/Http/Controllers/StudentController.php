<?php

namespace App\Http\Controllers;

use App\Models\BalanceMutation;
use App\Models\AuditLog;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\WhatsAppNotification;
use App\Jobs\SendWhatsAppNotification;
use App\Support\PhoneNumber;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::orderByDesc('created_at')->get();
        $editingStudent = $request->filled('edit')
            ? Student::findOrFail($request->integer('edit'))
            : null;

        return view('students.index', compact('students', 'editingStudent'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_number' => ['required', 'string', 'max:255', 'unique:students'],
            'name' => ['required', 'string', 'max:255'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'classroom' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'daily_limit' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $photo = $request->file('photo');
        unset($data['photo']);
        $data['card_token'] = (string) Str::uuid();
        $data['card_status'] = 'active';
        $data['card_issued_at'] = now();
        $student = Student::create($data);
        $student->update(['card_code' => 'SIRKAH-' . str_pad((string) $student->id, 6, '0', STR_PAD_LEFT)]);
        if ($photo) {
            $student->update(['photo_path' => $photo->store('students', 'public')]);
        }

        return redirect()->route('students.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'student_number' => ['required', 'string', 'max:255', Rule::unique('students')->ignore($student->id)],
            'name' => ['required', 'string', 'max:255'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'classroom' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'daily_limit' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $photo = $request->file('photo');
        unset($data['photo']);
        if ($photo) {
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }
            $data['photo_path'] = $photo->store('students', 'public');
        }
        $student->update($data);

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        $photoPath = $student->photo_path;
        DB::transaction(function () use ($student) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'student.deleted',
                'auditable_type' => Student::class,
                'auditable_id' => $student->id,
                'old_values' => $student->only(['student_number', 'name', 'balance', 'parent_phone']),
                'ip_address' => request()->ip(),
            ]);
            $student->delete();
        });

        if ($photoPath) {
            Storage::disk('public')->delete($photoPath);
        }

        return redirect()->route('students.index')->with('success', 'Siswa berhasil dihapus.');
    }

    public function topup(Request $request, Student $student)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $amount = (float) $validated['amount'];
        $referenceNumber = $validated['reference_number'] ?? null;
        $description = $validated['description'] ?? 'Top-up saldo siswa';

        DB::transaction(function () use ($student, $amount, $referenceNumber, $description) {
            $student = Student::whereKey($student->id)->lockForUpdate()->firstOrFail();
            $before = (float) $student->balance;
            $student->increment('balance', $amount);

            BalanceMutation::create([
                'student_id' => $student->id,
                'user_id' => auth()->id(),
                'type' => 'topup',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $before + $amount,
                'description' => $description,
                'reference_number' => $referenceNumber,
                'reference_type' => 'student_topup',
                'reference_id' => $student->id,
            ]);
            AuditLog::create([
                'user_id' => auth()->id(), 'action' => 'student.topup', 'auditable_type' => Student::class,
                'auditable_id' => $student->id, 'new_values' => ['amount' => $amount, 'balance' => $before + $amount],
                'ip_address' => request()->ip(),
            ]);
        });

        $student->refresh();
        $phone = PhoneNumber::normalize($student->parent_phone);
        if ($phone) {
            $message = "Sirkah - Top Up Saldo\nSantri: {$student->name}\nNominal: Rp " . number_format($amount, 0, ',', '.') . "\nSaldo sekarang: Rp " . number_format($student->balance, 0, ',', '.');
            $hash = hash('sha256', $phone . '|' . $message);
            $notification = WhatsAppNotification::firstOrCreate([
                'message_hash' => $hash,
            ], [
                'student_id' => $student->id,
                'phone_number' => $phone,
                'message' => $message,
                'status' => 'pending',
            ]);
            if ($notification->wasRecentlyCreated) SendWhatsAppNotification::dispatch($notification);
        }

        return redirect()->route('students.index')->with('success', 'Saldo siswa berhasil ditambah.');
    }

    public function regenerateCard(Student $student)
    {
        $oldToken = $student->card_token;
        $student->update([
            'card_token' => (string) Str::uuid(),
            'card_status' => 'active',
            'card_issued_at' => now(),
            'card_revoked_at' => null,
        ]);
        AuditLog::create(['user_id' => auth()->id(), 'action' => 'student.card_regenerated', 'auditable_type' => Student::class, 'auditable_id' => $student->id, 'old_values' => ['card_token' => $oldToken], 'new_values' => ['card_token' => $student->card_token], 'ip_address' => request()->ip()]);

        return redirect()->route('students.index')->with('success', 'Barcode kartu berhasil diterbitkan ulang.');
    }

    public function toggleCard(Student $student)
    {
        $student->update([
            'card_status' => $student->card_status === 'active' ? 'inactive' : 'active',
            'card_revoked_at' => $student->card_status === 'active' ? now() : null,
        ]);
        AuditLog::create(['user_id' => auth()->id(), 'action' => 'student.card_status_changed', 'auditable_type' => Student::class, 'auditable_id' => $student->id, 'new_values' => ['card_status' => $student->card_status], 'ip_address' => request()->ip()]);

        return back()->with('success', 'Status kartu berhasil diperbarui.');
    }
}
