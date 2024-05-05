<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShaveTokenPurchase;
use App\Models\ShavingQueue;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PurchaseTokenController extends Controller
{





    public function showVip()
    {
        $vipStudents = Student::whereHas('shaveTokenPurchases', function ($query) {
            $query->where('token_type', 'vip');
        })
            ->with('shaveTokenPurchases')
            ->get()
            ->map(function ($student) {
                $vipPurchases = $student->shaveTokenPurchases->where('token_type', 'vip');
                $jumlahTokenVip = $vipPurchases->count();
                return [
                    'student_name' => $student->name,
                    'Token_type' => 'vip',
                    'jumlah_token' => $jumlahTokenVip
                ];
            })->filter();

        return response()->json($vipStudents);
    }

    public function showReguler()
    {
        $regulerStudents = Student::whereHas('shaveTokenPurchases', function ($query) {
            $query->where('token_type', 'reguler');
        })
            ->with('shaveTokenPurchases')
            ->get()
            ->map(function ($student) {
                $regulerPurchases = $student->shaveTokenPurchases->where('token_type', 'reguler');
                $jumlahTokenReguler = $regulerPurchases->count();
                return [
                    'student_name' => $student->name,
                    'Token_type' => 'reguler',
                    'jumlah_token' => $jumlahTokenReguler
                ];
            })->filter();

        return response()->json($regulerStudents);
    }




    public function buy(Request $request)
    {
        $student = Student::where('uid', $request->uid)->first();

        if (!$student) {
            return response()->json(['error' => 'Siswa tidak ditemukan'], 404);
        }

        // Membuat validator untuk memvalidasi data input dari request
        $validator = Validator::make($request->all(), [
            'queue_number' => [
                'required_if:token_type,reguler',
                'integer',
                function ($attribute, $value, $fail) use ($student) {
                    if ($value === null && !ShavingQueue::where('student_id', $student->id)->where('type', 'vip')->exists()) {
                        $fail('Nomor antrian diperlukan untuk pembelian token reguler.');
                    }
                    if ($value !== null && ShavingQueue::where('queue_number', $value)->where('type', 'reguler')->where('student_id', '!=', $student->id)->exists()) {
                        $fail('Nomor antrian sudah diambil oleh siswa lain.');
                    }
                    if ($value !== null && ShavingQueue::where('queue_number', $value)->where('type', 'reguler')->where('student_id', $student->id)->exists()) {
                        $fail('Siswa ini sudah memiliki nomor antrian ini.');
                    }
                }
            ],
            'token_type' => 'required|in:' . implode(',', ShavingQueue::getConstants()),
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $queueNumber = $request->queue_number;
        if ($request->token_type === 'vip') {
            $queueNumber = ShavingQueue::where('student_id', $student->id)->where('type', 'vip')->whereNull('queue_number')->value('id') ?: ShavingQueue::where('student_id', $student->id)->where('type', 'vip')->max('queue_number') + 1;
        } else {
            $queueNumber = $request->queue_number ?? ShavingQueue::where('student_id', $student->id)->where('type', 'reguler')->max('queue_number') + 1;
        }

        $uid = Str::uuid();

        $shaveTokenPurchase = ShaveTokenPurchase::create([
            'uid' => $uid,
            'student_id' => $student->id,
            'tanggal_pembayaran' => now(),
            'token_type' => $request->token_type,
        ]);

        if ($request->token_type === 'reguler') {
            $shavingQueue = ShavingQueue::firstOrCreate([
                'queue_number' => $queueNumber,
                'type' => 'reguler',
                'student_id' => $student->id,
            ]);
        } else {
            $shavingQueue = ShavingQueue::where('student_id', $student->id)->where('type', 'vip')->orderBy('queue_number', 'desc')->firstOrCreate([
                'queue_number' => $queueNumber,
                'type' => 'vip',
                'student_id' => $student->id,
            ]);
        }

        if (!$shavingQueue) {
            return response()->json(['error' => 'Gagal membuat atau mengambil antrian cukur'], 400);
        }

        $shavingQueue->student_id = $student->id;
        $shavingQueue->save();

        return response()->json(['message' => 'Pembelian token cukur berhasil', 'shave_token_purchase' => $shaveTokenPurchase, 'queue_number' => $queueNumber], 201);
    }



    public function edit(ShaveTokenPurchase $shaveTokenPurchase, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'queue_number' => 'required_if:token_type,reguler|integer|min:1|max:' . ShavingQueue::where('student_id', $shaveTokenPurchase->student_id)->max('queue_number'),
            'token_type' => 'required|in:' . implode(',', ShavingQueue::getConstants()),
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $queueNumber = $request->queue_number;
        $queue = ShavingQueue::where('student_id', $shaveTokenPurchase->student_id)
            ->where('queue_number', $queueNumber)
            ->where('type', $request->token_type)
            ->first();

        if ($queue) {
            return response()->json(['error' => 'Queue number already purchased'], 400);
        }

        if ($request->token_type === 'reguler' && $shaveTokenPurchase->token_type === 'vip') {
            $currentVipQueue = ShavingQueue::where('student_id', $shaveTokenPurchase->student_id)
                ->where('type', 'vip')
                ->where('queue_number', $shaveTokenPurchase->queue_number)
                ->first();

            if (!$currentVipQueue) {
                return response()->json(['error' => 'Invalid VIP queue number'], 400);
            }

            $regulerQueue = ShavingQueue::where('student_id', $shaveTokenPurchase->student_id)
                ->where('type', 'reguler')
                ->where('queue_number', $queueNumber)
                ->first();

            if ($regulerQueue) {
                return response()->json(['error' => 'Queue number already purchased'], 400);
            }

            $currentVipQueue->delete();
        }

        $shaveTokenPurchase->token_type = $request->token_type;

        if ($request->token_type === 'reguler') {
            $shaveTokenPurchase->queue_number = $queueNumber;
        } else {
            $shaveTokenPurchase->queue_number = ShavingQueue::where('student_id', $shaveTokenPurchase->student_id)->where('type', 'vip')->max('queue_number') + 1;
        }

        $shaveTokenPurchase->save();

        return response()->json(['message' => 'Token type and queue number updated successfully', 'shave_token_purchase' => $shaveTokenPurchase]);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required|string|max:36',
            'token_type' => 'required|string|in:reguler,vip'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if ($request->token_type === 'reguler') {
            $validator = Validator::make($request->all(), [
                'queue_number' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
        }

        $student = Student::where('uid', $request->uid)->first();

        if (!$student) {
            return response()->json(['error' => 'Student tidak ditemukan'], 404);
        }

        $query = ShaveTokenPurchase::where('student_id', $student->id)
            ->where('token_type', $request->token_type);

        if ($request->token_type === 'reguler') {
            $query = $query->where('queue_number', $request->queue_number);
        }

        $shaveTokenPurchase = $query->first();

        if (!$shaveTokenPurchase) {
            return response()->json(['error' => 'Pembelian token tidak ditemukan'], 404);
        }

        $shaveTokenPurchase->delete();

        return response()->json(['message' => 'Pembelian token berhasil dihapus']);
    }
}

