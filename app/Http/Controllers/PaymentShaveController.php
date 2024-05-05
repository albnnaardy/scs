<?php

namespace App\Http\Controllers;

use App\Models\ShaveTokenPurchase;
use App\Models\ShavingQueue;
use App\Models\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class PaymentShaveController extends Controller
{
    


    // public function checkToken(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'uid' => 'required|string|max:36',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 400);
    //     }

    //     $student = Student::where('uid', $request->input('uid'))->first();

    //     if (!$student) {
    //         return response()->json(['error' => 'Siswa tidak ditemukan'], 404);
    //     }

    //     $tokens = ShaveTokenPurchase::where('student_id', $student->id)->get(); // Menggunakan get() untuk mengambil data

    //     if ($tokens->isEmpty()) {
    //         return response()->json(['error' => 'Tidak ada token yang tersedia'], 404);
    //     }

    //     $response = [
    //         'message' => 'Token tersedia',
    //         'tokens' => []
    //     ];

    //     foreach ($tokens as $token) {
    //         $shavingQueue = ShavingQueue::where('student_id', $student->id)->first(); // Menggunakan 'student_id' untuk mencari di tabel ShavingQueue
    //         $queueNumber = $shavingQueue ? $shavingQueue->queue_number : null;
    //         $response['tokens'][] = [
    //             'token_type' => $token->type,
    //             'jumlah' => $token->jumlah,
    //             'nomer_antrian' => $queueNumber
    //         ];
    //     }

    //     return response()->json($response, 200);
    // }
    
    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required|string|exists:students,uid',
            'token_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $student = Student::where('uid', $request->input('uid'))->first();
        if (!$student) {
            return response()->json(['error' => 'Siswa tidak ditemukan'], 404);
        }

        $token = ShaveTokenPurchase::where('token_type', $request->input('token_type'))
            ->where('student_id', $student->id)
            ->whereNull('used_at')
            ->first();

        if (!$token) {
            return response()->json(['error' => 'Token tidak ditemukan'], 404);
        }

        // Menghapus token setelah digunakan
        $token->delete();

        return response()->json(['message' => 'Pembayaran Shave berhasil'], 200);
    }
}
